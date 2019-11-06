<?php
Route::group(['namespace' => 'Abs\AttributePkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'attribute-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			Route::get('fields/get', 'FieldController@getFields');
		});
	});
});