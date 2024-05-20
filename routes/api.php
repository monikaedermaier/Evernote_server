<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Show all entities
Route::get('notes', [NoteController::class, 'index']);
Route::get('collections', [CollectionController::class, 'index']);
Route::get('todos', [TodoController::class, 'index']);
Route::get('tags', [TagController::class, 'index']);
Route::get('users', [UserController::class, 'index']);

//Show entities by ID
Route::get('notes/{id}', [NoteController::class, 'findById']);
Route::get('collections/{id}', [CollectionController::class, 'findById']);
Route::get('todos/{id}', [TodoController::class, 'findById']);
Route::get('tags/{id}', [TagController::class, 'findById']);
Route::get('users/{id}', [UserController::class, 'findById']);

//check if entities already exists by id
Route::get('notes/checkid/{id}', [NoteController::class, 'checkID']);
Route::get('collections/checkid/{id}', [CollectionController::class, 'checkID']);
Route::get('todos/checkid/{id}', [TodoController::class, 'checkID']);
Route::get('tags/checkid/{id}', [TagController::class, 'checkID']);
Route::get('users/checkid/{id}', [UserController::class, 'checkID']);

//search methods
Route::get('notes/search/{searchTerm}', [NoteController::class, 'findBySearchTerm']);
Route::get('collections/search/{searchTerm}', [CollectionController::class, 'findBySearchTerm']);
Route::get('todos/search/{searchTerm}', [TodoController::class, 'findBySearchTerm']);
Route::get('tags/search/{searchTerm}', [TagController::class, 'findBySearchTerm']);
Route::get('users/search/{searchTerm}', [UserController::class, 'findBySearchTerm']);


//Authentifizierung
Route::post('auth/login',[AuthController::class,'login']);

Route::group(['middleware' =>['api','auth.jwt','auth.jwt']],function(){
    //create entities
    Route::post('notes',[NoteController::class,'save']);
    Route::post('collections',[CollectionController::class,'save']);
    Route::post('todos',[TodoController::class,'save']);
    Route::post('tags',[TagController::class,'save']);
    Route::post('users',[UserController::class,'save']);
    //update entities
    Route::put('notes/{id}',[NoteController::class,'update']);
    Route::put('collections/{id}',[CollectionController::class,'update']);
    Route::put('todos/{id}',[TodoController::class,'update']);
    Route::put('tags/{id}',[TagController::class,'update']);
    Route::put('users/{id}',[UserController::class,'update']);
    //delete entities
    Route::delete('notes/{id}',[NoteController::class,'delete']);
    Route::delete('collections/{id}',[CollectionController::class,'delete']);
    Route::delete('todos/{id}',[TodoController::class,'delete']);
    Route::delete('tags/{id}',[TagController::class,'delete']);
    Route::delete('users/{id}',[UserController::class,'delete']);
    //logout
    Route::post('auth/logout',[AuthController::class,'logout']);
});

