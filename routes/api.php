<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', 'App\Http\Controllers\LoginController@login')->name('user.login');
Route::match(methods: ['post', 'get'], uri: '/logout', action: 'App\Http\Controllers\LoginController@logout')->name('user.logout');

Route::match(methods: ['post', 'get'], uri: '/user', action: 'App\Http\Controllers\LoginController@check')->middleware('auth:sanctum');

Route::post('/browse', 'App\Http\Controllers\BrowseController@redisTest')->middleware('auth:sanctum');

Route::apiResource('resource', App\Http\Controllers\ResourceController::class)->middleware('auth:sanctum');
Route::get('/resource/{resourceFile}/download', 'App\Http\Controllers\ResourceController@download')
    ->name('resource.download')
    ->middleware('auth:sanctum');

Route::apiResource('note', App\Http\Controllers\NoteController::class)->middleware('auth:sanctum');

Route::apiResource('group', App\Http\Controllers\GroupController::class)->middleware(['auth:sanctum']);

Route::apiResource('user', App\Http\Controllers\UserController::class)->middleware(['auth:sanctum', 'allow.admin']);
