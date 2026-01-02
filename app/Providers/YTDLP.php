<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class YTDLP
{
    /**
     * Download a file using YT-DLP
     * @param string $url
     * @param string $type
     * This code is from a silly jukebox site I made
     * TO BE HONEST, I'M CONFUSED BY THE WIZARDRY DONE THERE!
     * I remember using this to help me: https://ostechnix.com/yt-dlp-tutorial/
     */
    public static function download(string $url, string $type)
    {
        $table = Functions::retrieveDestinationTable();
        $ext = $table == 'audios' ? '.mp3' : '.mp4';

        $urls = explode(',', $url);

        //https://www.php.net/manual/en/function.fastcgi-finish-request.php
        //Solution from god.
        echo json_encode(['type' => 'Info', 'message' => "Download is running in background."]);
        fastcgi_finish_request();
        //However users won't know when or why something fails.
        //I would prefer using the current log system (the popups)
        //But let start by the notification tab.

        foreach ($urls as $url) {
            $url = escapeshellarg($url);
            $retrieveTitle = 'yt-dlp --no-cache-dir --skip-download --print "%(title)s" ' . $url;
            $retrieveUploader = 'yt-dlp --no-cache-dir --skip-download --print "%(uploader)s" ' . $url;
            $title = trim(shell_exec($retrieveTitle));
            $artist = trim(shell_exec($retrieveUploader));

            $filename = Functions::formatFilename($title . $ext);
            $filepath = public_path('/temp_uploads/pending/' . $filename);
            $coverDirectory = public_path('/temp_uploads/covers/');
            $thumbnailBase = $coverDirectory . 'thumbnail_' . uniqid();
            $coverFilepathTemp = $coverDirectory . 'temp_' . uniqid() . '.png';

            $retrieveThumbnail = 'yt-dlp --no-cache-dir --write-thumbnail --skip-download --convert-thumbnails png --output "' . $thumbnailBase . '.%(ext)s" ' . $url;
            shell_exec($retrieveThumbnail);

            $downloadedThumbnail = $thumbnailBase . '.png';
            $finalCoverFilename = null;

            if (file_exists($downloadedThumbnail)) {
                $cropImage = 'ffmpeg -y -i "' . $downloadedThumbnail . '" -vf "crop=min(iw\,ih):min(iw\,ih)" "' . $coverFilepathTemp . '"';
                shell_exec($cropImage);

                if (file_exists($coverFilepathTemp)) {
                    $finalCoverFilename = md5_file($coverFilepathTemp) . '.png';
                    rename($coverFilepathTemp, $coverDirectory . $finalCoverFilename);
                }

                if (file_exists($downloadedThumbnail))
                    unlink($downloadedThumbnail);
            }

            if ($ext == '.mp3') {
                $retrieveMedia = 'yt-dlp --no-cache-dir -x --audio-format mp3 -o "' . $filepath . '" ' . $url;
            } else {
                $retrieveMedia = 'yt-dlp --no-cache-dir -S "res:720,ext:mp4:m4a" --recode-video mp4 -o "' . $filepath . '" ' . $url;
            }

            shell_exec($retrieveMedia);

            $media = new Media($filename, $title, $artist, $finalCoverFilename, 'Unknown', 'Unknown', 'Unknown', 'None', $type, Auth::id());
            Queries::insertMedia($media, $table);
            Functions::updateMetadataFromDB($filename);
            Queries::insertNotification(new Notification(0, Auth::id(), str_replace("'", "",$url) . ' downloaded.'));
        }
    }

}