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

	public static function createFromObject($record_data) {

		$errors = [];

		$record = self::firstOrNew([
			'short_name' => $record_data->short_name,
		]);
		$record->name = $record_data->field_type_name;
		$record->save();
		return $record;
	}

	public static function createFromCollection($records) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->short_name) {
					continue;
				}
				$record = self::createFromObject($record_data);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

	public static function createMultipleFromArray($records) {
		foreach ($records as $data) {
			$record = self::firstOrCreate([
				'name' => $data['name'],
				'short_name' => $data['short_name'],
			]);
		}
	}

}
