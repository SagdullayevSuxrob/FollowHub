<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\PostInc;

class PostService
{
    //post store
    public function postStore($req)
    {
        $post = $req->user()->posts()->create([
            'caption' => $req->caption,
        ]);

        if ($req->file('media')) {
            $folderPath = "posts/user_" . $req->user()->id . "/post_" . $post->id;

            $files = $req->file('media');

            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $path = $file->store($folderPath, 'public');

                $extension = strtolower($file->getClientOriginalExtension());

                $type = match ($extension) {
                    'jpg', 'jpeg', 'png' => 'image',
                    'mp4' => 'video',
                    'pdf' => 'pdf',
                    default => 'file'
                };

                $post->media()->create([
                    'media_path' => $path,
                    'type' => $type
                ]);
            }
        }

        return $post;
    }

    // get all posts
    public function getMyPosts($perPage = 10)
    {
        $user = auth()->user();

        return $user->posts()
            ->with('media', 'user')
            ->latest()
            ->paginate($perPage);
    }

    // get post by id
    public function myPost(int $id)
    {
        $post = Auth::user()->posts()
            ->with(['user', 'media'])
            ->withCount('likes', 'comments')
            ->find($id);

        if (!$post) {
            return response()->json([
                "message" => "Post topilmadi yoki sizga tegishli emas."
            ], 404);
        }

        return $post;
    }

    // update post
    public function postUpdate($request, $post)
    {
        $post->update([
            'caption' => $request['caption'] ?? $post->caption,
        ]);
        if ($request->filled('delete_media_ids')) {
            $ids = $request->delete_media_ids;
            if (is_string($ids)) {
                $ids = json_decode($ids, true) ?? explode(',', $ids);
            }

            $ids = array_map('intval', $ids);

            $mediaDelete = PostMedia::where('post_id', $post->id)
                ->whereIn('id', $ids)
                ->get();

            foreach ($mediaDelete as $media) {

                if (Storage::disk('public')->exists($media->media_path)) {
                    Storage::disk('public')->delete($media->media_path);
                }

                $media->delete();
            }
        }

        if ($request->file('media')) {  //hasFile

            $folderPath = "posts/user_" . $request->user()->id . "/post_" . $post->id;

            $files = $request->file('media');

            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {

                $path = $file->store($folderPath, 'public');

                $type = match (strtolower($file->getClientOriginalExtension())) {
                    'jpg', 'jpeg', 'png'    => 'image',
                    'mp4'                   => 'video',
                    'pdf'                   => 'pdf',
                    default                 => 'unknown'
                };

                $post->media()->create([
                    'media_path'    => $path,
                    'type'          => $type,
                    'file_name'     => $file->getClientOriginalName()
                ]);
            }
        }
        return $post->load('media');
    }

    // delete post
    public function postDelete(int $post_id)
    {
        $post = Post::where('id', $post_id)
            ->where('user_id', auth()->id())
            ->first();

        $message = null;

        if (!$post) {
            return response()->json([
                "message" => 'Post topilmadi yoki bu post sizga tegishli emas!'
            ], 403);
        }

        $folderPath = "posts/user_" . $post->user_id . "/post_" . $post->id;

        if (Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->deleteDirectory($folderPath);
        }

        if ($post->delete()) {
            $message = 'Post muvaffaqiyatli o\'chirildi.';
        } else {
            $message = 'O\'chirishda muammo';
        }

        return response()->json([$message]);
    }
}
