<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EavDecimalC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('eav_decimal', function (Blueprint $table) {
			$table->unsignedInteger('entity_type_id');
			$table->unsignedInteger('entity_id');
			$table->unsignedInteger('field_group_id');
			$table->unsignedInteger('field_id');
			$table->decimal('value', 12, 2);

			$table->foreign('entity_type_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

			$table->foreign('field_group_id')->references('id')->on('field_groups')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('field_id')->references('id')->on('fields')->onDelete('CASCADE')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('eav_decimal');
	}
}
