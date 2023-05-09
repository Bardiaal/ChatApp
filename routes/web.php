<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\ChatController::class, 'index'])->name('home');
Route::get('/set-last-connection', [App\Http\Controllers\ChatController::class, 'setLastConnection']);

Route::get('/test-private-chat', [App\Http\Controllers\ChatController::class, 'testPrivateChat']);
Route::get('/test-group-chat', [App\Http\Controllers\ChatController::class, 'testGroupChat']);
Route::get('/test-private-message', [App\Http\Controllers\ChatController::class, 'testPrivateMessage']);
Route::get('/test-group-message', [App\Http\Controllers\ChatController::class, 'testGroupMessage']);
Route::get('/test-join-group', [App\Http\Controllers\ChatController::class, 'userJoinRandomGroup']);