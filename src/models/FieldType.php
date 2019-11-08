<?php

namespace Abs\AttributePkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldType extends Model {
	use SoftDeletes;
	protected $table = 'field_types';
	protected $fillable = [
		'name',
		'short_name',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}
