<?php

namespace Abs\AttributePkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Field extends Model {
	use SoftDeletes;
	protected $table = 'fields';
	protected $fillable = [
		'category_id',
		'name',
		'type_id',
		'list_source_id',
		'source_table_id',
		'config_type_id',
		'min_length',
		'max_length',
		'min_date',
		'max_date',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}
