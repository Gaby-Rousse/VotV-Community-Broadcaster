<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChannelsController extends Controller
{
    private const RADIO = 1;
    private const TV = 2;

    public function index(): JsonResponse
    {
        $channels = DB::table('channels')
            ->select(['id', 'name', 'normalized_name'])
            ->orderBy('id');

        return response()->json([
            'radio' => (clone $channels)
                ->whereRaw('(display_rules & ?) = ?', [self::RADIO, self::RADIO])
                ->get()
                ->map(fn (object $channel) => $this->channelData($channel, 'radio')),
            'tv' => (clone $channels)
                ->whereRaw('(display_rules & ?) = ?', [self::TV, self::TV])
                ->get()
                ->map(fn (object $channel) => $this->channelData($channel, 'tv')),
        ]);
    }

    private function channelData(object $channel, string $station): array
    {
        return [
            'id' => "{$station}-{$channel->id}",
            'name' => $channel->normalized_name === 'everything' ? 'VotV Community Broadcast' : "VCB {$channel->name}",
            'url' => $this->streamUrl($station, $channel->normalized_name),
        ];
    }

    private function streamUrl(string $station, string $channel): string
    {
        $streamName = match ($channel) {
            'everything' => 'votv',
            'hip_hop' => 'votv_hiphop',
            'let_s_plays' => 'votv_letsplays',
            '5min' => 'votv_lt5min',
            'chaotic' => 'votv_lt30sec',
            default => "votv_{$channel}",
        };
        $extension = $station === 'radio' ? 'mp3' : 'mp4';

        return "https://{$station}.votvbroadcast.com/{$streamName}.{$extension}";
    }
}
