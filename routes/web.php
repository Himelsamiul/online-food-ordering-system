<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('backend.pages.dashboard');
});

Route::get('/test', function () {
    return view('backend.pages.test');
});
