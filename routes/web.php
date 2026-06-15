<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Deployment helper routes (useful for running migrations and storage links on Render)
Route::get('/deploy/{action}', function ($action) {
    if (request('key') !== 'deploy123') {
        return response('Unauthorized: Invalid key parameter. Use ?key=deploy123', 403);
    }

    try {
        switch ($action) {
            case 'migrate':
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                break;
            case 'storage-link':
                \Illuminate\Support\Facades\Artisan::call('storage:link');
                break;
            case 'cache':
                \Illuminate\Support\Facades\Artisan::call('config:cache');
                \Illuminate\Support\Facades\Artisan::call('route:cache');
                \Illuminate\Support\Facades\Artisan::call('view:cache');
                break;
            case 'clear':
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                break;
            default:
                return "Unknown action. Available actions: migrate, storage-link, cache, clear";
        }
        return "<h3>Action '$action' executed successfully:</h3><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "<h3>Error executing '$action':</h3><pre>" . $e->getMessage() . "</pre>";
    }
});
