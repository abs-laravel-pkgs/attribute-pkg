<?php

namespace Abs\AttributePkg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldSourceTable extends Model {
	use SoftDeletes;
	protected $table = 'field_source_tables';
	protected $fillable = [
		'name',
		'model',
		'function',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}
