<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::query()->active()->with('user')->paginate(20);

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;

        if ($data['published_at'] === null && $data['is_draft'] === false) {
            $data['published_at'] = now();
        }

        if ($data['published_at'] >= now()) {
            $data['is_draft'] = false;
        }

        $post = Post::create($data);

        return response()->json($post, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return response()->json($post, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        if ($request->user()->cannot('update', $post)) {
            return response()->json(['message' => 'You are not authorized to update this post.'], 403);
        }

        $data = $request->validated();

        $post->update($data);

        return response()->json($post, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post)
    {
        if ($request->user()->cannot('delete', $post)) {
            return response()->json(['message' => 'You are not authorized to delete this post.'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully.'], 204);
    }
}
