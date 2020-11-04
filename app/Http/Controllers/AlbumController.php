<?php

namespace App\Http\Controllers;

use App\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
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
        $albums = $user->albums()->with('pictures')->skip(($page-1)*$items)->take($items)->get();
        return [
            'type' => "success",
            'count' => $albums->count(),
            'data' => $albums->toArray(),
        ];
    }

    public function show(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $album = $user->albums()->with('pictures')->find($id);
        if (!$album) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        return [
            'type' => "success",
            'data' => $album->toArray(),
        ];
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'pictures' => 'required|json',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors(),
            ], 400);
        }
        $pictureIds = json_decode($request->input('pictures'), true);
        if (!$pictureIds) {
            return response()->json([
                'type' => "error",
                'message' => "Non-empty array (i.e. [1,2,3]) required for \"pictures\" parameter.",
            ], 400);
        }
        $userPictures = $user->pictures()->whereIn('id', $pictureIds)->get();
        if ($userPictures->count() !== count($pictureIds)) {
            $unknownPictureIds = array_slice(array_diff($pictureIds, $userPictures->pluck('id')->toArray()), 0);
            return response()->json([
                'type' => "error",
                'message' => "Some pictures (".json_encode($unknownPictureIds).") do not exist or do not belong to current user.",
            ], 400);
        }
        $album = new Album([
            'name' => $request->input('name'),
        ]);
        $user->albums()->save($album);
        if (!$album->id) {
            return response()->json([
                'type' => "error",
                'message' => "Something went wrong.",
            ], 400);
        }
        $album->pictures()->attach($userPictures);
        return [
            'type' => 'success',
            'message' => 'Successfully created album!',
            'data' => $album->toArray(),
        ];
    }

    public function update(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'pictures' => 'required|json',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors(),
            ], 400);
        }
        $pictureIds = json_decode($request->input('pictures'), true);
        if (!$pictureIds) {
            return response()->json([
                'type' => "error",
                'message' => "Non-empty array (i.e. [1,2,3]) required for \"pictures\" parameter.",
            ], 400);
        }
        $userPictures = $user->pictures()->whereIn('id', $pictureIds)->get();
        if ($userPictures->count() !== count($pictureIds)) {
            $unknownPictureIds = array_slice(array_diff($pictureIds, $userPictures->pluck('id')->toArray()), 0);
            return response()->json([
                'type' => "error",
                'message' => "Some pictures (".json_encode($unknownPictureIds).") do not exist or do not belong to current user.",
            ], 400);
        }
        $album = $user->albums()->with('pictures')->find($id);
        if (!$album) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        $album->name = $request->input('name');
        $album->save();
        $album->pictures()->sync($userPictures);
        $album->refresh();
        return [
            'type' => 'success',
            'message' => 'Successfully updated album!',
            'data' => $album->toArray(),
        ];
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $album = $user->albums()->find($id);
        if (!$album) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        if (!$album->delete()) {
            return response()->json([
                'type' => "error",
                'message' => "Something went wrong.",
            ], 400);
        }
        return [
            'type' => 'success',
            'message' => 'Successfully deleted album!',
            'data' => $album->toArray(),
        ];
    }
}
