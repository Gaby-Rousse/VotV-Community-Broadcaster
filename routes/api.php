<?php

use App\Http\Controllers\Api\AccountsController;
use App\Http\Controllers\Api\ChannelsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//API V1, PUT ALL ROUTE OF THE FIRST VERSION OF THE API HERE.

Route::prefix('/v1')->group(function () {
    Route::get('/channels', [ChannelsController::class, 'index']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
