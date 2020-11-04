<?php

namespace App\Http\Controllers;

use App\Classes\PictureHelper;
use App\Picture;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            $page = $request->input('page');
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

    public function deliver(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $picture = $user->pictures()->find($id);
        if (!$picture) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        return Storage::download(PictureHelper::getUserPictureStoragePath($user) . '/' . $picture->file_name);
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
            'url' => Storage::url(PictureHelper::getUserPictureStoragePath($user) . '/' . $picture->file_name),
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
        $picture = new Picture();
        $picture->file_name = $request->file('file')->getClientOriginalName();
        if ($user->pictures()->where('file_name', $picture->file_name)->count()) {
            return response()->json([
                'type' => "error",
                'message' => "Picture with the same name already exists!",
            ], 400);
        }
        $file = $request->file('file')->storeAs(PictureHelper::getUserPictureStoragePath($user), $picture->file_name);
        $picture->file_path = $file;
        $user->pictures()->save($picture);
        if ($picture->id) {
            return [
                'type' => 'success',
                'message' => 'Successfully saved picture!',
                'data' => $picture->toArray(),
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
        Storage::delete($picture->file_path);
        $picture->file_name = $request->file('file')->getClientOriginalName();
        $file = $request->file('file')->storeAs(PictureHelper::getUserPictureStoragePath($user), $picture->file_name);
        $picture->file_path = $file;
        $user->pictures()->save($picture);
        return [
            'type' => 'success',
            'message' => 'Successfully saved picture!',
            'data' => $picture->toArray()
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
            'data' => $picture->toArray()
        ];
    }
}
