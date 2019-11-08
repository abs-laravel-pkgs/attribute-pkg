<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FieldGroupFieldC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('field_group_field', function (Blueprint $table) {
			$table->unsignedInteger('field_group_id');
			$table->unsignedInteger('field_id');
			$table->boolean('is_required');

			$table->foreign('field_group_id')->references('id')->on('field_groups')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('field_id')->references('id')->on('fields')->onDelete('CASCADE')->onUpdate('cascade');

			$table->unique(["field_group_id", "field_id"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('field_group_field');
	}
}
