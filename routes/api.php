<?php

use App\Http\Controllers\CrawlerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/start-crawl', [CrawlerController::class, 'start']);
/**
 * This route is not for production
 */
Route::get('/start-crawl/{source_id}/{start_index}', [CrawlerController::class, 'start2']); // Just for test
Route::get('/crawl-status/{id}', [CrawlerController::class, 'status']);

