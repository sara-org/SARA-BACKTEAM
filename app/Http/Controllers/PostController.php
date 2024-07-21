<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class PostController extends Controller
{
    public function addPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
        ]);

        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        $postData = [
            'text' => $request->input('text'),
            'user_id' => Auth::user()->id,
        ];

        $post = Post::create($postData);

        return ResponseHelper::created($post, 'Post added successfully');
    }

    public function updatePost(Request $request, $post_id)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
        ]);

  if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        try {
            $post = Post::findOrFail($post_id);
            $post->text = $request->input('text');
            $post->save();

            return ResponseHelper::success($post, 'Post updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to update post', 500);
        }
    }

    public function deletePost($post_id)
    {
        try {
            if (Auth::user()->role != '2') {
                return ResponseHelper::error(null, null, 'Unauthorized', 401);
            }
            $post = Post::findOrFail($post_id);
            $post->delete();

            return ResponseHelper::success([], 'Post deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to delete post', 500);
        }
    }

    public function getPostById($id)
    {
        try {
            $post = Post::with('likes','comments')->findOrFail($id);
            $post['is_owner'] = $post['is_owner'];
            $post['is_liked'] = $post['is_liked'];

            return ResponseHelper::success($post, 'Post retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to retrieve post', 500);
        }
    }

    public function getAllPosts()
    {
        try {
            $posts = Post::with('likes','comments')->get();

            return ResponseHelper::success($posts, 'Posts retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to retrieve posts', 500);
        }
    }
}
