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

	public function getMinDateAttribute($value) {
		return empty($value) ? '' : date('d-m-Y', strtotime($value));
	}
	public function getMaxDateAttribute($value) {
		return empty($value) ? '' : date('d-m-Y', strtotime($value));
	}

	public function setMinDateAttribute($date) {
		return $this->attributes['min_date'] = empty($date) ? date('Y-m-d') : date('Y-m-d', strtotime($date));
	}
	public function setMaxDateAttribute($date) {
		return $this->attributes['max_date'] = empty($date) ? date('Y-m-d') : date('Y-m-d', strtotime($date));
	}

}
