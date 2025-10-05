<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\Message;
use App\Models\Music;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Kiwilan\Audio\Audio;
use PHPUnit\Exception;

class Queries
{
    //Medias
    /**
     * Insert a new media inside the concerned table
     * @param Media $media - The media to insert
     * @param string $table - The table to insert into
     * @return void
     */
    static function insertMedia(Media $media, string $table = 'audios')
    {
        $media = (array)$media;
        DB::table($table)->insert($media);
        Functions::insertDurations($table);
    }


    /**
     * Receive a media
     * @param string $filename - The filename of the media you want to receive
     * @return Media
     */
    static function receiveMedia(string $filename): Media
    {
        $value = DB::table(Functions::retrieveDestinationTable())->where('filename', $filename)->get();
        $media = new Media($value[0]->filename, $value[0]->title, $value[0]->artist, $value[0]->cover, $value[0]->album, $value[0]->genre, $value[0]->year, $value[0]->description, $value[0]->type, $value[0]->owner, $value[0]->destination);
        $media->approved = $value[0]->approved;
        $media->seconds = $value[0]->duration;
        $media->isFavorite = self::isFavorite($value[0]->filename);
        $media->favoriteCount = DB::table('favorites')->where('filename', $value[0]->filename)->count();
        $media->isReported = self::isReported($value[0]->filename);
        if (session('connectedUser')) {
            $media->reportReason = DB::table('reports')->where('filename', $value[0]->filename)->where('from', session('connectedUser')->username)->value('reason') ?? '';
        }
        $media->explicit = $value[0]->explicit;
        return $media;
    }

    /**
     * Tells if the file is in the favorite list of the connected user
     * @param string $filename - The filename to check
     * @return bool
     */
    static function isFavorite(string $filename): bool
    {
        if (session('connectedUser')) {
            return DB::table('favorites')->where('filename', $filename)->where('username', session('connectedUser')->username)->exists();
        }
        return false;
    }

    /**
     * Tells if the file has been report by the connected user
     * @param string $filename - The filename to check
     * @return bool
     */
    static function isReported(string $filename): bool
    {
        if (session('connectedUser')) {
            return DB::table('reports')->where('filename', $filename)->where('from', session('connectedUser')->username)->exists();
        }
        return false;
    }

    /**
     * Tells if the connected user is the owner of the file
     * @param string $filename - The filename to check
     * @param $table - The table that contains the filename
     * @return bool
     */
    static function isOwner(string $filename, string $table = 'audios'): bool
    {
        return session('connectedUser')->username == DB::table($table)->where('filename', $filename)->value('owner');
    }

    /**
     * Tells if the file is in the pending list
     * @param string $filename - The filename to check
     * @return bool
     */
    static function isPending(string $filename): bool
    {
        return DB::table(Functions::retrieveDestinationTable())->where('filename', $filename)->value('approved') == 0;
    }

    /**
     * Set a file as approved
     * @param $filename - The filename to approve
     * @param $table
     * @return void
     */
    static function approveMedia($filename, $table = 'audios')
    {
        DB::table($table)->where('filename', $filename)->update(['approved' => 1]);
    }

    //Users

    /**
     * Insert a user inside the database
     * @param User $user - The user to insert
     * @return void
     */
    static function insertUser(User $user): void
    {
        DB::table('users')->insert(['username' => $user->username, 'password' => $user->password, 'isAdmin' => $user->admin]);
    }

    //Notifications
    /**
     * Insert a notification
     * @param Notification $notification - The notification to insert
     * @return void
     */
    static function insertNotification(Notification $notification): void
    {
        DB::table('notifications')->insert(['from' => $notification->from, 'to' => $notification->to, 'content' => $notification->content]);
    }

    /**
     * Insert a message
     * @param Message $message - The message to insert
     * @param string $table - The table (either suggestions or bug)
     * @return void
     */
    //Messages
    static function insertMessage(Message $message, string $table): void
    {
        DB::table($table)->insert(['from' => $message->from, 'content' => $message->content]);
    }

    /**
     * Tells if the connected user is the owner of a message
     * @param int $id
     * @param string $table
     * @return bool
     */
    static function isOwnerOfMessage(int $id, string $table) : bool
    {
        return DB::table($table)->where('id', $id)->value('from') == session('connectedUser')->username;
    }


}
