<?php

use App\Http\Controllers\Api\AccountsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//API V1, PUT ALL ROUTE OF THE FIRST VERSION OF THE API HERE.

Route::prefix('/v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
