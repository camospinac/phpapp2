<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserEmail; 

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {   
        $documentTypes = [
            'TARJETA IDENTIDAD',
            'CEDULA CIUDANIA',
            'CEDULA EXTRANJERIA',
            'PASAPORTE'
        ];

        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'identification_type' => ['required', Rule::in($documentTypes)], // Valida que sea uno de los tipos permitidos
            'identification_number' => 'required|string|max:255|unique:'.User::class, // Asegura que el número de ID sea único
            'celular' => 'required|string|max:20|unique:users',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'], // Valida que el código de referido exista
        ]);

        // Buscamos al usuario que refirió, si se proporcionó un código
        $referrer = null;
        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }

        // Creamos al nuevo usuario
        $user = User::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'identification_type' => $request->identification_type,
            'identification_number' => $request->identification_number,
            'celular' => $request->celular,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referred_by_id' => $referrer?->id, // Guardamos el ID del referidor
        ]);

        $baseCode = Str::slug($user->nombres . $user->apellidos, '');

        
        do {
            
            $code = Str::upper(Str::limit($baseCode, 4, '') . random_int(1000, 9999));
        } while (User::where('referral_code', $code)->exists());

        $user->referral_code = $code;
        $user->save();

        Mail::to($user->email)->send(new WelcomeUserEmail($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
