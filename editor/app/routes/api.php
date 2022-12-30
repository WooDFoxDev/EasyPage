<?php

use Easypage\Kernel\Route;

// Put here routes for API requests
//
//
// Available patterns can be seen at routes/web.php

Route::post('/page/new', 'PageController@add', ['authenticated']);
Route::patch('/page/{id}', 'PageController@update', ['authenticated']);
Route::delete('/page/{id}', 'PageController@delete', ['authenticated']);

Route::post('/media/upload', 'ImageController@upload', ['authenticated']);
Route::delete('/media/{id}', 'ImageController@delete', ['authenticated']);

Route::post('/export/{id}', 'ExportController@exportPage', ['authenticated']);
Route::post('/download/{id}', 'ExportController@exportArchive', ['authenticated']);

Route::post('/install', 'InstallController@install', ['install']);
Route::post('/login', 'LoginController@login');
Route::post('/logout', 'LoginController@logout', ['authenticated']);
