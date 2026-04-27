<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Post;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // get User Profile
    public function userProfile(User $user)
    {
        $result = $this->userService->userProfile($user);

        if (isset($result['error'])) {
            return response()->json(["message" => $result['error']], $result['code']);
        }
        return response()->json([$result['success']], $result['code']);
    }

    // get Users Posts
    public function postsAll(User $user)
    {
        $me = auth()->user();
        $accessError = $this->userService->checkAccess($me, $user);
        if ($accessError) return $accessError;

        $posts = $user->posts()
            ->with(['user', 'media'])
            ->latest()
            ->paginate(10);

        if ($posts->isEmpty()) {
            return response()->json([
                "user" => $user->username,
                "message" => "Bu foydalanuvchida hali post mavjud emas",
            ]);
        }

        return PostResource::collection($posts);
    }

    // Bitta postni olish
    public function post(User $user, Post $post)
    {
        $me = auth()->user();
        $accessError = $this->userService->checkAccess($me, $user);
        if ($accessError) return $accessError;

        // Post foydalanuvchiga tegishliligini tekshirish
        if ($post->user_id !== $user->id) {
            return response()->json(["message" => "Post topilmadi yoki bu foydalanuvchiga tegishli emas."], 404);
        }

        return new PostResource($post->load('user', 'media'));
    }

    // get Followers - list of users following the given user
    public function followers(User $user)
    {
        $result = $this->userService->getFollowers($user);

        if (isset($result['error'])) {
            return response()->json($result['error'], $result['code']);
        }

        return response()->json([
            "User" => $result['data']['user'],
            'followers' => UserResource::collection($result['data']['followers'])->response()->getData(true),
        ], 200);
    }

    // get Following - list of users followed by the given user
    public function following(User $user)
    {
        $result = $this->userService->getFollowing($user);

        if (isset($result['error'])) {
            return response()->json($result['error'], $result['code']);
        }

        return response()->json([
            "User" => $result['data']['user'],
            'following' => UserResource::collection($result['data']['following'])->response()->getData(true),
        ], 200);
    }

    // get Friends - list of users who are both following and followed by the given user
    public function friends(User $user)
    {
        $result = $this->userService->getFriends($user);

        if (isset($result['error'])) {
            return response()->json($result['error'], $result['code']);
        }

        return response()->json([
            "User" => $result['data']['user'],
            'friends' => UserResource::collection($result['data']['friends'])->response()->getData(true),
        ], 200);
    }
}
