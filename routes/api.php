<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WordController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('exportWord/{path}/{type}/{name}', [WordController::class, 'exportWord'])->name('exportWord');
/* Route::get('compareFile/{file1}/{file2}', [WordController::class, 'compareFile'])->name('compareFile');
Route::post('upload-file', [WordController::class, 'uploadFile'])->name('upload-file'); */
Route::post('receiveData', [WordController::class, 'receiveData'])->name('receiveData');
