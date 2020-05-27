<?php

use Illuminate\Support\Facades\Route;


Route::get('/{any}', 'SPAController@index')->where('any', '.*');
