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
            'radio' => DB::table('channels')
                ->select(['id', 'generator_name as name', 'radio_url as url'])
                ->whereNotNull('radio_url')
                ->orderBy('generator_order')
                ->get()
                ->values(),
            'tv' => DB::table('channels')
                ->select(['id', 'generator_name as name', 'tv_url as url'])
                ->whereNotNull('tv_url')
                ->orderBy('generator_order')
                ->get()
                ->values(),
        ]);
    }
}
