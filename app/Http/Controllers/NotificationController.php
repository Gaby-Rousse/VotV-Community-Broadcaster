<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Providers\Functions;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Returns a view with all the notifications destined to the user.
     * @param Request $request
     * @return Factory|View|Application|\Illuminate\View\View|object|null
     */
    function getNotifications(Request $request)
    {
        if($request->get('forceRefresh') || Functions::isUpdated('notifications'))
        {
            session()->flash('play_mailSFX', Functions::isUpdated('notifications') && !$request->get('forceRefresh'));
            Functions::insertLatestUpdate('notifications');
            if(session('connectedUser')) {
                $notifications = [];
                //Unread first, then by time.
                $values = DB::table('notifications')->where('to', session('connectedUser')->username)->orderBy('seen', 'asc')->orderBy('created_at', 'desc')->get();
                foreach ($values as $value) {
                    $notif = new Notification($value->from, $value->to, $value->content);
                    $notif->created_at = $value->created_at;
                    $notif->seen = $value->seen;
                    $notif->id = $value->id;
                    $notifications[] = $notif;
                }
                return view('panels/getNotifications', ['notifications' => $notifications, 'count' => session('connectedUser')->notificationCount()]);
            }
        }

        return null;
    }

    /**
     * If the ?id is for an unread notification, will mark it as read.
     * If the ?id is for a read notification, will delete it.
     * If ?id=readAll will mark as read every notifications
     * If ?id=deleteAll will delete every notification
     * @param Request $request
     * @return void
     */
    function readOrDeleteNotification(Request $request)
    {
        $id = $request->get('id');
        if($id == 'readAll')
        {
            DB::table('notifications')->where('to',session('connectedUser')->username)->update(['seen' => 1]);
        }
        if($id == 'deleteAll')
        {
            DB::table('notifications')->where('to',session('connectedUser')->username)->delete();
        }
        //La notification t'adresse t'elle?
        if(DB::table('notifications')->where('id', $id)->value('to') == session('connectedUser')->username)
        {
            if(DB::table('notifications')->where('id',$id)->value('seen') == 0)
            {
                DB::table('notifications')->where('id',$id)->update(['seen' => 1]);
            }
            else
            {
                DB::table('notifications')->where('id',$id)->delete();
            }
        }
    }


}
