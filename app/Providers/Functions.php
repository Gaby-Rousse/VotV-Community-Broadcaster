<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\MediaHelper;
use App\Models\Notification;
use App\Models\User;
use FFMpeg\FFProbe;

use getID3;
use getid3_writetags;
use Illuminate\Foundation\Application;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Functions
{

    //https://github.com/kiwilan/php-audio
    /**
     * Parse the metadata from a file using PHP-AUDIO
     * @param string $filename - Filename to parse the data from
     * @param string $type - The type of the file (Media, Event, Ad, Segue)
     * @return Media|string
     */
    /*
    static function parseMetadata(string $filename, string $type)
    {
        try {
            $audio = Audio::read(public_path('/temp_uploads/pending/' . $filename));

            $title = $audio->getTitle(); // `?string` to get title
            if (!$title) {
                $title = trim($filename, '.mp3');
                //Il n'y avait pas de titre, donc ajoute les aux métadonnées...
                $audio->write()->title($title)->save();
            }

            $artist = $audio->getArtist(); // `?string` to get artist
            if (!$artist) {
                $artist = 'Unknown Artist';
            }

            $album = $audio->getAlbum(); // `?string` to get album
            if (!$album) {
                $album = 'Unknown';
            }

            $genre = $audio->getGenre(); // `?string` to get genre
            if (!$genre) {
                $genre = 'Unknown';
            }

            if ($type != 'media') {
                $genre = ucfirst($type);
            }

            $year = $audio->getYear(); // `?int` to get year
            if (!$year) {
                $year = 'Unknown';
            }

            $description = $audio->getDescription(); // `?string` to get description
            if (!$description) {
                $description = 'None';
            }

            $cover = $audio->getCover();
            if (!$cover) {
                $coverFileName = 'unknown.png';
            } else {
                //https://www.php.net/manual/en/function.explode.php
                $mimeType = $cover->getMimeType();
                try {
                    $extension = explode('/', $mimeType)[1];
                    $coverFileName = self::formatFilename($title . '.' . $extension);

                    //https://www.w3schools.com/php/func_filesystem_file_put_contents.asp
                    file_put_contents(public_path('temp_uploads/covers/') . $coverFileName, $cover->getContents());
                } catch (\Exception $e) {
                    $coverFileName = 'unknown.png';
                }
            }

            return new Media($filename, $title, $artist, $coverFileName, $album, $genre, $year, $description, $type, Auth::user()->username);

        } catch (\Exception $e) {
            return json_encode($e->getMessage());
        }
    } */

    /**
     * Parse the metadata from a file using getID3
     * @param string $filename - Filename to parse the data from
     * @param string $type - The type of the file (Media, Event, Ad, Segue)
     * @return Media|string|array
     */
    static function parseMetadata(string $filename, string $type = 'media')
    {
        $getID3 = new GetID3();

        $filepath = public_path('/temp_uploads/pending/' . $filename);
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $filenameWithoutExt = pathinfo($filepath, PATHINFO_FILENAME);
        $thisFileInfo = $getID3->analyze($filepath);
        $getID3->CopyTagsToComments($thisFileInfo);
        if ($ext == 'mp3') {
            if (isset($thisFileInfo['tags']))
                $info = $thisFileInfo['tags']['id3v2'];
        } else if ($ext == 'mp4') {
            if (isset($thisFileInfo['tags']))
                $info = $thisFileInfo['tags']['quicktime'];
        }

        //Default values
        $title = $filenameWithoutExt;
        $artist = 'Unknown Artist';
        $album = 'Unknown';
        $genre = 'Unknown';
        $year = 'Unknown';
        $description = 'None';
        $coverFileName = 'unknown.png';

        if (isset($thisFileInfo['comments']['picture'])) {
            if ($coverData = $thisFileInfo['comments']['picture'][0]['data']) {
                $coverMime = $thisFileInfo['comments']['picture'][0]['image_mime'];
                $extension = explode("/", $coverMime)[1];
                $extension = preg_replace('/[^a-zA-Z0-9]/', '', $extension);
                $coverFileName = md5($coverData) . '.' . $extension;
                $coverFilepath = public_path('/temp_uploads/covers/' . $coverFileName);
                file_put_contents($coverFilepath, $coverData);
            }
        }

        if (isset($info['title'][0])) {
            $title = $info['title'][0];
        } else {
            //No title? We'll extract the filename and write it to the file immediately.
            self::updateMetadata(array('title' => array($title)), $filepath);
        }

        if (isset($info['artist'][0])) {
            $artist = $info['artist'][0];
        }

        if (isset($info['genre'][0])) {
            $genre = $info['genre'][0];
        }

        if (isset($info['year'][0])) {
            $year = $info['year'][0];
        }

        if (isset($info['comment'][0])) {
            $description = $info['comment'][0];
        }

        return new Media($filename, $title, $artist, $coverFileName, $album, $genre, $year, $description, $type, Auth::id());
    }

    static function updateMetadata(array $tags, $filepath)
    {
        $TextEncoding = 'UTF-8';
        $getID3 = new GetID3();
        $getID3->setOption(array('encoding' => $TextEncoding));

        $tagwriter = new Getid3_writetags();
        $tagwriter->filename = $filepath;

        if (self::retrieveDestinationTable() == 'audios')
            $tagwriter->tagformats = array('id3v2.4');
        else
            $tagwriter->tagformats = array('quicktime');


        $tagwriter->overwrite_tags = true;
        $tagwriter->remove_other_tags = true;
        $tagwriter->tag_encoding = $TextEncoding;

        if (isset($tags['cover']) && file_exists($tags['cover'])) {
            $imagePath = $tags['cover'];
            $imageInfo = getimagesize($imagePath);

            if ($imageInfo !== false) {
                $tags['attached_picture'][0] = [
                    'data' => file_get_contents($imagePath),
                    //front
                    'picturetypeid' => 0x03,
                    'description' => 'cover',
                    'mime' => $imageInfo['mime'],
                ];
                //not included in tag_data
                unset($tags['cover']);
            }
        }

        $tagwriter->tag_data = $tags;

        if (!$tagwriter->WriteTags()) {
            return false;
        }

        return true;
    }

    static function updateMetadataFromDB($filename)
    {
        $mediaHelper = self::generateMediaHelper($filename);

        if ($mediaHelper->media->approved == 0)
            $coverFilePath = public_path('/temp_uploads/covers/');
        else
            $coverFilePath = public_path('/uploads/covers/');

        $tags = [
            'title' => array($mediaHelper->media->title),
            'artist' => array($mediaHelper->media->artist),
            'genre' => array($mediaHelper->media->genre),
            'year' => array($mediaHelper->media->year),
            'comment' => array($mediaHelper->media->description),
            'cover' => $coverFilePath . $mediaHelper->media->cover,
        ];
        self::updateMetadata($tags, $mediaHelper->mediaPath);
    }

    /**
     * Creates a mediaHelper (see class)
     * @param string $filename
     * @param bool $approving - When requesting this MediaHelper, are you currently approving it?
     * @return MediaHelper
     */
    static function generateMediaHelper(string $filename, bool $approving = false): MediaHelper
    {
        $folder = self::retrieveDestinationTable();

        $media = Queries::receiveMedia($filename);

        $mediaPath = '';
        if (Queries::isPending($filename, $folder) && !$approving) {
            $mediaPath = public_path('/temp_uploads/pending/' . $filename);
            $coverPath = public_path('/temp_uploads/covers/');
            $media->isPending = true;
        } else {
            if ($media->type === 'media') {
                $mediaPath = public_path('/uploads/' . $folder . '/medias/' . $filename);
            } else if ($media->type === 'event') {
                $mediaPath = public_path('/uploads/' . $folder . '/events/' . $filename);
            } else if ($media->type === 'ad') {
                $mediaPath = public_path('/uploads/' . $folder . '/advertisements/' . $filename);
            } else if ($media->type === 'segue') {
                $mediaPath = public_path('/uploads/' . $folder . '/segues/' . $filename);
            }
            $coverPath = public_path('/uploads/covers/');
            $media->isPending = false;
        }
        return new MediaHelper($media, $folder, $mediaPath, $coverPath);
    }


    /**
     * Retrieve the table according to the value stored in the session
     * @return string
     */
    static function retrieveDestinationTable()
    {
        $table = session('media_type');
        if (!$table) {
            $table = 'audios';
        }
        return $table;
    }


    //https://en.wikipedia.org/wiki/PLS_(file_format) (easy peasy :3)
    //https://www.w3schools.com/PHP/php_file_create.asp


    /**
     * Generates/update a playlist
     * @param string $pDestination - The playlist to create/update
     * @return void
     */
    static function generatePlaylist(string $pDestination)
    {
        $table = self::retrieveDestinationTable();
        $availableChannels = ['christmas', 'classical', 'country', 'electronic', 'hip hop', 'instrumental', 'jazz', 'mariachi', 'metal', 'pop', 'rock', 'video game', 'weird', 'animations', 'documentaries', 'horror', "let's plays", 'memes', 'news', 'shows', 'vlogs'];
        $eventChannels = ['strange [4%]', 'weird [2%]', 'bizarre [1%]', 'outlandish [0.4%]', 'unfathomable [0.2%]', 'otherworldly [0.1%]', 'transcendental [0.04%]'];
        $prefix = '';

        $channel = strtolower($pDestination);
        //Si le channel n'est pas diffusé, pas de playlist boss
        if (!in_array($channel, $availableChannels) && !in_array($channel, $eventChannels)) {
            return;
        }
        if ($table == 'videos' && in_array($channel, $eventChannels)) {
            $prefix = 'v_';
        }
        $file = fopen(public_path('uploads/playlists/' . $prefix . $channel) . '.pls', "w");
        fwrite($file, "[playlist]\n");
        $values = DB::table($table)->where('destination', $channel)->get();
        $i = 1;
        if (in_array($channel, $availableChannels)) {
            foreach ($values as $value) {
                fwrite($file, "file" . $i . "=../public/uploads/" . $table . "/medias/" . $value->filename . "\n");
                $i++;
            }
        } else if (in_array($channel, $eventChannels)) {
            foreach ($values as $value) {
                fwrite($file, "file" . $i . "=../public/uploads/" . $table . "/events/" . $value->filename . "\n");
                $i++;
            }
        }
        fclose($file);


    }

    /**
     * Repetitive.
     * Creates a playlist according to the available durations. Always triggered whenever a new media is probed
     * @return void
     */
    static function generateDurationsPlaylist()
    {
        $channels = ['lt30sec' => '30', 'lt5min' => '300', 'lt15min' => '900'];
        foreach ($channels as $channel => $time) {
            $file = fopen(public_path('uploads/playlists/' . $channel) . '.pls', "w");
            fwrite($file, "[playlist]\n");
            $values = DB::table('videos')->where('type', '=', 'media')->where('duration', '<', $time)->get();
            $i = 1;
            foreach ($values as $value) {
                fwrite($file, "file" . $i . "=../public/uploads/videos/medias/" . $value->filename . "\n");
                $i++;
            }
            fclose($file);
        }
    }

    /**
     * Repetitive.
     * Creates SFW playlist. Always triggered whenever a new media is probed and explicit is updated.
     * @return void
     */
    static function generateSafePlaylist()
    {
        $tables = ['audios', 'videos'];
        $folders = ['media', 'event', 'ad'];
        foreach ($tables as $table) {
            foreach ($folders as $folder) {
                $file = fopen(public_path('uploads/playlists/safe_' . $folder . '_' . $table) . '.pls', "w");
                fwrite($file, "[playlist]\n");
                $values = DB::table($table)->where('explicit', '=', 0)->where('type', '=', $folder)->get();
                $i = 1;
                foreach ($values as $value) {
                    fwrite($file, "file" . $i . "=../public/uploads/" . $table . '/' . $folder . 's' . '/' . $value->filename . "\n");
                    $i++;
                }
                fclose($file);
            }
        }
    }


    /**
     * Refactor a filename so it doesn't breaks everything
     * @param string $filename - The filename to format
     * @return string
     */
    static function formatFilename(string $filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);
        $maxFilenameLength = 240;
        if (strlen($filename_without_ext) > $maxFilenameLength) {
            $filename_without_ext = substr($filename_without_ext, 0, $maxFilenameLength);
        }
        $problematicCharacters = ['"', '#', '/', '?', '.', ','];
        $filename_without_ext = str_replace($problematicCharacters, '', $filename_without_ext);
        $filename_without_ext = self::trimUnicode($filename_without_ext);
        return $filename_without_ext . '.' . $ext;
    }

    /**
     * From this: https://stackoverflow.com/questions/33273210/php-fix-zero-width-space-inside-string-variable
     * Trim a string so it doesn't contain any "empty" character.
     * @param string $str the string to trim
     * @return string
     */
    static function trimUnicode($str)
    {
        return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $str);
    }

    /**
     * Insert durations of medias inside a database.
     * @param string $table - The concerned table
     * @return int - amount of durations inserted
     */
    static function insertDurations(string $table): int
    {
        //https://github.com/PHP-FFMpeg/PHP-FFMpeg?tab=readme-ov-file#ffprobe
        $ffprobe = FFProbe::create();
        $values = DB::table($table)->where('duration', 0)->get();
        foreach ($values as $value) {
            $mediaHelper = Functions::generateMediaHelper($value->filename);
            try {
                DB::table($table)->where('filename', $value->filename)->update(['duration' => $ffprobe->format($mediaHelper->mediaPath)->get('duration')]);
            } catch (\Exception $error) {

                DB::table($table)->where('filename', $value->filename)->delete();
                try {
                    unlink($mediaHelper->mediaPath);
                } catch (\Exception $ignored) {

                }
            }
        }
        return $values->count();
    }

    /**
     * Is the file unreadable according to ffmpeg?
     * https://stackoverflow.com/questions/58815980/how-can-i-tell-if-a-video-file-is-corrupted-ffmpeg
     * @param string $filepath
     * @return bool
     */
    static function validateFile(string $filepath): bool
    {
        $escapedFilepath = escapeshellarg($filepath);
        $cmd = "ffmpeg -v error -i $escapedFilepath -c copy -f null - > /dev/null 2>&1";
        exec($cmd, $output, $return_var);
        return $return_var == 0;
    }

    /**
     * For AutoRefreshedPanel. Insert the latest time you received something from the server
     * Therefore if the latest content uploaded isn't more recent than this value, you don't receive any new data
     * See isUpdated.
     * @param string $table
     * @return void
     */
    static function insertLatestUpdate(string $table): void
    {
        if ($table == 'notifications') {
            if (Auth::check())
                session(['created_at' => DB::table($table)->orderBy('created_at', 'desc')->where('to', Auth::id())->limit(1)->value('created_at')]);
        }
        session(['last_update_' . $table => DB::table($table)->orderBy('updated_at', 'desc')->limit(1)->value('updated_at')]);
    }

    /**
     * When the autoRefreshedPanel request for a view from a controller, it checks this value
     * If the latest changes on the database (either a new update or insert) is more recent it will send the updated view, otherwise, null.
     * @param string $table
     * @return bool
     */
    static function isUpdated(string $table): bool
    {
        if ($table == 'notifications') {
            if (Auth::check())
                $result = DB::table($table)->orderBy('created_at', 'desc')->where('to', Auth::id())->limit(1)->value('created_at');
            if ($result) {
                return session('created_at') != $result;
            } else {
                return false;
            }
        }
        return session('last_update_' . $table) != DB::table($table)->orderBy('updated_at', 'desc')->limit(1)->value('updated_at');
    }
}
