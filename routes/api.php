<?php

Route::group(['prefix' => 'api', 'namespace' => 'Jmrashed\LaravelCoreService\Controllers\Api',], function(){
	Route::get('service/check', 'CheckController@index');
});
