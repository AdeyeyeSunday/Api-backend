<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\PostService;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->middleware('auth:api');
        
        return $this->postService = $postService;
    }

    public function getAllPosts()
    {
        return $this->postService->getAllPost();
    }

    public function getPost($id)
    {
        return $this->postService->getPost($id);
    }

    public function getComments($postId)
    {
        return $this->postService->getComments($postId);
    }

    public function getAllUserPosts()
    {
        return $this->postService->getAllUserPosts();
    }

    public function getUserPost($id)
    {
        return $this->postService->getUserPost($id);
    }

    public function getLoggedInUserPost($id)
    {
        return $this->postService->getLoggedInUserPost($id);
    }

    public function createPost(Request $request)
    {
        return $this->postService->createPost($request);
    }

    public function updateUserPost(Request $request, $id)
    {
        return $this->postService->updateUserPost($request, $id);
    }

    public function deleteUserPost($id)
    {
        return $this->postService->deleteUserPost($id);
    }

    public function createComment(Request $request, $id)
    {
        return $this->postService->createComment($request, $id);
    }

}
