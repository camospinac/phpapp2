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
            'email' => 'admin@financepwa.com',
            'password' => Hash::make('password'), // Contraseña: password
            'rol' => 'admin',
        ]);

        // 2. Creamos un usuario de prueba
        $normalUser = User::create([
            'nombres' => 'Usuario',
            'apellidos' => 'De Pruebas',
            'celular' => '3109876543',
            'email' => 'usuario@financepwa.com',
            'password' => Hash::make('password'), // Contraseña: password
            'rol' => 'usuario',
        ]);

        // --- CREACIÓN DE TRANSACCIONES PARA EL USUARIO DE PRUEBA ---

        Transaction::create([
            'id_user' => $normalUser->id,
            'tipo' => 'abono',
            'monto' => 1500000.00,
            'observacion' => 'Salario Quincena 1',
        ]);

        Transaction::create([
            'id_user' => $normalUser->id,
            'tipo' => 'retiro',
            'monto' => 80000.00,
            'observacion' => 'Compra supermercado',
        ]);

        Transaction::create([
            'id_user' => $normalUser->id,
            'tipo' => 'retiro',
            'monto' => 50000.00,
            'observacion' => 'Plan de datos celular',
        ]);
        
        Transaction::create([
            'id_user' => $normalUser->id,
            'tipo' => 'abono',
            'monto' => 250000.00,
            'observacion' => 'Pago proyecto freelance',
        ]);

        Transaction::create([
            'id_user' => $normalUser->id,
            'tipo' => 'retiro',
            'monto' => 120000.00,
            'observacion' => 'Cena fin de semana',
        ]);
    }
}