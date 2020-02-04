<?php

use Illuminate\Database\Seeder;

class FieldTypeSeeder extends Seeder {
	public function run() {

		$this->call(Abs\AttributePkg\Database\Seeds\FieldTypePkgSeeder::class);

	}
}