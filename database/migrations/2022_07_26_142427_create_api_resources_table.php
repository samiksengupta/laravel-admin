<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Samik\LaravelAdmin\Models\ApiResource;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('method')->default(ApiResource::METHOD_GET);
            $table->string('route')->nullable();
            $table->text('fields')->nullable();
            $table->boolean('secure')->default(0);
            $table->boolean('hidden')->default(0);
            $table->boolean('disabled')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_resources');
    }
};
