<?php

namespace Abs\AttributePkg\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldConfigSource extends Model {
	use SoftDeletes;
	protected $table = 'field_config_sources';
	protected $fillable = [
		'name',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}
