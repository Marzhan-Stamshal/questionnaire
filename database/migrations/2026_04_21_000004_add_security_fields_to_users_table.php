<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSecurityFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('user')->after('is_admin');
            $table->boolean('can_view_sensitive_reports')->default(false)->after('role');
        });

        DB::table('users')->where('is_admin', 1)->update([
            'role' => 'admin',
            'can_view_sensitive_reports' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'can_view_sensitive_reports']);
        });
    }
}

