<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Helper\ResponseHelper;
use App\Http\Requests\LikeRequest;
use App\Services\LikeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    public function likePost(LikeRequest $request)
    {
        
        $like = app(LikeService::class)->likePost($request);
        return ResponseHelper::created($like, 'Post liked successfully');
    }

    public function unlikePost(Request $request, $post)
{

    $like = Like::where('user_id',auth('sanctum')->user()->id)->where('post_id',$post)->delete();

    if ($like) {
        return ResponseHelper::success([], 'Post unliked successfully');
    } else {
        return ResponseHelper::error([], 'Like not found', null, 404);
    }
}
}
