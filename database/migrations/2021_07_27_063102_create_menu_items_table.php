<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onUpdate('cascade')->onDelete('set null');
            $table->string('text')->nullable();
            $table->string('path')->nullable();
            $table->string('icon_class')->nullable();
            $table->string('target')->default('_self');
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->onUpdate('cascade')->onDelete('set null');
            $table->unsignedTinyInteger('order')->default(1);
            $table->unsignedTinyInteger('display')->default(1);
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
        Schema::dropIfExists('menu_items');
    }
}
