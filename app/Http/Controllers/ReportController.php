<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Providers\Queries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    function reports()
    {
        if (Auth::user()->isAdmin())
            return view('reports', ['title' => 'Reports', 'reports' => DB::table('reports')->get()]);
        return redirect('/');
    }

    /**
     * Send a new report. If the reason is empty it deletes it from the database.
     * @param Request $request
     *
     */
    function reportMedia(Request $request)
    {
        if (Auth::check()) {
            $validated = $request->validate([
                //Si quelqu'un gosse avec le hidden
                'filename' => 'required',
                'reason' => '',
            ]);
            if (trim($validated['reason'])) {
                DB::table('reports')->upsert(['filename' => $validated['filename'], 'from' => Auth::id(), 'reason' => trim($validated['reason'])], ['from', 'filename']);
                $admins = DB::table('users')->where('isAdmin', '=', 1)->get();
                foreach ($admins as $admin) {
                    Queries::insertNotification(new Notification(Auth::id(), $admin->id, 'added a report for: ' . $validated['filename'] . ' open the reports tab for more info.'));
                }
                return json_encode(['type' => 'Info', 'message' => 'File reported successfully!']);
            } else {
                DB::table('reports')->where('from', Auth::id())->where('filename', $validated['filename'])->delete();
                return json_encode(['type' => 'Info', 'message' => 'Report deleted successfully!']);
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
        if (Auth::user()->isAdmin()) {
            DB::table('reports')->where('id', $id)->delete();
            return redirect()->back();
        }
        return redirect()->back();
    }
}
