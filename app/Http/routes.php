<?php

// Pages
Route::get('/', 'AppController@comingsoon');
Route::get('app', 'AppController@app');

// App Data
Route::get('app/pages', 'AppController@pages');

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