<?php

Route::get('/', 'PagesController@splash');
Route::get('/gsl', 'PagesController@greatsaltlake');
Route::get('/gamut', 'PagesController@gamut');
Route::get('/rb', 'PagesController@redbuttecreek');
Route::get('/bio', 'PagesController@biodiversity');

// Data Sites
Route::get('data', 'DataController@sites');
Route::get('data/update', 'DataController@sitesUpdate');

// Data Series for specific site
Route::get('data/{sitecode}', 'DataController@series');

// Data
Route::get('data/{sitecode}/{variablecode}', 'DataController@data'); // All
Route::get('data/{sitecode}/{variablecode}/update', 'DataController@dataUpdate'); // Update
Route::get('data/{sitecode}/{variablecode}/{start}{end}', 'DataController@dataRange'); // Range
Route::get('data/{sitecode}/{variablecode}/{start}/{end}', 'DataController@dataRange'); // Range
