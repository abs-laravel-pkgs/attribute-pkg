<?php
namespace Abs\AttributePkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class AttributePkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//MASTER > FIELDS
			4000 => [
				'display_order' => 10,
				'parent_id' => 2,
				'name' => 'fields',
				'display_name' => 'Fields',
			],
			4001 => [
				'display_order' => 1,
				'parent_id' => 4000,
				'name' => 'add-field',
				'display_name' => 'Add',
			],
			4002 => [
				'display_order' => 2,
				'parent_id' => 4000,
				'name' => 'edit-field',
				'display_name' => 'Edit',
			],
			4003 => [
				'display_order' => 3,
				'parent_id' => 4000,
				'name' => 'delete-field',
				'display_name' => 'Delete',
			],

			//MASTER > FIELD GROUPS
			4020 => [
				'display_order' => 11,
				'parent_id' => 2,
				'name' => 'field-groups',
				'display_name' => 'Field Groups',
			],
			4021 => [
				'display_order' => 1,
				'parent_id' => 4020,
				'name' => 'add-field-group',
				'display_name' => 'Add',
			],
			4022 => [
				'display_order' => 2,
				'parent_id' => 4020,
				'name' => 'edit-field-group',
				'display_name' => 'Edit',
			],
			4023 => [
				'display_order' => 3,
				'parent_id' => 4020,
				'name' => 'delete-field-group',
				'display_name' => 'Delete',
			],

		];

		foreach ($permissions as $permission_id => $permsion) {
			$permission = Permission::firstOrNew([
				'id' => $permission_id,
			]);
			$permission->fill($permsion);
			$permission->save();
		}
		//$this->call(RoleSeeder::class);

	}
}