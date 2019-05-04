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
//Route::post('/searchEngine/test', 'searchController@test')->name('test');
Route::post('/FileManager/uploade', 'FileManagerController@uploade')->name('uploadeFile');
Route::post('/FileManager/BackUp', 'FileManagerController@BackUpCorpus')->name('BackUpCorpus');
Route::post('/FileManager/deleteDocument/{id}', 'FileManagerController@deleteDocument')->name('deleteDocument');
Route::post('/Documents/browse/{term_id}', 'viewDocumentController@BrowseDocumentByTremID')->name('TermDocs');
Route::post('/Term/browse/{doc_id}', 'TermConroller@BrowseTermsByDocumentID')->name('DocTerms');
Route::post('/Corpus/ApplyingBackUp/{corpus_is}', 'FileManagerController@ApplyBackUp')->name('ApplyBackUp');
Route::post('/Corpus/deleteBackUp/{corpus_is}', 'FileManagerController@deleteCorpusById')->name('deleteBackUp');
Route::get('/FileManager/resetIndex/', 'FileManagerController@resetIndex')->name('resetIndex');
Route::get('/Document/browse', 'viewDocumentController@browse')->name('browseDocument');
Route::get('/Corpus/browse', 'FileManagerController@getCorpus')->name('getCorpus');
Route::get('/Term/browse/', 'TermConroller@browse')->name('browseTerm');
//Route::get('/similarity/browse', 'similarityController@browse')->name('browsesimilarity');
Route::post('/similarity/findsimilartiy/{document_id}', 'similarityController@findSimilartiy')->name('findsimilartiy');
Route::post('/similarity/findsimilartiyalldoc', 'similarityController@findsimilartiyAllDoc')->name('findsimilartiyalldoc');
Route::get('/similarity/browse', 'similarityController@browse')->name('browseDocument');
Route::get('/similarity/browseexist', 'similarityController@browseexist')->name('browseexist');
Route::post('/similarity/BackUp', 'similarityController@BackUpSimilarity')->name('BackUpSimilarity');
Route::post('/similarity/delete', 'similarityController@deleteSimilarity')->name('deleteSimilarity');
Route::get('/similarity/BrowseBackUp', 'similarityController@BrowseBackUp')->name('BrowseBackUp');
Route::post('/similarity/deleteBackUp/{backup_id}', 'similarityController@deleteBackUp')->name('deleteSimilarityBackUp');
Route::post('/similarity/deleteAllSimilarityNow/', 'similarityController@deleteAllSimilarityNow')->name('deleteAllSimilarityNow');
Route::post('/similarity/ApplyBackUp/{backup_id}', 'similarityController@ApplyBackUp')->name('ApplySimilarityBackUp');



