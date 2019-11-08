<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FieldsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('fields', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->string('name', 191);
			$table->unsignedInteger('type_id');
			$table->unsignedInteger('list_source_id')->nullable();
			$table->unsignedInteger('source_table_id')->nullable();
			$table->unsignedInteger('config_type_id')->nullable();
			$table->unsignedMediumInteger('min_length')->nullable();
			$table->unsignedMediumInteger('max_length')->nullable();
			$table->date('min_date')->nullable();
			$table->date('max_date')->nullable();
			$table->unsignedInteger('created_by_id')->nullable();
			$table->unsignedInteger('updated_by_id')->nullable();
			$table->unsignedInteger('deleted_by_id')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');
			$table->unique(["company_id", "name"]);
			$table->foreign('type_id')->references('id')->on('field_types')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('list_source_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

			$table->foreign('config_type_id')->references('id')->on('config_types')->onDelete('CASCADE')->onUpdate('cascade');

			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('fields');
	}
}
