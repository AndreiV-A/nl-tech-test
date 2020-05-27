<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
			$table->string('service');
			$table->string('access_token');
			$table->string('refresh_token')->nullable();
			$table->string('scope')->nullable();
			$table->string('type')->nullable();
			$table->unsignedInteger('expiry')->nullable();
			$table->boolean('stale')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_tokens');
    }
}
