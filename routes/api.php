<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::post('/register', [AuthController::class, 'register']); // User Registration
Route::post('/login', [AuthController::class, 'login']); // User Login

Route::middleware('auth:sanctum')->group(function () {

    // 1. Shaxsiy profil va Auth amallari
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/update', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete', [AuthController::class, 'delete']);

    // 2. Boshqa foydalanuvchilar (User Profile & Posts)
    Route::prefix('user')->group(function () {
        Route::get('/{user}', [UserController::class, 'userProfile']);
        Route::get('/{user}/followers', [UserController::class, 'followers']);
        Route::get('/{user}/following', [UserController::class, 'following']);
        Route::get('/{user}/friends', [UserController::class, 'friends']);
        Route::get('/{user}/posts', [UserController::class, 'postsAll']);
        Route::get('/{user}/post/{post}', [UserController::class, 'post']);
    });

    // 3. Mening postlarim (My Posts)
    Route::prefix('my')->group(function () {
        Route::get('/posts', [PostController::class, 'index']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::get('/posts/{post}', [PostController::class, 'show']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    });

    // 4. Like tizimi (Yangi qo'shganimiz)
    Route::post('/posts/{post}/like', [LikeController::class, 'toggleLike']);

    // 5. Ijtimoiy tizimlar (Follow & Block)
    Route::post('/follow/{user}', [FollowController::class, 'follow']);
    Route::delete('/unfollow/{user}', [FollowController::class, 'unfollow']);
    Route::get('/status/{user}', [FollowController::class, 'status']);
    Route::post('/follow/{user}/accept', [FollowController::class, 'acceptFollowRequest']);
    Route::delete('/follow/{user}/reject', [FollowController::class, 'rejectFollowRequest']);

    Route::post('/block/{user}', [BlockController::class, 'block']);
    Route::delete('/unblock/{user}', [BlockController::class, 'unblock']);
    Route::get('/blocked-users', [BlockController::class, 'blockedList']);
});
