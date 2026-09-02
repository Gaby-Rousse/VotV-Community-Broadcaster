<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChannelsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'radio' => DB::table('browse_audio')
                ->select(['id', 'long_name as name', 'url'])
                ->orderBy('sort_order')
                ->get()
                ->values(),
            'tv' => DB::table('browse_video')
                ->select(['id', 'long_name as name', 'url'])
                ->orderBy('sort_order')
                ->get()
                ->values(),
        ]);
    }
}
