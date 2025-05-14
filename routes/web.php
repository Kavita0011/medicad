<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\GitHubWebhookController;

Route::match(['get', 'post'], '/github-webhook', [GitHubWebhookController::class, 'handle']);

Route::get('/', function () {
    return view('welcome');
});
Route::get('/home', [HomeController::class, 'index']);
Route::get('/about', [AboutController::class, 'index']);