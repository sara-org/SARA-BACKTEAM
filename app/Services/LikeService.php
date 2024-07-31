<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Http\Requests\LikeRequest;
use App\Models\Like;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LikeService
{
    public function likePost(LikeRequest $request)
{
    $data = $request->validated();
    $data['user_id'] = auth('sanctum')->user()->id;
    $data['like_date'] = Carbon::now()->format('Y-m-d H:i:s');
    $like = Like::create($data);
    return $like;
}
}
