<?php

namespace Abs\AttributePkg;
use App\Company;
use App\Config;
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

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->field_group_name,
		]);
		$record->category_id = $category->id;
		$record->combine_fields = 0;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public static function mapFields($records, $company = null) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::mapField($record_data, $company);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}
	public static function mapField($record_data, $company = null) {
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

		$record = FieldGroup::where('name', $record_data->field_group_name)->where('company_id', $company->id)->first();
		if (!$record) {
			$errors[] = 'Invalid Field Group : ' . $record_data->field_group_name;
		}

		$field = Field::where('name', $record_data->field)->where('company_id', $company->id)->first();
		if (!$field) {
			$errors[] = 'Invalid Field : ' . $record_data->field;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}
		$record->fields()->syncWithoutDetaching([
			$field->id => [
				'is_required' => $record_data->is_required,
			],
		]);

		return $record;
	}
}
