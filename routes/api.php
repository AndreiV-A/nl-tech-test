<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// authentification
Route::post('/login', 'AuthController@login');
Route::middleware('auth:api')->post('/logout', 'AuthController@logout');

// users
Route::post('/users', 'API\UsersController@store');
Route::middleware('auth:api')->get('/users', 'API\UsersController@index');
Route::middleware('auth:api')->get('/users/{user}', 'API\UsersController@show');

// box files
// Route::get('/boxfiles_test1', 'API\BoxFilesController@test1');
Route::middleware('auth:api')->get('/boxfiles', 'API\BoxFilesController@index');
Route::middleware('auth:api')->post('/boxfiles', 'API\BoxFilesController@store');
Route::middleware('auth:api')->delete('/boxfiles/{boxfile}', 'API\BoxFilesController@deleteFile');
// Route::get('/boxfiles/DELETE/{boxfile}', 'API\BoxFilesController@deleteFile');