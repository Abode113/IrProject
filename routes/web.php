<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return redirect('/seach_engine');
});



Route::get('/seach_engine', function () {
    return view('search_engine');
});

Route::get('/xpath_search_engine', function () {
    return view('xpath_search_engine');
});

Route::get('admin', function () {
    return view('admin_template');
});


Route::post('/searchEngine/xpathsearch', 'xpathSearchController@search')->name('xpathsearch');
Route::post('/searchEngine/search', 'searchController@search')->name('search');
Route::post('/FileManager/uploade', 'FileManagerController@uploade')->name('uploadeFile');
Route::post('/FileManager/deleteDocument/{id}', 'FileManagerController@deleteDocument')->name('deleteDocument');
Route::get('/FileManager/resetIndex/', 'FileManagerController@resetIndex')->name('resetIndex');
Route::get('/Document/browse', 'viewDocumentController@browse')->name('browseDocument');
Route::get('/similarity/browse', 'similarityController@browse')->name('browsesimilarity');
