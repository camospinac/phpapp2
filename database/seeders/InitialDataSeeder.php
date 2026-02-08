<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Para evitar problemas, desactivamos temporalmente la revisión de llaves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vaciamos las tablas para empezar de cero cada vez que ejecutemos el seeder
        User::truncate();
        Transaction::truncate();
        
        // Reactivamos la revisión
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- CREACIÓN DE USUARIOS ---

        // 1. Creamos el usuario Administrador
        User::create([
            'nombres' => 'Admin',
            'apellidos' => 'Principal',
            'celular' => '3001234567',
            'email' => 'admin@grupoeon.com',
            'password' => Hash::make('EON2025$'), // Contraseña: password
            'rol' => 'admin',
        ]);

        User::create([
            'nombres' => 'Asesor',
            'apellidos' => 'Comercial',
            'celular' => '3001112233',
            'email' => 'asesor@grupoeon.com',
            'password' => Hash::make('Admin2025$'), // Contraseña: password
            'rol' => 'asesor', // <-- NUEVO ROL
        ]);
    }
}