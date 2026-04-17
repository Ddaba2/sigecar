<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Vérifier si l'admin existe déjà
        $admin = User::where('email', 'admin@sigecar.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@sigecar.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
            ]);

            echo "Admin user created successfully!\n";
        } else {
            // Mettre à jour le mot de passe
            $admin->update([
                'password' => Hash::make('admin123')
            ]);
            echo "Admin password updated!\n";
        }

        // Créer un gestionnaire
        $gestionnaire = User::where('email', 'gestionnaire@sigecar.com')->first();
        if (!$gestionnaire) {
            User::create([
                'name' => 'Gestionnaire',
                'email' => 'gestionnaire@sigecar.com',
                'password' => Hash::make('gest123'),
                'role' => 'gestionnaire',
                'status' => 'active',
            ]);
            echo "Gestionnaire user created successfully!\n";
        }

        // Créer un marketeur
        $marketeur = User::where('email', 'marketeur@sigecar.com')->first();
        if (!$marketeur) {
            User::create([
                'name' => 'Petro Bama',
                'email' => 'marketeur@sigecar.com',
                'password' => Hash::make('mark123'),
                'role' => 'marketeur',
                'company_name' => 'Petro Bama',
                'status' => 'active',
            ]);
            echo "Marketeur user created successfully!\n";
        }
    }
}
