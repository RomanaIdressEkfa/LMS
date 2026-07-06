<?php

use Illuminate\Support\Facades\Route;

/*
 * This is the API backend. It has no visual website of its own — the actual
 * LMS site is the Next.js frontend. If someone opens this server in a browser
 * by mistake, send them to the real website.
 */
Route::get('/', function () {
    return redirect()->away(env('FRONTEND_URL', 'http://localhost:3000'));
});
