<?php

use Illuminate\Support\Facades\Route;

Route::get('/reparaya', function () {
    return view('reparaya', [
        'project' => config('reparaya.name'),
        'group' => config('reparaya.group_code')
    ]);
});
