<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function toggleLike(Post $post, Request $request,)
    {
        $me = $request->user();
        $user = $post->user;

        if ($user->Blocked($me) || $user->isBlocked($me)) {
            return response()->json(['message' => 'Amalni bajarish taqiqlangan.'], 403);
        }

        if ($user->isPrivate($me) && $me->id != $user->id && !$me->isFollowing($user)) {
            $followStatus = DB::table('follows')
                ->where('follower_id', $me->id)
                ->where('following_id', $user->id)
                ->value('status');

            if ($followStatus == 'requested') {
                return response()->json([
                    "message" => "Bu hisob shaxsiy, like bosish uchun obuna so'rovingiz tasdiqlangan bolishi kerak."
                ], 403);
            }

            if (!$followStatus) {
                return response()->json([
                    "message" => "Bu hisob shaxsiy, like bosish uchun obuna bo'lishingiz kerak."
                ], 403);
            }
        }

        $like = $post->likes()->where('user_id', $me->id)->first();


        if ($like) {
            $like->delete();
            return response()->json([
                'message' => 'Like removed',
                'likes_count' => $post->likes()->count()
            ]);
        }

        $post->likes()->create(['user_id' => $me->id]);

        return response()->json([
            "post" => $post,
            'message' => 'Post liked!',
            'likes_count' => $post->likes()->count()
        ]);
    }
}
