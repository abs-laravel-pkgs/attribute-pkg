<?php

namespace Abs\AttributePkg;
use Abs\AttributePkg\Field;
use Abs\AttributePkg\FieldType;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
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
				'fields.*'
			)
			->where('fields.company_id', Auth::user()->company_id)
			->groupBy('fields.id')
			->orderBy('fields.id', 'Desc');

		return Datatables::of($fields_list)
			->addColumn('status', function ($field_list) {
				if ($field_list->deleted_at == NULL) {
					$status = "<td><span class='status-indicator green'></span>ACTIVE</td>";
				} else {
					$status = "<td><span class='status-indicator red'></span>INACTIVE</td>";
				}
				return $status;
			})
			->addColumn('action', function ($field_list) {

				$img_edit = asset('public/theme/img/table/cndn/edit.svg');
				$img_delete = asset('public/theme/img/table/cndn/delete.svg');

				return '<a href="#!/attribute-pkg/field/edit/' . $field_list->id . '" class="">
                        <img class="img-responsive" src="' . $img_edit . '" alt="Edit" />
                    	</a>
						<a href="javascript:;"  data-toggle="modal" data-target="#field-delete-modal" onclick="angular.element(this).scope().calldeleteConfirm(' . $field_list->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete"></a>';
			})
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
		} else {
			$field = Field::withTrashed()->find($id);
			if (!$field) {
				return response()->json(['success' => false, 'error' => 'Field not found']);
			}
			$field->status = $field->deleted_at == NULL ? 'Active' : 'Inactive';
			$this->data['action'] = 'Edit';
		}

		$this->data['extras'] = [
			'field_type_list' => collect(FieldType::select('name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Field Type']),
			'list_source_list' => collect(FieldType::select('name', 'id')->get())->prepend(['id' => '', 'name' => 'Select List Source']),
			'source_table_list' => collect(FieldType::select('name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Source Type']),
		];
		$this->data['field_category'] = $field_category;
		$this->data['field'] = $field;
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function saveField(Request $request) {
		dd($request->all());
		DB::beginTransaction();
		try {

			$validator = Validator::make($request->all(), [
				'name' => [
					'unique:fields,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',category_id,' . $request->category_id,
					'required:true',
				],
			]);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			if ($request->id) {
				$field = Field::find($request->id);
				$field->updated_at = date("Y-m-d H:i:s");
				$field->updated_by_id = Auth()->user()->id;

			} else {
				$field = new Field();
				$field->created_at = date("Y-m-d H:i:s");
				$field->created_by_id = Auth()->user()->id;
			}

			if ($request->status == 'InActive') {
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
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			// dd($e->getMessage());
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function deleteField($id) {
		$field = Field::withTrashed()->where('id', $id)->first();
		if (!$field) {
			return response()->json(['success' => false, 'errors' => ['Field not found']]);
		}
		Field::withTrashed()->where('id', $id)->forceDelete();
		return response()->json(['success' => true]);
	}

}
