<?php

namespace Abs\AttributePkg;
use Abs\AttributePkg\FieldGroup;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class FieldGroupController extends Controller {

	public function __construct() {
	}

	public function getFieldGroupFilterdata($category_id = NULL) {
		$field_category = Config::where('id', $category_id)->first();
		if (!$field_category) {
			return response()->json(['success' => false, 'errors' => ['Field category not found']]);
		}

		return response()->json(['success' => true, 'field_category' => $field_category]);
	}

	public function getFieldGroupList() {
		$field_groups_list = FieldGroup::withTrashed()
			->select(
				'field_groups.id',
				'field_groups.name',
				'field_groups.category_id',
				'field_groups.deleted_at',
				DB::raw('count(field_group_field.field_id) as fields_count')
			)
			->leftjoin('field_group_field', 'field_group_field.field_group_id', 'field_groups.id')
			->where('field_groups.company_id', Auth::user()->company_id)
			->groupBy('field_groups.id')
			->orderBy('field_groups.id', 'Desc');

		return Datatables::of($field_groups_list)
			->addColumn('field_group_name', function ($field_group_list) {
				if ($field_group_list->deleted_at == NULL) {
					$name = "<td><span class='status-indicator green'></span>" . $field_group_list->name . "</td>";
				} else {
					$name = "<td><span class='status-indicator red'></span>" . $field_group_list->name . "</td>";
				}
				return $name;
			})
			->addColumn('action', function ($field_group_list) {

				$img_edit = asset('public/theme/img/table/cndn/edit.svg');
				$img_delete = asset('public/theme/img/table/cndn/delete.svg');

				return '<a href="#!/attribute-pkg/field-group/edit/' . $field_group_list->category_id . '/' . $field_group_list->id . '" class="">
                        <img class="img-responsive" src="' . $img_edit . '" alt="Edit" />
                    	</a>
						<a href="javascript:;"  data-toggle="modal" data-target="#field-group-delete-modal" onclick="angular.element(this).scope().calldeleteConfirm(' . $field_group_list->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete"></a>';
			})
			->rawColumns(['field_group_name', 'action'])
			->make(true);
	}

	public function getFieldGroupFormdata($category_id, $id = NULL) {
		$field_category = Config::where('id', $category_id)->first();
		if (!$field_category) {
			return response()->json(['success' => false, 'error' => 'Field category not found']);
		}

		if (!$id) {
			$field_group = new FieldGroup;
			$this->data['action'] = 'Add';
			$field_group->status = 'Active';
			$field_group->fields = [];
		} else {
			$field_group = FieldGroup::withTrashed()->with([
				'fields',
			])->find($id);
			if (!$field_group) {
				return response()->json(['success' => false, 'error' => 'Field group not found']);
			}
			foreach ($field_group->fields as $key => $field) {
				$field->is_required_status = $field->pivot->is_required ? 'Yes' : 'No';
			}
			$field_group->status = $field_group->deleted_at == NULL ? 'Active' : 'Inactive';
			$this->data['action'] = 'Edit';
		}

		$this->data['extras'] = [
			'fields_list' => collect(Field::select('name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Field']),
			'field_type_list' => Field::select('field_types.name', 'fields.id')->join('field_types', 'field_types.id', 'fields.type_id')->get()->keyBy('id'),
		];
		$this->data['field_category'] = $field_category;
		$this->data['field_group'] = $field_group;
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function saveFieldGroup(Request $request) {
		// dd($request->all());
		DB::beginTransaction();
		try {

			$error_messages = [
				'name.required' => 'Field group name is required',
				'name.unique' => 'Field group name has already been taken',
			];

			$validator = Validator::make($request->all(), [
				'name' => [
					'unique:field_groups,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',category_id,' . $request->category_id,
					'required:true',
				],
			], $error_messages);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			//VALIDATE FIELD-GROUP FIELD UNIQUE
			if ($request->fields && !empty($request->fields)) {
				$field_group_fields = collect($request->fields)->pluck('id')->toArray();
				$field_group_fields_unique = array_unique($field_group_fields);
				if (count($field_group_fields) != count($field_group_fields_unique)) {
					return response()->json(['success' => false, 'errors' => ['Field has already been taken']]);
				}
			}

			if ($request->id) {
				$field_group = FieldGroup::withTrashed()->find($request->id);
				$field_group->updated_at = date("Y-m-d H:i:s");
				$field_group->updated_by_id = Auth()->user()->id;
			} else {
				$field_group = new FieldGroup();
				$field_group->created_at = date("Y-m-d H:i:s");
				$field_group->created_by_id = Auth()->user()->id;
			}

			if ($request->status == 'Inactive') {
				$field_group->deleted_at = date("Y-m-d H:i:s");
				$field_group->deleted_by_id = Auth()->user()->id;
			} else {
				$field_group->deleted_at = NULL;
				$field_group->deleted_by_id = NULL;
			}
			$field_group->fill($request->all());
			$field_group->company_id = Auth::user()->company_id;
			$field_group->save();

			//SAVE FIELD-GROUP FIELD
			$field_group->fields()->sync([]);
			if ($request->fields) {
				if (!empty($request->fields)) {
					foreach ($request->fields as $key => $field) {
						$is_required = $field['is_required'] == 'Yes' ? 1 : 0;
						$field_group->fields()->attach($field['id'], ['is_required' => $is_required]);
					}
				}
			}

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Field group saved successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			// dd($e->getMessage());
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function delete($id) {
		$field_group = FieldGroup::withTrashed()->where('id', $id)->first();
		if (!$field_group) {
			return response()->json(['success' => false, 'errors' => ['Field group not found']]);
		}
		FieldGroup::withTrashed()->where('id', $id)->forceDelete();
		return response()->json(['success' => true]);
	}

}
