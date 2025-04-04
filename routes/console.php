<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {

    $basePath = public_path('Dump20250403');  // Get the full path to the folder inside the public directory

    // Check if the folder exists
    if (!File::exists($basePath)) {
        $this->error("The folder '$basePath' does not exist.");
        return;
    }

    // Get all .sql files recursively from the folder
    $files = File::allFiles($basePath);

    // Filter out only .sql files
    $sqlFiles = collect($files)->filter(function ($file) {
        return $file->getExtension() === 'sql';
    });

    // Import each .sql file
    foreach ($sqlFiles as $file) {
        $sql = file_get_contents($file->getRealPath());
        DB::unprepared($sql);  // Execute the SQL query
        $this->info("SQL file '{$file->getFilename()}' imported successfully!");
    }

    // If no SQL files were found
    if ($sqlFiles->isEmpty()) {
        $this->info("No SQL files found in the folder 'Dump20250403'.");
    }

    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
