<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Post $post, Request $request)
    {
        $user = $post->user;
        $me = Auth::user();

        // bir birimizni bloklamaganmizmi
        if ($user->Blocked($me) || $user->isBlocked($me)) {
            return response()->json(["message" => "Amalni bajarish taqiqlangan!"], 403);
        }

        // profil yopiq emasmi
        if ($user->isPrivate($me)) {
            return response()->json(["message" => "Izoh yozish uchun obuna bo'ling."], 403);
        }
        $request->validate([
            'content' => 'required|string|max:1000',        // comment yozish
            'parent_id' => 'nullable|exists:comments,id'    // commentga javob yozish
        ]);

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            "message" => "Comment added!",
            "data" => $comment->load('user:id,name,username')
        ], 201);
    }

    public function update(Comment $comment, Request $request)
    {
        $user = $comment->user;
        $me = $request->user();

        if ($me->id !== $user->id) {
            return response()->json(["message" => "Bu amalni bajarishga ruxsat yo'q!"], 403);
        }

        $request->validate([
            'content' => "required|string|max:1000",
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json([
            "message" => "Comment updated!",
            "data" => $comment->load('user:id,name,username')
        ]);
    }

    public function delete(Comment $comment, Request $request)
    {
        $me = $request->user();

        if ($me->id === $comment->user_id || $me->id === $comment->post->user_id) {
            $comment->delete();

            return response()->json(["message" => "Izoh o'chirildi."]);
        }
        return response()->json(["message" => "Bu amalni bajarishga ruxsat yo'q!"], 403);
    }
}
