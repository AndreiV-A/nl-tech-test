<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', 'AuthController@login');
Route::middleware('auth:api')->post('/logout', 'AuthController@logout');

Route::post('/users', 'API\UsersController@store');
Route::middleware('auth:api')->get('/users', 'API\UsersController@index');
Route::middleware('auth:api')->get('/users/{user}', 'API\UsersController@show');


