<?php

use App\Models\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// NOTES
Route::get('/', [\App\Http\Controllers\NoteController::class, "index"]);

// COLLECTIONS
Route::get('/', [\App\Http\Controllers\CollectionController::class, "index"]);

// TODOS
Route::get('/', [\App\Http\Controllers\TodoController::class, "index"]);

// TAGS
Route::get('/', [\App\Http\Controllers\TagController::class, "index"]);

// USERS
Route::get('/', [\App\Http\Controllers\UserController::class, "index"]);

