<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('login', [AuthController::class, 'login']);

Route::post('register', [AuthController::class, 'register']);

Route::prefix('user')->middleware(['auth:api', 'throttle:global'])->group(function () {
    Route::get('/', [HomeController::class, 'user']);

    Route::get('/all-users', [HomeController::class, 'getAllUsers']);

    Route::get('all-posts', [PostController::class, 'getAllPosts']);

    Route::get('get-post-comments/{postId}', [PostController::class, 'getComments']);

    Route::get('get-post/{id}', [PostController::class, 'getPost']);

    Route::get('get-all-user-posts', [PostController::class, 'getAllUserPosts']);

    Route::get('get-user-post/{id}', [PostController::class, 'getUserPost']);

    Route::get('get-logged-in-user-post/{id}', [PostController::class, 'getLoggedInUserPost']);

    Route::post('create-post', [PostController::class, 'createPost']);

    Route::patch('update-post/{id}', [PostController::class, 'updateUserPost']);

    Route::delete('delete-post/{id}', [PostController::class, 'deleteUserPost']);

    Route::post('comment/{id}', [PostController::class, 'createComment']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
