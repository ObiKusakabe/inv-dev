<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/fix-avatar', function () {
    DB::table('users')->update(['avatar' => null]);
    return 'Avatar cleared! Refresh halaman profile dan upload ulang.';
});
