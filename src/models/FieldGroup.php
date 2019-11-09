<?php

namespace Abs\AttributePkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldGroup extends Model {
	use SoftDeletes;
	protected $table = 'field_groups';
	protected $fillable = [
		'company_id',
		'category_id',
		'name',
		'combine_fields',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	public function fields() {
		return $this->belongsToMany('Abs\AttributePkg\Field', 'field_group_field', 'field_group_id', 'field_id')->withPivot(['is_required']);
	}

}
