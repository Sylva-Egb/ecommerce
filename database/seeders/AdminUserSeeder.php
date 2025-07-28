<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si l'admin existe déjà
        if (!User::where('email', 'romaricegbewole@gmail.com')->exists()) {
            User::create([
                'name' => 'Romaric',
                'email' => 'romaricegbewole@gmail.com',
                'phone' => '+2290166978010',
                'password' => Hash::make('Sylvanus'), // Changez ce mot de passe !
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            echo "✅ Utilisateur admin créé avec succès\n";
        } else {
            echo "❌  Utilisateur admin existe déjà\n";
        }
    }
}