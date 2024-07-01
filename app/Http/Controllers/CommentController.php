<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\Post;
use App\Models\Comment;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class CommentController extends Controller
{
    public function addComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        $commentData = [
            'comment' => $request->input('comment'),
            'user_id' => auth()->user()->id,
            'post_id' => $request->input('post_id'),
        ];

        $comment = Comment::create($commentData);

        return ResponseHelper::created($comment, 'Comment added successfully');
    }

    public function updateComment(Request $request, $comment_id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        try {
            $comment = Comment::findOrFail($comment_id);
            if (auth()->user()->id !== $comment->user_id) {return ResponseHelper::error([], 'Unauthorized', 'You are not authorized to update this comment', 401); }
            $comment->comment = $request->input('comment');
            $comment->save();
            return ResponseHelper::success($comment, 'Comment updated successfully');
        }
         catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to update comment', 500);
        }
    }

    public function deleteComment($comment_id)
{
    try {
        $comment = Comment::findOrFail($comment_id);
        if (auth()->user()->id !== $comment->user_id)
        { return ResponseHelper::error([], 'Unauthorized', 'You are not authorized to delete this comment', 401);}
        $comment->delete();
        return ResponseHelper::success([], 'Comment deleted successfully');
    } catch (\Exception $e) {
        return ResponseHelper::error([], $e->getMessage(), 'Failed to delete comment', 500);
    }
}

    public function getCommentById($id)
    {

            $comment = Comment::findOrFail($id);
            $comment['is_owner'] = $comment['is_owner'];
            return ResponseHelper::success($comment, null,'Comment retrieved successfully',200);

    }
    public function getUserComments()
{
    try {
        $user = auth()->user();
        $comments = Comment::where('user_id', $user->id)->get();

        return ResponseHelper::success($comments, 'User comments retrieved successfully');
    } catch (\Exception $e) {
        return ResponseHelper::error([], $e->getMessage(), 'Failed to retrieve user comments', 500);
    }
}
    public function getAllComments()
    {
        try {
            $comments = Comment::all();

            return ResponseHelper::success($comments, 'Comments retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to retrieve comments', 500);
        }
    }
    public function getPostComments($post_id)
{
    try {
        $comments = Comment::where('post_id', $post_id)->get();

        return ResponseHelper::success($comments, 'Comments retrieved successfully');
    } catch (\Exception $e) {
        return ResponseHelper::error([], $e->getMessage(), 'Failed to retrieve comments', 500);
    }
}
}
