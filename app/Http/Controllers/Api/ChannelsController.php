<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChannelsController extends Controller
{
    public function index(): JsonResponse
    {
        $channels = DB::table('channels')
            ->select(['id', 'name', 'normalized_name'])
            ->orderBy('id')
            ->get()
            ->map(fn ($channel) => [
                'id' => $channel->id,
                'name' => $channel->name,
                'radio_url' => $this->streamUrl('radio', $channel->normalized_name),
                'tv_url' => $this->streamUrl('tv', $channel->normalized_name),
            ]);

        return response()->json($channels);
    }

    private function streamUrl(string $station, string $channel): string
    {
        $streamName = $channel === 'everything' ? 'votv' : "votv_{$channel}";
        $extension = $station === 'radio' ? 'mp3' : 'mp4';

        return "https://{$station}.votvbroadcast.com/{$streamName}.{$extension}";
    }
}
