<?php
/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

// Default Route
Route::get('/', function () {
    return view('commons.welcome');
});
