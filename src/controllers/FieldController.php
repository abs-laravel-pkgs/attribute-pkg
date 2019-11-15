<?php

namespace Abs\AttributePkg;
use Abs\AttributePkg\Field;
use Abs\AttributePkg\FieldType;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class FieldController extends Controller {

	public function __construct() {
	}

	public function getFieldFilterdata($category_id = NULL) {
		$field_category = Config::where('id', $category_id)->first();
		if (!$field_category) {
			return response()->json(['success' => false, 'errors' => ['Field category not found']]);
		}

		return response()->json(['success' => true, 'field_category' => $field_category]);
	}

	public function getFieldList() {
		$fields_list = Field::withTrashed()
			->select(
				'fields.id',
				'fields.name',
				'fields.category_id',
				'fields.deleted_at',
				'field_types.short_name'
			)
			->join('field_types', 'field_types.id', 'fields.type_id')
			->where('fields.company_id', Auth::user()->company_id)
			->groupBy('fields.id')
			->orderBy('fields.id', 'Desc');

		return Datatables::of($fields_list)
			->addColumn('field_name', function ($field_list) {
				if ($field_list->deleted_at == NULL) {
					$name = "<td><span class='status-indicator green'></span>" . $field_list->name . "</td>";
				} else {
					$name = "<td><span class='status-indicator red'></span>" . $field_list->name . "</td>";
				}
				return $name;
			})
			->addColumn('action', function ($field_list) {

				$img_edit = asset('public/theme/img/table/cndn/edit.svg');
				$img_delete = asset('public/theme/img/table/cndn/delete.svg');

				return '<a href="#!/attribute-pkg/field/edit/' . $field_list->category_id . '/' . $field_list->id . '" class="">
                        <img class="img-responsive" src="' . $img_edit . '" alt="Edit" />
                    	</a>
						<a href="javascript:;"  data-toggle="modal" data-target="#field-delete-modal" onclick="angular.element(this).scope().calldeleteConfirm(' . $field_list->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete"></a>';
			})
			->rawColumns(['field_name', 'action'])
			->make(true);
	}

	public function getFieldFormdata($category_id, $id = NULL) {
		$field_category = Config::where('id', $category_id)->first();
		if (!$field_category) {
			return response()->json(['success' => false, 'error' => 'Field category not found']);
		}

		if (!$id) {
			$field = new Field;
			$this->data['action'] = 'Add';
			$field->status = 'Active';
			$field->unique = 'Yes';
		} else {
			$field = Field::withTrashed()->find($id);
			if (!$field) {
				return response()->json(['success' => false, 'error' => 'Field not found']);
			}
			$field->status = $field->deleted_at == NULL ? 'Active' : 'Inactive';
			$field->unique = 'Yes';
			$this->data['action'] = 'Edit';
		}

		$this->data['extras'] = [
			'field_type_list' => collect(FieldType::select('short_name as name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Field Type']),
			'list_source_list' => collect(FieldType::select('short_name as name', 'id')->get())->prepend(['id' => '', 'name' => 'Select List Source']),
			'source_table_list' => collect(FieldType::select('short_name as name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Source Type']),
		];
		$this->data['field_category'] = $field_category;
		$this->data['field'] = $field;
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function saveField(Request $request) {
		// dd($request->all());
		DB::beginTransaction();
		try {

			$error_messages = [
				'name.required' => 'Field name is required',
				'name.unique' => 'Field name has already been taken',
			];

			$validator = Validator::make($request->all(), [
				'name' => [
					'unique:fields,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',category_id,' . $request->category_id,
					'required:true',
				],
			], $error_messages);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			//MAX DATE AND MIN DATE VALIDATION
			if ($request->min_date && $request->max_date) {
				if (strtotime($request->min_date) > strtotime($request->max_date)) {
					return response()->json(['success' => false, 'errors' => ['Min Date should be lesser than max date']]);
				}
				if (strtotime($request->max_date) < strtotime($request->min_date)) {
					return response()->json(['success' => false, 'errors' => ['Max Date should be greater than min date']]);
				}
			}

			//MAX LENGTH AND MIN LENGTH VALIDATION
			if ($request->min_length && $request->max_length) {
				if ($request->min_length > $request->max_length) {
					return response()->json(['success' => false, 'errors' => ['Min length should be lesser than max length']]);
				}
				if ($request->max_length < $request->min_length) {
					return response()->json(['success' => false, 'errors' => ['Max length should be greater than min length']]);
				}
			}

			if ($request->id) {
				$field = Field::withTrashed()->find($request->id);
				$field->updated_at = date("Y-m-d H:i:s");
				$field->updated_by_id = Auth()->user()->id;
				$message = 'Field updated successfully';
			} else {
				$field = new Field();
				$field->created_at = date("Y-m-d H:i:s");
				$field->created_by_id = Auth()->user()->id;
				$message = 'Field added successfully';
			}

			if ($request->status == 'Inactive') {
				$field->deleted_at = date("Y-m-d H:i:s");
				$field->deleted_by_id = Auth()->user()->id;
			} else {
				$field->deleted_at = NULL;
				$field->deleted_by_id = NULL;
			}
			$field->fill($request->all());
			$field->company_id = Auth::user()->company_id;
			$field->save();

			DB::commit();
			return response()->json(['success' => true, 'message' => $message]);
		} catch (Exception $e) {
			DB::rollBack();
			// dd($e->getMessage());
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function delete($id) {
		$field = Field::withTrashed()->where('id', $id)->first();
		if (!$field) {
			return response()->json(['success' => false, 'errors' => ['Field not found']]);
		}
		Field::withTrashed()->where('id', $id)->forceDelete();
		return response()->json(['success' => true]);
	}

}
