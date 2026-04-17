<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            if (Schema::hasColumn('logs', 'table_cible')) {
                $table->dropColumn('table_cible');
            }
            if (Schema::hasColumn('logs', 'element_id')) {
                $table->dropColumn('element_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->string('table_cible')->nullable()->after('action');
            $table->unsignedBigInteger('element_id')->nullable()->after('table_cible');
        });
    }
};
