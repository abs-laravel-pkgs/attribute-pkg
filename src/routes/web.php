<?php

Route::group(['namespace' => 'Abs\AttributePkg', 'middleware' => ['web', 'auth'], 'prefix' => 'attribute-pkg'], function () {
	Route::get('/field-groups/get-filter-data/{category_id?}', 'FieldGroupController@getFieldGroupFilterdata')->name('getFieldGroupFilterdata');
	Route::get('/field-groups/get-list', 'FieldGroupController@getFieldGroupList')->name('getFieldGroupList');
	Route::get('/field-group/delete/{id?}', 'FieldGroupController@delete')->name('deleteFieldGroup');
	Route::get('/field-group/get-form-data/', 'FieldGroupController@getFieldGroupFormdata')->name('getFieldGroupFormdata');
	Route::post('/field-group/save', 'FieldGroupController@saveFieldGroup')->name('saveFieldGroup');

	Route::get('/field-types/get-list', 'FieldTypeController@getFieldTypeList')->name('getFieldTypeList');
	Route::post('/field-type/save', 'FieldTypeController@saveFieldType')->name('saveFieldType');

	//FIELDS
	Route::get('/field/get-filter-data/{category_id?}', 'FieldController@getFieldFilterdata')->name('getFieldFilterdata');
	Route::get('/fields/get-list', 'FieldController@getFieldList')->name('getFieldList');
	Route::get('/field/delete/{id?}', 'FieldController@delete')->name('deleteField');
	Route::get('/field/get-form-data/', 'FieldController@getFieldFormdata')->name('getFieldFormData');
	Route::post('/field/save', 'FieldController@saveField')->name('saveField');

	Route::get('/fields/get', 'FieldController@getFields')->name('getFields');
});