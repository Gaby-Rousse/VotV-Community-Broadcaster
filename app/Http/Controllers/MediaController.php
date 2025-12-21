<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Media;
use App\Models\MediaHelper;
use App\Models\Notification;
use App\Providers\Cobalt;
use App\Providers\Functions;
use App\Providers\Queries;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{

    function getSession()
    {
        return json_encode(session('keywords'));
    }

    //### FILTERS that adjusts the collection generated in getMedias
    function showFavorite(Request $request)
    {
        session(['favorite' => $request->get('value')]);
    }

    function setOwner(Request $request)
    {
        session(['owner' => $request->get('value')]);
    }

    function setChannel(Request $request)
    {
        session(['channel' => $request->get('value')]);
    }

    function setSearchKeywords(Request $request)
    {
        session(['keywords' => $request->get('keywords')]);
    }

    function setSorting(Request $request)
    {
        $desc = $request->get('desc');
        $sortBy = $request->get('sortBy');
        if ($sortBy) {
            session(['sortBy' => $sortBy]);
        }
        if ($desc) {
            session(['desc' => $desc]);
        }
    }

    function setType(Request $request)
    {
        $type = $request->get('type');
        if ($type) {
            session(['type' => $type]);
        }
    }


    /**
     * Add or remove a file from the list of favorites of a user.
     * @param Request $request
     * @return false|string
     */
    function toggleFavorite(Request $request)
    {
        $validated = $request->validate([
            'filename' => 'required'
        ]);
        $filename = $validated['filename'];
        if (Auth::check()) {
            if (DB::table(Functions::retrieveDestinationTable())->where('filename', $filename)->exists()) {
                if (DB::table('favorites')->where('filename', $filename)->where('username', Auth::user()->username)->exists()) {
                    DB::table('favorites')->where('filename', $filename)->where('username', Auth::user()->username)->delete();
                    //return json_encode($filename . ' removed from favorites');
                } else {
                    DB::table('favorites')->insert(['filename' => $filename, 'username' => Auth::user()->username]);
                    //return json_encode($filename . ' added to favorites');
                }
            } else {
                echo json_encode(['type' => 'Error', 'message' => "File doesn't exists!"]);
            }
        } else {
            echo json_encode(['type' => 'Warning', 'message' => "Not connected!"]);
        }
    }


    /**
     * Creates a new media
     * @param Request $request
     * @return false|string|void|null
     */
    function uploadMedia(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required',
        ]);

        $forceTranscode = $request->input('forceTranscode') ?? false;

        try {
            $table = Functions::retrieveDestinationTable();

            //Is the type available?
            $availableTypes = ['media', 'event', 'ad', 'segue'];
            $type = $validated['type'];

            if (!in_array($type, $availableTypes)) {
                echo json_encode(['type' => 'Error', 'message' => "Not a suitable type."]);
            }

            //Are you signed in?
            if (!auth::check()) {
                echo json_encode(['type' => 'Error', 'message' => "Not connected. Please sign in."]);
            }

            if ($_FILES['mediaFile']) {
                foreach ($_FILES["mediaFile"]["error"] as $key => $error) {
                    $originalName = Functions::formatFilename($_FILES["mediaFile"]["name"][$key]);
                    $currentFilename = $originalName;

                    if (DB::table($table)->get()->contains('filename', $originalName)) {
                        if (DB::table($table)->where('filename', $originalName)->where('owner', Auth::user()->username)->exists()) {
                            return json_encode(['type' => 'Warning', 'message' => "A file with this name already exists. You may edit it"]);
                        }
                        return json_encode(['type' => 'Warning', 'message' => "A file with this name already exists."]);
                    }
                    //Move file
                    $fileResult = move_uploaded_file($_FILES["mediaFile"]["tmp_name"][$key], public_path('/temp_uploads/pending/') . $originalName);
                    $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                    //https://stackoverflow.com/questions/173868/how-can-i-get-a-files-extension-in-php
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    if ($table == 'audios') {
                        if ($ext == 'mp3' && !$forceTranscode) {
                            $media = Functions::parseMetadata($originalName, $type);
                        } else {
                            $transcoded = true;
                            //Convert into mp3.
                            //https://github.com/PHP-FFMpeg/PHP-FFMpeg#audio
                            $ffmpeg = FFMpeg::create();
                            $audio = $ffmpeg->open(public_path('/temp_uploads/pending/') . $originalName);

                            $format = new Mp3();
                            $format->on('progress', function ($audio, $format, $percentage) {
                                //silence...
                                //echo "$percentage % transcoded";
                            });

                            $format
                                ->setAudioChannels(2)
                                ->setAudioKiloBitrate(128);

                            $newFilename = $filenameWithoutExt . '_xcode.mp3';
                            $newFilepath = public_path('/temp_uploads/pending/') . $newFilename;
                            $currentFilename = $newFilename;

                            $audio->save($format, $newFilepath);
                            //Delete the unconverted file
                            unlink(public_path('/temp_uploads/pending/') . $originalName);
                            $media = Functions::parseMetadata($newFilename, $type);
                        }

                    } else if ($table == 'videos') {

                        if ($ext == 'mp4' && !$forceTranscode) {
                            $media = Functions::parseMetadata($originalName, $type);
                        } else {
                            return json_encode(['type' => 'Info', 'message' => 'Video transcoding has been disabled temporally.']);
                            //Convert a file into a mp4
                            //https://github.com/PHP-FFMpeg/PHP-FFMpeg?tab=readme-ov-file#video
                            $ffmpeg = FFMpeg::create();
                            $video = $ffmpeg->open(public_path('/temp_uploads/pending/') . $originalName);

                            $format = new X264();
                            $format->on('progress', function ($video, $format, $percentage) {
                                //silence...
                                //echo "$percentage % transcoded";
                            });

                            $newFilename = $filenameWithoutExt . '_xcode.mp4';
                            $currentFilename = $newFilename;
                            $newFilepath = public_path('/temp_uploads/pending/') . $newFilename;

                            $format
                                ->setKiloBitrate(1000)
                                ->setAudioChannels(2)
                                ->setAudioKiloBitrate(256);
                            $video->save($format, $newFilepath);
                            unlink(public_path('/temp_uploads/pending/') . $originalName);
                            $media = Functions::parseMetadata($newFilename, $type);
                        }
                    }

                    //not a Media? We received an error.
                    if (!($media instanceof Media)) {
                        return json_encode(['type' => 'Error', 'message' => $media]);
                    }

                    if (!$fileResult) {
                        return json_encode(['type' => 'Error', 'message' => "move_uploaded_file returned false. No additionnal information available."]);
                    }

                    $filepath = public_path('/temp_uploads/pending/') . $currentFilename;

                    if (Functions::validateFile($filepath)) {
                        Queries::insertMedia($media, $table);
                    } else {
                        echo json_encode(['type' => 'Error', 'message' => "INVALID_FILE: FFMPEG CONSIDER THIS FILE CORRUPTED. You might want to try compatibility mode"]);
                        unlink($filepath);
                    }


                }
            }
        } catch (\Exception $e) {
            echo json_encode(['type' => 'Error', 'message' => $e->getMessage()]);
        }

    }

    /**
     * Downloads a file into the server using the API of a self hosted cobalt instance
     * See Cobalt Class
     * @param Request $request
     *
     */
    function importMedia(Request $request)
    {
        if (Auth::check()) {
            $validated = $request->validate([
                'url' => 'required',
                'type' => 'required',
            ]);
            Cobalt::download($validated['url'], $validated['type']);
        }
    }


    /**
     * Update a media entry.
     * @param Request $request
     * @return false|string|void
     * @throws \Exception
     */
    function updateMetadata(Request $request)
    {
        $validated = $request->validate([
            'filename' => 'required',
            'title' => 'required',
            'artist' => 'required',
            'genre' => 'required',
            'year' => 'required',
            'description' => 'required',
            'destination' => 'required',
            'type' => 'required',
        ]);

        try {


            $explicit = 0;


            $validTypes = ['media', 'ad', 'event', 'segue'];
            if (!in_array($validated['type'], $validTypes)) {
                return json_encode('Invalid folder.');
            }

            if ($request->input('explicit')) {
                $explicit = 1;
            }

            $filename = $validated['filename'];

            $mediaHelper = Functions::generateMediaHelper($filename);
            //Take the old destination and the new one and regenerate both playlist
            $oldDestination = $mediaHelper->media->destination;
            $newDestination = $validated['destination'];

            //Are you connected?
            if (Auth::check()) {
                //Are you the owner?
                if (!Queries::isOwner($filename, $mediaHelper->table) && !Auth::user()->isAdmin()) {
                    return json_encode(['type' => 'Error', 'message' => "Not the owner of the file."]);
                }
                //Good.
            } else {
                return json_encode(['type' => 'Error', 'message' => "Not connected."]);
            }


            if ($request->hasFile('cover')) {
                if ($request->file('cover')->isValid()) {
                    $cover = $request->file('cover');
                    $mime = $cover->getClientMimeType();
                    $coverFileName = Functions::formatFilename($cover->getClientOriginalName());
                    //Is the cover an image?
                    if (str_starts_with($mime, 'image/')) {
                        //On vérifie qu'une image avec ce nom n'existe pas. S'il y a le même nom on va réutiliser l'image du serveur, l'écrire au fichier et la BD.
                        if (DB::table($mediaHelper->table)->where('filename', $filename)->value('cover') != $coverFileName) {
                            $cover->move($mediaHelper->coverPath, $coverFileName);
                        }
                        $filepathCover = $mediaHelper->coverPath . $coverFileName;
                        DB::table($mediaHelper->table)->where('filename', $filename)->update(['title' => $validated['title'], 'artist' => $validated['artist'], 'genre' => $validated['genre'], 'year' => $validated['year'], 'description' => $validated['description'], 'cover' => $coverFileName, 'destination' => $validated['destination'], 'explicit' => $explicit, 'type' => $validated['type']]);
                    } else {
                        return json_encode(['type' => 'Error', 'message' => "Cover MIME type not allowed. Expected image/*"]);
                    }
                } else {
                    return json_encode(['type' => 'Error', 'message' => "Server consider this image as invalid"]);
                }
            } else {
                DB::table($mediaHelper->table)->where('filename', $filename)->update(['title' => $validated['title'], 'artist' => $validated['artist'], 'genre' => $validated['genre'], 'year' => $validated['year'], 'description' => $validated['description'], 'destination' => $validated['destination'], 'explicit' => $explicit, 'type' => $validated['type']]);
            }

            $tags = array(
                'title' => array($validated['title']),
                'artist' => array($validated['artist']),
                'genre' => array($validated['genre']),
                'year' => array($validated['year']),
                'comment' => array($validated['description']),
            );


            if (!Queries::isPending($mediaHelper->media->filename)) {
                //Update the playlists if the file is not inside the pending list.
                //We only regenerate for approved files since pending files are not broadcasted.
                Functions::generatePlaylist($oldDestination);
                Functions::generatePlaylist($newDestination);
                Functions::generateSafePlaylist();
            }


            //If the file is already approved we need to move it now.
            //Otherwise, approving it will move it.
            if (!Queries::isPending($filename)) {
                $sourceFile = $mediaHelper->mediaPath;
                $destinationPath = public_path('uploads/' . $mediaHelper->table . '/' . $validated['type'] . 's/');
                rename($sourceFile, $destinationPath . pathinfo($sourceFile, PATHINFO_BASENAME));
            }

            if ($mediaHelper->table == 'audios')
                Functions::updateMetadata($tags, $mediaHelper->mediaPath);
        } catch (\Exception $e) {
            echo json_encode(['type' => 'Error', 'message' => $e->getMessage()]);
        }

    }

    /**
     * Delete a media from the server
     * @param Request $request
     * @return false|string|void
     */
    function deleteMedia(Request $request)
    {
        if (Auth::check()) {
            $validated = $request->validate([
                //Si quelqu'un gosse avec le hidden
                'confirm' => 'required',
                'filename' => 'required',
                'reason' => '',
            ]);
            if (strtolower($validated['confirm']) == 'y') {
                $filename = $validated['filename'];
                $mediaHelper = functions::generateMediaHelper($filename);
                //es tu le propriétaire ou admin
                if (Queries::isOwner($filename, $mediaHelper->table) || Auth::user()->isAdmin()) {
                    DB::table($mediaHelper->table)->where('filename', $filename)->delete();
                    DB::table('reports')->where('filename', $filename)->delete();
                    if ($validated['reason']) {
                        Queries::insertNotification(new Notification(Auth::user()->username, $mediaHelper->media->owner, 'deleted ' . $filename . ' with the following reason: ' . $validated['reason']));
                    }
                    try {
                        unlink($mediaHelper->mediaPath);
                    } catch (\Exception $e) {
                        return json_encode(['type' => 'Error', 'message' => $e->getMessage()]);
                    }


                } else {
                    return json_encode(['type' => 'Error', 'message' => "Not owner of the file"]);
                }
            }
        }

    }

    /**
     * Moves a file from 'temp_uploads' toward 'uploads'
     * According to the type it will also move the file to the adequate folder (medias, events, ads, segues)
     * @param Request $request
     * @return void
     */
    function approveMedia(Request $request)
    {
        $validated = $request->validate([
            'filename' => 'required'
        ]);
        if (Auth::check()) {
            //es tu admin?
            if (Auth::user()->isAdmin()) {
                $filename = $validated['filename'];

                $mediaHelper = functions::generateMediaHelper($filename, true);
                Queries::approveMedia($filename, $mediaHelper->table);

                Functions::generatePlaylist($mediaHelper->media->genre);
                Functions::generateDurationsPlaylist();
                Functions::generateSafePlaylist();


                File::move(public_path('temp_uploads/pending/') . $filename, $mediaHelper->mediaPath);
                if (!File::exists(public_path('uploads/covers/') . $mediaHelper->media->cover)) {
                    File::move(public_path('temp_uploads/covers/') . $mediaHelper->media->cover, public_path('uploads/covers/') . $mediaHelper->media->cover);
                }
                Queries::insertNotification(new Notification(Auth::user()->username, $mediaHelper->media->owner, 'approved ' . $filename));
            }
        }
    }

    /**
     * Returns a view that contains the various approved medias according to various filters.
     * @param Request $request
     * @return Factory|View|Application|\Illuminate\View\View|object|null
     */
    function getMedias(Request $request)
    {
        $table = Functions::retrieveDestinationTable();

        if (!$request->get('forceRefresh') && !Functions::isUpdated($table)) {
            return null;
        }

        Functions::insertLatestUpdate($table);

        $type = session('type', 'media');
        $query = DB::table($table)->where('approved', 1);

        $keywords = strtolower(session('keywords', ''));
        if (!empty($keywords) && str_contains($keywords, ':')) {
            $parts = explode(':', $keywords, 2);
            $prefix = $parts[0];
            $token = $parts[1];

            $columns = ['title', 'artist', 'genre', 'year', 'filename'];

            if (in_array($prefix, $columns)) {
                $operator = ($prefix === 'filename') ? '=' : 'like';
                $value = ($prefix === 'filename') ? $token : "%$token%";
                $query->where($prefix, $operator, $value);
            }
        }

        $query->where('type', $type);

        if (session('channel') && session('channel') !== 'Everything') {
            $query->where('destination', session('channel'));
        }
        if (session('owner') == 1 && Auth::check()) {
            $query->where('owner', Auth::user()->username);
        }
        if (session('favorite') == 1 && Auth::check()) {
            $favorites = DB::table('favorites')
                ->where('username', Auth::user()->username)
                ->pluck('filename');
            $query->whereIn('filename', $favorites);
        }

        $sortBy = session('sortBy', 'title');
        $direction = (session('desc') == 1) ? 'desc' : 'asc';
        $query->orderBy($sortBy, $direction);

        $medias = $query->paginate(10);

        return view('panels/getMedias', ['medias' => $medias, 'count' => $medias->total()]);
    }

    /**
     * Returns a view that contains the pending medias. Filters are not applied there.
     * @param Request $request
     * @return Factory|View|Application|\Illuminate\View\View|object|null
     */
    function getPendingMedias(Request $request)
    {
        $table = Functions::retrieveDestinationTable();
        if ($request->get('forceRefresh') || Functions::isUpdated($table)) {
            Functions::insertLatestUpdate($table);

            if (Auth::check()) {
                if (Auth::user()->isAdmin()) {
                    $values = DB::table($table)->where('approved', '=', 0)->get();
                } else {
                    $values = DB::table($table)->where('approved', '=', 0)->where('owner', Auth::user()->username)->get();
                }
                return view('panels/getPendingMedias', ['medias' => $values, 'count' => count($values)]);
            }
        }
        return null;
    }

    /**
     * Returns a single file according to its filename.
     * @param Request $request
     * @return false|string
     */
    function getMedia(Request $request)
    {
        try {
            $filename = $request->get('filename');

            $mediaHelper = Functions::generateMediaHelper($filename);

            return json_encode($mediaHelper->media);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }

    }

    /**
     * Return the filename of a file according to its title.
     * TODO: Should return the filename according to its title AND its artist.
     * @param Request $request
     * @return false|string
     */
    function getFilename(Request $request)
    {
        try {
            $title = $request->get('title');
            $filename = DB::table('audios')->where('title', $title)->value('filename');
            return json_encode($filename);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }

    }

    /**
     * Tells the requester if the fetched file is in the pending list.
     * @param Request $request
     * @return false|string
     */
    function isPending(Request $request)
    {
        return json_encode(Queries::isPending($request->get('filename')));
    }

    /**
     * Tells the requester if they are allowed to edit a file.
     * @param Request $request
     * @return false|string
     */
    function isOwner(Request $request)
    {
        $filename = $request->get('filename');
        $table = Functions::retrieveDestinationTable();
        $message = true;
        //Admin have no restrictions.
        if (Auth::user()->isAdmin()) {
            return json_encode(true);
        }
        if (!DB::table($table)->where('filename', '=', $filename)->exists()) {
            $message = "File doesn't exists.";
        }
        if (!DB::table($table)->where('filename', '=', $filename)->where('owner', Auth::user()->username)->exists()) {
            $message = "A file with this name already exists.";
        }
        return json_encode($message);
    }

}

