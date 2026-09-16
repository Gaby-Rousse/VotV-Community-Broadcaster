<?php

use App\Http\Controllers\Api\ChannelsController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API V1, PUT ALL ROUTE OF THE FIRST VERSION OF THE API HERE.

Route::prefix('/v1')->group(function () {
    Route::prefix('notifications')->middleware('auth:sanctum')->controller(NotificationsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{notification}/read', 'read');
    });
    Route::prefix('support/threads')->controller(SupportController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{thread}', 'show');
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/requests/pending', 'requests');
            Route::post('/', 'store')->middleware('throttle:10,1');
            Route::post('/{thread}/messages', 'reply')->middleware('throttle:30,1');
            Route::patch('/{thread}', 'update')->middleware('throttle:30,1');
            Route::patch('/{thread}/submission-review', 'reviewSubmission')->middleware('throttle:20,1');
            Route::post('/{thread}/read', 'read');
            Route::post('/{thread}/access-requests', 'requestAccess')->middleware('throttle:5,1');
            Route::patch('/{thread}/access-requests/{accessRequest}', 'reviewAccess')->middleware('throttle:20,1');
        });
    });
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::controller(ChannelsController::class)->group(function () {
        Route::get('/channels', 'index');
    });

    Route::controller(UserController::class)->group(function () {
        Route::delete('/user/{id}', 'delete')->middleware('auth:sanctum');
    });
});
