<?php

// Pages
Route::get('/', 'AppController@comingsoon');
Route::get('test', 'AppController@test');
Route::get('app', 'AppController@app');

// App Data
Route::get('pages', 'AppController@pages');

// Data Sites
Route::get('sites', 'DataController@sites');
Route::get('sites/update', 'DataController@sitesUpdate');

// Data Series for specific site
Route::get('sites/{sitecode}', 'DataController@series');
Route::get('sites/{sitecode}/update', 'DataController@seriesUpdate');

// Data
Route::get('sites/{sitecode}/{variablecode}', 'DataController@data');
Route::get('sites/{sitecode}/{variablecode}/update', 'DataController@dataUpdate');