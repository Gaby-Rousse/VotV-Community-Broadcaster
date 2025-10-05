<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Providers\Queries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    function reports()
    {
        if (session('connectedUser')->isAdmin())
            return view('reports', ['title' => 'Reports', 'reports' => DB::table('reports')->get()]);
        return redirect('/');
    }

    /**
     * Send a new report. If the reason is empty it deletes it from the database.
     * @param Request $request
     * @return void
     */
    function reportMedia(Request $request)
    {
        if (session('connectedUser')) {
            $validated = $request->validate([
                //Si quelqu'un gosse avec le hidden
                'filename' => 'required',
                'reason' => '',
            ]);
            if (trim($validated['reason'])) {
                DB::table('reports')->upsert(['filename' => $validated['filename'], 'from' => session('connectedUser')->username, 'reason' => trim($validated['reason'])], ['from', 'filename']);
                $admins = DB::table('users')->where('isAdmin', '=', 1)->get();
                foreach ($admins as $admin) {
                    Queries::insertNotification(new Notification(session('connectedUser')->username, $admin->username, 'added a report for: ' . $validated['filename'] . ' open the reports tab for more info.'));
                }
            } else {
                DB::table('reports')->where('from', session('connectedUser')->username)->where('filename', $validated['filename'])->delete();
            }

        }


    }

    /**
     * Delete a Report according to an id
     * Why not an API Gaby? just sayin...
     * @param int $id
     * @return RedirectResponse
     */
    function deleteReport(int $id)
    {
        if (session('connectedUser')->isAdmin()) {
            DB::table('reports')->where('id', $id)->delete();
            return redirect()->back();
        }
        return redirect()->back();
    }
}
