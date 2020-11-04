<?php

namespace App\Http\Controllers;

use App\Poster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PosterController extends Controller
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
        $posters = $user->posters()->skip(($page-1)*$items)->take($items)->get();
        return [
            'type' => "success",
            'count' => $posters->count(),
            'data' => $posters->toArray(),
        ];
    }

    public function show(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $poster = $user->posters()->find($id);
        if (!$poster) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        return [
            'type' => "success",
            'data' => $poster->toArray(),
        ];
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'string',
            'picture_id' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors(),
            ], 400);
        }
        $picture = $user->pictures()->find($request->input('picture_id'));
        if (!$picture) {
            return response()->json([
                'type' => "error",
                'message' => "Unknown picture!",
            ], 400);
        }
        $poster = new Poster();
        $poster->title = $request->input('title');
        $poster->description = $request->input('description', '');
        $poster->picture_id = $picture->id;
        $user->posters()->save($poster);
        if (!$poster->id) {
            return response()->json([
                'type' => "error",
                'message' => "Something went wrong.",
            ], 400);
        }
        return [
            'type' => 'success',
            'message' => 'Successfully created poster!',
            'data' => $poster->toArray(),
        ];
    }

    public function update(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $poster = $user->posters()->find($id);
        if (!$poster) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'string',
            'picture_id' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'type' => "error",
                'message' => $validator->errors(),
            ], 400);
        }
        $picture = $user->pictures()->find($request->input('picture_id'));
        if (!$picture) {
            return response()->json([
                'type' => "error",
                'message' => "Unknown picture!",
            ], 400);
        }
        $poster->title = $request->input('title');
        $poster->description = $request->input('description', '');
        $poster->picture_id = $picture->id;
        $user->posters()->save($poster);
        if (!$poster->id) {
            return response()->json([
                'type' => "error",
                'message' => "Something went wrong.",
            ], 400);
        }
        return [
            'type' => 'success',
            'message' => 'Successfully updated poster!',
            'data' => $poster->toArray(),
        ];
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        $poster = $user->posters()->find($id);
        if (!$poster) {
            return response()->json([
                'type' => "error",
                'message' => "Not found!",
            ], 404);
        }
        if (!$poster->delete()) {
            return response()->json([
                'type' => "error",
                'message' => "Something went wrong.",
            ], 400);
        }
        return [
            'type' => 'success',
            'message' => 'Successfully deleted poster!',
            'data' => $poster->toArray(),
        ];
    }
}
