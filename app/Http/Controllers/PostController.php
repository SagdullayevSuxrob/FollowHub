<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostDeleteRequest;
use App\Http\Requests\Posts\PostStoreRequest;
use App\Http\Requests\Posts\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostMedia;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    private $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }
    // Post Create
    public function store(PostStoreRequest $request)
    {
        $post = $this->postService->postStore($request);

        return response()->json([
            "message" => "Post created successfully",
            "post" => new PostResource($post),
        ], 201);
    }

    // All Posts of me
    public function index()
    {
        $posts = $this->postService->getMyPosts(10);

        return PostResource::collection($posts)->additional([
            "user" => auth()->user()->only('id', 'name', 'username')
        ]);
    }

    // Show Single Post
    public function show($id)
    {
        $post = $this->postService->myPost($id);

        return new PostResource($post);
    }

    // Update Post
    public function update(PostUpdateRequest $request, Post $post)
    {
        $updatedPost = $this->postService->postUpdate($request, $post);

        return response()->json([
            "message"   => "Post muvaffaqiyatli yangilandi",
            "post"      => new PostResource($updatedPost)
        ]);
    }

    // Delete Post
    public function destroy(int $post_id)
    {
        $response = $this->postService->postDelete($post_id);

        return $response;
    }
}
