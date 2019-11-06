<?php

Route::group(['namespace' => 'Abs\AttributePkg', 'middleware' => ['web', 'auth'], 'prefix' => 'attribute-pkg'], function () {
	Route::get('/field-groups/get-list', 'FieldGroupController@getFieldGroupList')->name('getFieldGroupList');
	Route::post('/field-group/save', 'FieldGroupController@saveFieldGroup')->name('saveFieldGroup');

	Route::get('/field-types/get-list', 'FieldTypeController@getFieldTypeList')->name('getFieldTypeList');
	Route::post('/field-type/save', 'FieldTypeController@saveFieldType')->name('saveFieldType');

	Route::get('/fields/get-list', 'FieldController@getFieldList')->name('getFieldList');
	Route::post('/field/save', 'FieldController@saveField')->name('saveField');
});