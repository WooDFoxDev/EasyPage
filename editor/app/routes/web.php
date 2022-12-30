<?php

use Easypage\Kernel\Route;

// Put here web routes
// Arguments
// 1 - Path or pattern
// 2 - Callback for that pattern
// 3 - Guards list
//
// Available patterns
// Route::get('/pages', 'PageController'); // @index method will be called. Use it to request all the items
// Route::get('/pages', 'PageController@index'); // The same as above
// Route::get('/page/{id}', 'PageController@show'); // Request singular item
// Route::post('/page/add', 'PageController@add'); // Add new item
// Route::patch('/page/{id}', 'PageController@update'); // Update existing items
// Route::delete('/page/{id}', 'PageController@delete'); // Remove item
// Route::crud('/page/{id}', 'PageController'); // Paste all four CRUD routes for item class
// Route::get('/', function () { /** some code... */ }); // Closure callback
// Route::get('/', fn () => /** some code... */ ); // Closure callback (arrow function)
// Route::get('/{parameter}', function ($parameter) { /** some code... */ }); // Variables are also supported for closures
// Route::get('/{parameter}', function (Storage $storage, $parameter) { /** some code... */ }); // DI is also supported for closures
// Route::get('/rages', [PageController::class]); // Class name is also supported through array
// Route::get('/rages', [PageController::class, 'index']); // Also with method passing
// 
// Method 'index' will be called if nothing given


Route::get('/', 'PageController', ['authenticated']);
Route::get('/media', 'ImageController', ['authenticated']);

Route::get('/page/new', 'PageController@new', ['authenticated']);
Route::get('/page/{id}', 'PageController@show', ['authenticated']);

Route::get('/login', 'LoginController');
Route::get('/install', 'InstallController', ['install']);
