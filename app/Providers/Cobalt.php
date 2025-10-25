<?php

namespace App\Providers;


use App\Models\Media;
use App\Models\Notification;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Http;

//https://github.com/imputnet/cobalt/blob/main/docs/api.md
//https://laravel.com/docs/12.x/http-client

class Cobalt
{
    public const API_URL = 'https://subito-c.meowing.de/';
    public const API_LOCAL = 'localhost:9000';

    /**
     * Download a file using a Cobalt remote API
     * @param string $url
     * @param string $type
     * @return string|void
     */
    public static function download(string $url, string $type)
    {
        $destinationTable = Functions::retrieveDestinationTable();
        $downloadMode = $destinationTable == 'audios' ? 'audio' : 'auto';

        $urls = explode(',', $url);

        //https://www.php.net/manual/en/function.fastcgi-finish-request.php
        //Solution from god.
        echo json_encode(['type' => 'Info', 'message' => "Download is running in background."]);
        fastcgi_finish_request();
        //However users won't know when or why something fails.
        //I would prefer using the current log system (the popups)
        //But let start by the notification tab.


        foreach ($urls as $url) {

            try {
                //https://github.com/imputnet/cobalt/blob/main/docs/api.md
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post(self::API_URL, [
                    'url' => $url,
                    'downloadMode' => $downloadMode,
                    'videoQuality' => '720',
                ]);
            } catch (\Exception $e) {
                Queries::insertNotification(new Notification('',session('connectedUser')->username,$url . ' ' . $e->getMessage()));
            }


            $collection = $response->collect();
            if (isset($collection['error'])) {
                echo json_encode(['type' => 'Error', 'message' => $collection['error']['code']]);
            }

            if ($collection['status'] == "local-processing") {
                $output = $collection['output'];
                $filename = Functions::formatFilename($output['filename']);
                $title = $output['metadata']['title'];
                $coverFilename = $title . '.png';
                //dd($collection);

                //Why? I guess file_get_contents is sus
                //https://stackoverflow.com/questions/11680709/file-get-contents-give-me-403-forbidden
                ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');

                $filepath = public_path('/temp_uploads/pending/' . $filename);
                $coverFilepath = public_path('/temp_uploads/covers/' . $coverFilename);

                if ($output['type'] == 'audio/mpeg') {

                    $audio = $collection['tunnel'][0];
                    $cover = $collection['tunnel'][1];
                    if (file_put_contents($filepath, file_get_contents($audio))) {
                        $media = new Media($filename, $title, $output['metadata']['artist'] ?? 'Unknown Artist', 'unknown.png', $output['metadata']['album'] ?? 'Unknown', 'Unknown', 'Unknown', 'None', $type, session('connectedUser')->username);
                        if (file_put_contents($coverFilepath, file_get_contents($cover))) {
                            $media->cover = $coverFilename;
                        }
                        Queries::insertMedia($media, Functions::retrieveDestinationTable());
                        Queries::insertNotification(new Notification('',session('connectedUser')->username,$url . ' downloaded.'));
                    } else {
                        Queries::insertNotification(new Notification('',session('connectedUser')->username,$url . ' failed to download.'));
                            //json_encode(['type' => 'Error', 'message' => "File failed to download"]);
                    }
                } else if ($output['type'] == 'video/mp4') {
                    //First tunnel.mp4 is only the video
                    //Second tunnel.mp4 is only the audio
                    $video = $collection['tunnel'][0];
                    $audio = $collection['tunnel'][1];
                    $tmpVideo = public_path('/temp_uploads/merge/' . 'video_' . $filename);
                    $tmpAudio = public_path('/temp_uploads/merge/' . 'audio_' . $filename);
                    if (file_put_contents($tmpVideo, file_get_contents($video))) {
                        if (file_put_contents($tmpAudio, file_get_contents($audio))) {
                            //Pretty much copied from here
                            //https://stackoverflow.com/questions/61818186/ffmpeg-merge-audio-and-video-files
                            $ffmpeg = FFMpeg::create();
                            $advancedMedia = $ffmpeg->openAdvanced([$tmpVideo, $tmpAudio]);
                            //TODO: Technically, copying the audio and the video into the output would be better and require less resources.
                            //Tunnel[0] always contains a .mp4 file in x264
                            //Tunnel[1] always contains a .mp4 file in aac
                            $advancedMedia->map([], new X264('aac', 'libx264'), $filepath)->save();
                            $media = new Media($filename, $title, $output['metadata']['artist'] ?? 'Unknown Artist', 'unknown.png', $output['metadata']['album'] ?? 'Unknown', 'Unknown', 'Unknown', 'None', $type, session('connectedUser')->username);
                            Queries::insertMedia($media, Functions::retrieveDestinationTable());
                            unlink($tmpVideo);
                            unlink($tmpAudio);
                            Queries::insertNotification(new Notification('',session('connectedUser')->username,$url . ' downloaded.'));
                        }
                        else
                        {
                            Queries::insertNotification(new Notification('',session('connectedUser')->username,$url . ' failed to download.'));
                            //      echo json_encode(['type' => 'Error', 'message' => "File failed to download"]);
                        }

                    } else {
                        Queries::insertNotification(new Notification('',session('connectedUser')->username,$url . ' failed to download.'));
                        //      echo json_encode(['type' => 'Error', 'message' => "File failed to download"]);
                    }

                }


            }


        }
    }

}