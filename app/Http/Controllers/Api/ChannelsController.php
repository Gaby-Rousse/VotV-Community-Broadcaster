<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChannelsController extends Controller
{
    private const RADIO_CHANNELS = [
        'everything',
        'sfw',
        'country',
        'classical',
        'electronic',
        'hip_hop',
        'instrumental',
        'jazz',
        'mariachi',
        'metal',
        'pop',
        'rock',
        'video_game',
        'weird',
    ];

    private const TV_CHANNELS = [
        'everything',
        'sfw',
        'animations',
        'documentaries',
        'horror',
        'let_s_plays',
        'memes',
        'shows',
        'vlogs',
        'news',
    ];

    public function index(): JsonResponse
    {
        $channels = DB::table('channels')
            ->select(['id', 'name', 'normalized_name'])
            ->get()
            ->keyBy('normalized_name');

        $radio = collect(self::RADIO_CHANNELS)
            ->map(fn (string $channel) => $this->channelData($channels->get($channel), 'radio', $channel))
            ->filter()
            ->values();

        $tv = collect(self::TV_CHANNELS)
            ->map(fn (string $channel) => $this->channelData($channels->get($channel), 'tv', $channel))
            ->filter()
            ->values()
            ->push([
                'id' => 'tv-lt5min',
                'name' => 'VCB < 5min',
                'url' => 'https://tv.votvbroadcast.com/votv_lt5min.mp4',
            ])
            ->push([
                'id' => 'tv-chaotic',
                'name' => 'VCB CHAOTIC',
                'url' => 'https://tv.votvbroadcast.com/votv_lt30sec.mp4',
            ]);

        return response()->json([
            'radio' => $radio,
            'tv' => $tv,
        ]);
    }

    private function channelData(?object $channel, string $station, string $normalizedName): ?array
    {
        if ($channel === null) {
            return null;
        }

        return [
            'id' => "{$station}-{$channel->id}",
            'name' => $normalizedName === 'everything' ? 'VotV Community Broadcast' : "VCB {$channel->name}",
            'url' => $this->streamUrl($station, $normalizedName),
        ];
    }

    private function streamUrl(string $station, string $channel): string
    {
        $streamName = match ($channel) {
            'everything' => 'votv',
            'let_s_plays' => 'votv_letsplays',
            default => "votv_{$channel}",
        };
        $extension = $station === 'radio' ? 'mp3' : 'mp4';

        return "https://{$station}.votvbroadcast.com/{$streamName}.{$extension}";
    }
}
