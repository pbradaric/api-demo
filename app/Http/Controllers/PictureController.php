<?php

namespace App\Http\Controllers;

use App\Picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PictureController extends Controller
{
    public function index(Request $request)
    {
        $items = 50;
        $page = 1;
        if ($request->exists('page')) {
            $page = $request->get('page');
            if (!is_numeric($page) || $page < 1) {
                $page = 1;
            }
        }
        $user = Auth::guard('api')->user();
        $pictures = $user->pictures()->skip(($page-1)*$items)->take($items)->get();
        return [
            'type' => "success",
            'count' => $pictures->count(),
            'data' => $pictures->toArray(),
        ];
    }

    public function show(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $picture = $user->pictures()->find($id);
        if (!$picture) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        return [
            'type' => "success",
            'data' => $picture->toArray(),
        ];
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,gif,png',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors(),
            ], 400);
        }
        $file = $request->file('file')->store('public/pictures');
        $picture = new Picture();
        $picture->file_name = pathinfo($file, PATHINFO_BASENAME);
        $picture->file_path = $file;
        $user->pictures()->save($picture);
        if ($picture->id) {
            return [
                'type' => 'success',
                'message' => 'Successfully saved picture!',
                'data' => [
                    'id' => $picture->id,
                    'name' => $picture->file_name,
                    'path' => $picture->file_path,
                ]
            ];
        }
        return response()->json([
            'type' => "error",
            'message' => "Something went wrong.",
        ], 400);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $picture = $user->pictures()->find($id);
        if (!$picture) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,jpg,gif,png',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors(),
            ], 400);
        }
        // Delete old one first.
        $file = $request->file('file')->store('public/pictures');
        $picture->file_name = $file;
        $picture->file_path = $file;
        $picture->save();
        return [
            'type' => 'success',
            'message' => 'Successfully saved picture!',
            'data' => [
                'id' => $picture->id,
                'path' => $picture->file_path,
            ]
        ];
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $picture = $user->pictures()->find($id);
        if (!$picture) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        Storage::delete($picture->file_path);
        if (!$picture->delete()) {
            return response()->json([
                'type' => "error",
                'message' => "Something went wrong.",
            ], 400);
        }
        return [
            'type' => 'success',
            'message' => 'Successfully deleted picture!',
            'data' => [
                'picture_id' => $picture->id,
            ]
        ];
    }
}
