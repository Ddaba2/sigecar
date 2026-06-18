<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Update users that have a linked marqueteur to have role 'marketeur'
        DB::table('marqueteurs')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->each(function ($userId) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['role' => 'marketeur']);
            });
    }

    public function down(): void
    {
        // No automatic rollback; role changes are irreversible without backup data
    }
};
