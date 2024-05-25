<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\Post;
use app\Models\Comment;
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
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',

        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        $commentData = [
            'comment' => $request->input('comment'),
            'user_id' => $request->input('user_id'),
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
            $comment->text = $request->input('comment');
            $comment->save();

            return ResponseHelper::success($comment, 'Comment updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to update comment', 500);
        }
    }

    public function deleteComment($comment_id)
    {
        try {
            $comment = Comment::findOrFail($comment_id);
            $comment->delete();

            return ResponseHelper::success([], 'Comment deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to delete comment', 500);
        }
    }

    public function getCommentById($id)
    {
        try {
            $comment = Comment::findOrFail($id);

            return ResponseHelper::success($comment, 'Comment retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'Failed to retrieve comment', 500);
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
}