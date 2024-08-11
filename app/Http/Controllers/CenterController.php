<?php

namespace App\Http\Controllers;
use App\Models\Centerimg;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Models\Centerinfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;


class CenterController extends Controller
{
public function addText(Request $request)
{
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'text' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), 'Validation failed', 422);
        }

        $text = Centerinfo::create($request->all());

        return ResponseHelper::success([
            'text' => $text,
            'message' => 'Text created'
        ], 201);
}
public function updateText(Request $request, $centerinfo_id)
{
        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'text' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), 'Validation failed', 422);
        }

        $text = Centerinfo::find($centerinfo_id);

        if (!$text) {
            return ResponseHelper::error([], null, 'Center Text not found', 404);
        }

        $text->update($request->all());

        return ResponseHelper::success([
            'text' => $text,
            'message' => 'Text updated'
        ], 201);
}
public function getText($centerinfo_id)
{
        $text = Centerinfo::find($centerinfo_id);

        if (!$text) {
            return ResponseHelper::error([], null, 'Text not found', 404);
        }

        return ResponseHelper::success($text, 'Text retrieved');
}
public function deleteText($centerinfo_id)
{
        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $text = Centerinfo::find($centerinfo_id);

        if (!$text) {
            return ResponseHelper::error([], null, 'Text not found', 404);
        }

        $text->delete();

        return ResponseHelper::success([], 'Text deleted');
}
public function addImage(Request $request)
{
        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'string'],
            'centerinfo_id' => ['required', 'integer', Rule::exists('centerinfos', 'id')],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }

        $img = Centerimg::create($request->all());

        return ResponseHelper::created($img, 'Image created');
}
public function updateImage(Request $request, $centerimg_id)
{
        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'string'],
            'centerinfo_id' => ['required', 'integer', Rule::exists('centerinfos', 'id')],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }

        $img = Centerimg::find($centerimg_id);

        if (!$img) {
            return ResponseHelper::error([], null, 'Center image not found', 404);
        }

        $img->update($request->all());

        return ResponseHelper::updated($img, 'Image updated');
}
public function getImage($centerimg_id)
{
        $img = Centerimg::find($centerimg_id);
        if (!$img) {return ResponseHelper::error([], null, 'Center image not found', 404);}
        return ResponseHelper::success($img, 'Text retrieved');
}
public function deleteImage($centerimg_id)
{
      if (Auth::user()->role != '2') {return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));}
        $img = Centerimg::find($centerimg_id);
        if (!$img) {return ResponseHelper::error([], null, 'Image not found', 404);}
        $img->delete();
        return ResponseHelper::success([], 'Image deleted');
}
}


