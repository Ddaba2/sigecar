<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->nullable();
            }
        });

        if (Schema::hasColumn('users', 'phone')) {
            $users = \Illuminate\Support\Facades\DB::table('users')->select('id', 'phone')->get();
            foreach ($users as $user) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update(['telephone' => $user->phone]);
            }
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'telephone')) {
                $table->dropColumn('telephone');
            }
        });
    }
};