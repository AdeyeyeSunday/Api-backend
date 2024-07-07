<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\ErrorService as ServicesErrorService;
use Illuminate\Support\Facades\Cache;

class PostService
{
    protected $errorService;

    protected $cacheTime;

    /* The JSON response function can be seen in helper.php under the app folder.
    The error handling service can be found in app/Services. */

    public function __construct(ServicesErrorService $errorService)
    {
        $this->errorService = $errorService;
        // ..........  Cache time is set in the .env file, defaulting to 60 minutes if not specified ..........*/
        $this->cacheTime = env('CACHE_TIME', 60);
    }

    public function getAllPost()
    {
        try {
            // ..........Generate a cache key for all posts..........
            $cacheKey = "all_posts";

            // ..........Attempt to retrieve all posts from cache..........
            $posts = Cache::remember($cacheKey, $this->cacheTime, function () {

                // Fetch all posts from the database
                return Post::all();
            });

            return jsonResponse('Posts fetched successfully', $posts);

        } catch (Exception $e) {

           return $this->errorService->handleError($e);

        }
    }


    public function getPost($id)
    {
        try {
            // ..........Ggenerate a cache key based on the post id and time..........
            $cacheKey = "post_{$id}";

            // .......... Attempt to retrieve post from cache..........
            $post = Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
                // Find the post by ID
                return Post::find($id);
            });

            // .......... Check if post is null (not found..........
            if (!$post) {

                return jsonResponse('Post not found', null, 404, 'error');
            }
            // ..........this retunr a success result..........
            return jsonResponse('Post fetched successfully', $post);
        } catch (Exception $e) {

            return $this->errorService->handleError($e);
        }
    }

    public function getComments($postId)
    {
        try {
            // ..........Ggenerate a cache key based on the comment id and time..........
            $cacheKey = "post_comment_{$postId}";

            // .......... Attempt to retrieve comment from cache..........
            $comments = Cache::remember($cacheKey, $this->cacheTime, function () use ($postId) {
                // Find the comment by ID
                return Comment::where('post_id', $postId)->latest()->get();
            });

            // .......... Check if comment is null (not found..........
            if (!$comments) {

                return jsonResponse('Comments not found', null, 404, 'error');
            }
            // ..........this retunr a success result..........
            return jsonResponse('Comments fetched successfully', $comments);
        } catch (Exception $e) {

          return  $this->errorService->handleError($e);
        }
    }


    public function getAllUserPosts()
    {
        try {
            // ..........geting the user that logi..........
            $user = Auth::user();

            // ..........generate a cache key based on the user id and time..........
            $cacheKey = "user_posts_{$user->id}";

            // ..........attempt to retrieve user posts from cache..........
            $posts = Cache::remember($cacheKey, $this->cacheTime, function () use ($user) {
                // Fetch posts belonging to the authenticated user
                return Post::where('user_id', $user->id)->get();
            });

            // ..........check if posts are empty..........
            if ($posts->isEmpty()) {

                return jsonResponse('Post not found', null, 404, 'error');
            }

            //this retunr a success result
            return jsonResponse('User Posts fetched successfully', $posts);


        } catch (Exception $e) {

         return $this->errorService->handleError($e);
        }
    }



    public function getUserPost($id)
    {
        try {
            // ..........generate a unique cache key for user posts..........
            $cacheKey = "user_posts_{$id}";
            // ..........attempt to retrieve posts from cache..........
            $posts = Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {

                $user = Auth::user();
                return Post::where('user_id', $user->id)->with('comments')->get();
            });

            // ..........check if posts are empty..........
            if ($posts->isEmpty()) {
                return jsonResponse('No posts found for the user', null, 404, 'error');
            }

            // ..........return success response with fetched posts..........
            return jsonResponse('User Posts fetched successfully', $posts);

        } catch (Exception $e) {
            return  $this->errorService->handleError($e);
        }
    }


    public function getLoggedInUserPost($id)
    {
        try {
            // Generate a unique cache key for user posts
            $cacheKey = "user_posts_{$id}";

            // Attempt to retrieve posts from cache
            $posts = Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
                return Post::where('user_id', $id)->with('user.comments')->get();
            });

            // Check if posts are empty
            if ($posts->isEmpty()) {
                return jsonResponse('No posts found for the user', null, 404, 'error');
            }

            // Transform posts data structure for the view
            $postsData = $posts->map(function ($post) {
                return [
                    'user_name' => $post->user->name,
                    'title' => $post->post_title,
                    'content' => $post->content,
                    'comments' => $post->user->comments->map(function ($comment) {
                        return [
                            'comment' => $comment->comment,
                            'created_at' => $comment->created_at->format('Y-m-d H:i:s')
                        ];
                    })->toArray()
                ];
            })->toArray(); // Ensure the collection is converted to an array

            // Return view with fetched posts
            return view('auth.dashboard', ['postData' => $postsData]);
        } catch (Exception $e) {

            return $this->errorService->handleError($e);

        }
    }




    public function createPost(Request $request)
    {
        try {

            $request->validate([
                'post_title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $request->validate([
                'post_title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $data = Post::create([
                'user_id' => Auth::user()->id,
                'post_title' => $request->post_title,
                'content' => $request->content
            ]);

            // ..........cache key specific to the newly created post..........
            $cacheKey = "post_{$data->id}";

            // ..........dtore the post data in the cache..........
            Cache::put($cacheKey, $data, $this->cacheTime);

            //..........this retunr a success result..........
            return jsonResponse('Post created successfully', $data);

        } catch (Exception $e) {

        return  $this->errorService->handleError($e);
        }
    }

    public function createComment(Request $request, $id)
    {
        try {
            //.......... Fetch the post by id..........
            $post = Post::findOrFail($id);

            $request->validate([
                'post_id' => 'required|exists:posts,id',
                'comment' => 'required|string|max:255',
            ]);

            // ..........Create the comment..........
            $data = Comment::create([
                'user_id' => Auth::user()->id,
                'post_id' => $post->id,
                'comment' => $request->comment,
            ]);

            Log::debug($data);
            //..........this retunr a success result..........
            return jsonResponse('Comment created successfully', $data);

        } catch (Exception $e) {

            return  $this->errorService->handleError($e);
        }
    }

    public function updateUserPost(Request $request, $id)
    {
        try {

            // validaor  the request data
            $request->validate([
                'post_title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            // .......... generate a cache key specific to the post..........
            $cacheKey = "post_{$id}";

            // ..........attempt to retrieve the post from cache..........
            $updatePost = Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
                return Post::find($id);
            });

            // ..........ccheck if the post was found..........
            if (!$updatePost) {
                return jsonResponse('Post not found', null, 404, 'error');
            }

            // ..........update the post attributes..........
            $updatePost->update([
                'post_title' => $request->post_title,
                'content' => $request->content,
            ]);

            // ..........clear the cache for this specific post..........
            Cache::forget($cacheKey);

            // store the updated post in cache
            Cache::put($cacheKey, $updatePost, $this->cacheTime);

            // ..........return success response with the updated post..........
            return jsonResponse('Post updated successfully', $updatePost);
        } catch (Exception $e) {

            return  $this->errorService->handleError($e);

        }
    }


    public function deleteUserPost($id)
    {
        try {
            $user = Auth::user();

            // Generate cache key
            $cacheKey = "user_{$user->id}_post_{$id}";

            // Attempt to retrieve the post from cache
            $post = Cache::remember($cacheKey, $this->cacheTime, function () use ($user, $id) {
                return Post::where('user_id', $user->id)->where('id', $id)->first();
            });

            // Check if the post exists
            if (is_null($post)) {
                return jsonResponse('No posts found for deletion', null, 404, 'error');
            }
            // Delete the post from the database
            $post->delete();

            // Remove the cached post data
            Cache::forget($cacheKey);

            // Return success response
            return jsonResponse('Post deleted successfully');

        } catch (Exception $e) {

            return  $this->errorService->handleError($e);

        }
    }

}
