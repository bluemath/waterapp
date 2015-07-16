<?php

// Pages
Route::get('/', 'PagesController@splash');
Route::get('/gsl', 'PagesController@greatsaltlake');
Route::get('/gamut', 'PagesController@gamut');
Route::get('/rbc', 'PagesController@redbuttecreek');
Route::get('/bio', 'PagesController@biodiversity');

// Data Sites
Route::get('data/sites', 'DataController@sites');
Route::get('data/sites/update', 'DataController@sitesUpdate');

// Data Series for specific site
Route::get('data/sites/{sitecode}', 'DataController@series');
Route::get('data/sites/{sitecode}/update', 'DataController@seriesUpdate');

// Data
Route::get('data/sites/{sitecode}/{variablecode}/update', 'DataController@dataUpdate');
Route::get('data/sites/{sitecode}/{variablecode}/{start?}/{end?}', 'DataController@data');

// Map tiles
// Route::get('map/{url?}', 'MapController@relay')->where('url', '(.*)');;