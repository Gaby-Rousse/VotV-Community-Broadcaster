<?php

use App\Http\Controllers\Auth\Account;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Models\Message;
use App\Providers\Cobalt;
use App\Providers\Functions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

//Views
Route::view('/wip', 'nothing', ['title' => 'WIP']);
//If a route fails, return the wip page.
Route::fallback(function () {
    return redirect('/wip');
});
Route::view('/changelog', 'changelog', ['title' => 'Changelog']);
Route::view('/credits', 'credits', ['title' => 'Credits']);
Route::view('/debug', 'debug', ['title' => 'Debug']);
//Account related views
Route::view('/signin', 'user.signin', ['title' => 'Sign in']);
Route::view('/signup', 'user.signup', ['title' => 'Sign up']);
Route::view('/account', 'user.account', ['title' => 'Account']);


//Media Related Actions
Route::controller(MediaController::class)->group(function () {
    Route::post('/reportMedia', 'reportMedia');
    Route::get('/toggleFavorite', 'toggleFavorite');

    //Is that still used?
    Route::get('/getSession', 'getSession');

    //CREATE
    Route::post('/uploadMedia', 'uploadMedia');
    //CREATE from url
    Route::post('/import', 'importMedia');
    //UPDATE
    Route::post('/updateMetadata', 'updateMetadata');
    //DELETE
    Route::post('/deleteMedia', 'deleteMedia');
    Route::get('/approveMedia', 'approveMedia');


    //Fill the autoRefreshedPanels
    Route::get('/getMedias', 'getMedias');
    Route::get('/getPendingMedias', 'getPendingMedias');


    //FILTERS (that affects the 'getMedias' autoRefreshedPanel)
    Route::get('/setSearchKeywords', 'setSearchKeywords');
    Route::get('/setChannel', 'setChannel');

    Route::get('/showFavorite', 'showFavorite');
    Route::get('/setSorting', 'setSorting');
    Route::get('/setType', 'setType');
    Route::get('/onlyMe', 'setOwner');

    //GET INFOS
    Route::get('/getMedia', 'getMedia');
    Route::get('/getFilename', 'getFilename');
    Route::get('/isPending', 'isPending');
    Route::get('/isOwner', 'isOwner');

});

//Reports
Route::controller(ReportController::class)->group(function () {
    Route::get('/reports', 'reports');
    Route::post('/reportMedia', 'reportMedia');
    Route::get('/deleteReport/{id}', 'deleteReport');
});

//Suggestions and bugs forms
Route::controller(MessageController::class)->group(function () {
    //bugs and suggestions use the same view, but some values are generated depending on the url
    Route::get('/bugs', 'bugs');
    Route::get('/suggestions', 'suggestions');

    //CREATE
    Route::post('/sendMessage', 'sendMessage');
    //UPDATE or DELETE
    Route::post('/updateMessage', 'updateMessage');
});

//AUTH
Route::post('/register', Register::class);
Route::post('/login', Login::class);
Route::post('/account', Account::class);

Route::controller(NotificationController::class)->group(function () {
    //UPDATE / DELETE
    Route::get('/notificationAction', [NotificationController::class, 'readOrDeleteNotification']);
    //Values for the notifications autoRefreshedPanel
    Route::get('/getNotifications', [NotificationController::class, 'getNotifications']);
});


//CUSTOM, no controller.

Route::get('/', function () {
    return view('index', ['title' => 'VOTV Community Broadcaster']);
});

Route::get('/upload', function () {
    return view('upload', ['title' => 'Upload', 'count' => 0, 'reportCount' => DB::table('reports')->count(), 'approvedMaxPages' => ceil(DB::table(Functions::retrieveDestinationTable())->where('approved', 1)->count() / 10), 'pendingMaxPages' => ceil(DB::table(Functions::retrieveDestinationTable())->where('approved', 0)->count() / 10)]);
});


Route::get('/uploadAudio', function (Request $request) {
    session(['media_type' => 'audios']);
    $keywords = $request->get('keywords');
    session(['keywords' => $keywords]);
    return redirect('upload');
});


Route::get('/uploadVideo', function (Request $request) {
    session(['media_type' => 'videos']);
    $keywords = $request->get('keywords');
    session(['keywords' => $keywords]);
    return redirect('upload');
});


//Test
Route::get('/generatePlaylist', [MediaController::class, 'generatePlaylist']);
Route::get('/insertDurations', [MediaController::class, 'webInsertDurations']);

//Route::get('/updateTable',[MediasController::class,'updateTable']);

Route::get('/cookie', function () {
    return json_encode(Cookie::get('username'));
});
Route::view('/test', 'test', ['title' => 'Test']);
Route::view('/status', 'status', ['title' => 'Status']);
Route::get('/php', function () {
    return phpinfo();
});

Route::get('/generateDurationsPlaylist', function () {
    Functions::generateDurationsPlaylist();
});

Route::get('/cobalt', function () {
    dd(Cobalt::download('https://www.youtube.com/watch?v=4yUU5v-1v0w', 'media'));
});

Route::get('/stats', function () {
    $truc = DB::table('audios')->select('destination', DB::raw('count(*) as count'))->groupBy('destination')->get();
    foreach ($truc as $item) {
        echo "$item->destination" . ":" . $item->count . "<br>";
    }
});

Route::post('/generateOnlineTXT', [MediaController::class, 'generateOnlineTXT']);
Route::post('/downloadAllSelectedFiles', [MediaController::class, 'downloadAllSelectedFiles']);
Route::post('/updateBatch', [MediaController::class, 'batchEditSelectedFiles']);
Route::post('/deleteBatch', [MediaController::class, 'batchDeleteSelectedFiles']);

Route::view('/help', 'help', ['title' => 'Help']);
Route::view('/history', 'history', ['title' => 'History']);