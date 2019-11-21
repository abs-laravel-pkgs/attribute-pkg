<?php

namespace Abs\AttributePkg;
use App\Company;
use App\Config;
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

	public static function createFromCollection($records, $company = null) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::createFromObject($record_data, $company);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}
	public static function createFromObject($record_data, $company = null) {

		$errors = [];
		if (!$company) {
			$company = Company::where('code', $record_data->company)->first();
		}
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$category = Config::where('name', $record_data->category)->where('config_type_id', 83)->first();
		if (!$category) {
			$errors[] = 'Invalid category : ' . $record_data->category;
		}

		$field_type = FieldType::where('name', $record_data->field_type)->first();
		if (!$field_type) {
			$errors[] = 'Invalid field type : ' . $record_data->field_type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->field_name,
			'category_id' => $category->id,
		]);
		$record->type_id = $field_type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public function fieldGroups() {
		return $this->belongsToMany('Abs\AttributePkg\FieldGroup', 'field_group_field');
	}

	public function fieldType() {
		return $this->belongsTo('Abs\AttributePkg\FieldType', 'type_id', 'id');
	}

}
