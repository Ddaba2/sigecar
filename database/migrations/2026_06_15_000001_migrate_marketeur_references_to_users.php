<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('marqueteurs')) {
            $this->repointExistingUserIdColumns();

            return;
        }

        $map = DB::table('marqueteurs')
            ->whereNotNull('user_id')
            ->pluck('user_id', 'id');

        foreach (DB::table('marqueteurs')->whereNull('user_id')->get() as $row) {
            $userId = DB::table('users')->insertGetId([
                'name' => $row->company_name,
                'email' => 'marqueteur-' . $row->id . '@sigecar.local',
                'password' => bcrypt(str()->random(32)),
                'role' => 'marketeur',
                'status' => $row->status ?? 'active',
                'company_name' => $row->company_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $map[$row->id] = $userId;
        }

        $this->migrateColumn('marketeur_stocks', 'marketeur_id', $map);
        $this->migrateColumn('depotages', 'marketeur_id', $map);
        $this->migrateColumn('chargements', 'marketeur_id', $map);
        $this->migrateColumn('cuves', 'marketeur_id', $map);
        $this->migrateColumn('cessions', 'cedant_id', $map);
        $this->migrateColumn('cessions', 'beneficiaire_id', $map);

        $this->renameToUserId('marketeur_stocks', 'marketeur_id');
        $this->renameToUserId('depotages', 'marketeur_id');
        $this->renameToUserId('chargements', 'marketeur_id');
        $this->renameToUserId('cuves', 'marketeur_id');

        $this->repointCessionUsers();
    }

    protected function migrateColumn(string $table, string $column, $map): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropForeignIfExists($table, $column);

        foreach (DB::table($table)->whereNotNull($column)->get() as $row) {
            $oldId = $row->{$column};
            if (isset($map[$oldId])) {
                DB::table($table)->where('id', $row->id)->update([$column => $map[$oldId]]);
            }
        }
    }

    protected function renameToUserId(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        // If the column is already user_id, ensure correct type and foreign key then exit
        if ($column === 'user_id' && Schema::hasColumn($table, 'user_id')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('user_id')->nullable()->change();
                $blueprint->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
            return;
        }

        $this->dropForeignIfExists($table, $column);

        if ($table === 'marketeur_stocks') {
            // user_id column already defined with foreign key in its own migration
            // Ensure correct type (nullable unsignedBigInteger)
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('user_id')->nullable()->change();
            });
            return;
        }

        // Existing logic for other tables
        $this->dropForeignIfExists($table, $column);

        if ($column !== 'user_id') {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->renameColumn($column, 'user_id');
            });
        }
        // Ensure column definition and foreign key for other tables
        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unsignedBigInteger('user_id')->nullable()->change();
            $blueprint->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    protected function repointCessionUsers(): void
    {
        if (! Schema::hasTable('cessions')) {
            return;
        }

        $this->dropForeignIfExists('cessions', 'cedant_id');
        $this->dropForeignIfExists('cessions', 'beneficiaire_id');

        Schema::table('cessions', function (Blueprint $blueprint) {
            $blueprint->foreign('cedant_id')->references('id')->on('users')->cascadeOnDelete();
            $blueprint->foreign('beneficiaire_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    protected function repointExistingUserIdColumns(): void
    {
        foreach (['marketeur_stocks', 'depotages', 'chargements', 'cuves'] as $table) {
            if ($table === 'marketeur_stocks') {
                continue;
            }
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'marketeur_id')) {
                $this->renameToUserId($table, 'marketeur_id');
            } elseif (Schema::hasColumn($table, 'user_id')) {
                $this->dropForeignIfExists($table, 'user_id');
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreign('user_id')
                        ->references('id')->on('users')->nullOnDelete();
                });
            }
        }

        $this->repointCessionUsers();
    }

    protected function dropForeignIfExists(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropForeign([$column]);
            });
        } catch (\Throwable) {
            // FK déjà absente ou nom différent selon le SGBD.
        }
    }

    public function down(): void
    {
        // Non réversible sans la table marqueteurs.
    }
};
