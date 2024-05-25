<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    public function likePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        $likeData = [
            'user_id' => $request->input('user_id'),
            'post_id' => $request->input('post_id'),
            'like_date' => now(),
        ];

        $like = Like::create($likeData);

        return ResponseHelper::created($like, 'Post liked successfully');
    }

    public function unlikePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        $userId = $request->input('user_id');
        $postId = $request->input('post_id');

        Like::where('user_id', $userId)->where('post_id', $postId)->delete();
        Like::where('user_id', $userId)->where('post_id', $postId)->update(['like_date' => now()]);

        return ResponseHelper::success([], 'Post unliked successfully');
    }
}