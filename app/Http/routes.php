<?php

// Pages
Route::get('/', 'AppController@app'); // used to go to @comingsoon as a temp page, set to @app for normal use
Route::get('test', 'AppController@test');
Route::get('app', 'AppController@app');

// App Data
Route::get('pages', 'AppController@pages');

Route::get('cameras', 'CamerasController@siteslinked');
Route::get('cameras/update', 'CamerasController@update');
Route::get('cameras/{sitecode}', 'CamerasController@timestamps');

// Data Sites
Route::get('sites', 'DataController@sites');
Route::get('sites/update', 'DataController@sitesUpdate');

// Data Series for specific site
Route::get('sites/{sitecode}', 'DataController@series');
Route::get('sites/{sitecode}/update', 'DataController@seriesUpdate');

// Data
Route::get('sites/{sitecode}/{variablecode}', 'DataController@data');
Route::get('sites/{sitecode}/{variablecode}/update', 'DataController@dataUpdate');