<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('phone')->nullable()->unique()->after('username');
            $table->text('preferences')->nullable()->after('password');
            $table->unsignedTinyInteger('active')->default(1)->after('preferences');
            $table->foreignId('role_id')->nullable()->after('preferences')->constrained('roles')->onUpdate('cascade')->onDelete('set null');
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('deleted_at');
            $table->dropColumn('role_id');
            $table->dropColumn('active');
            $table->dropColumn('preferences');
            $table->dropColumn('username');
        });
    }
};
