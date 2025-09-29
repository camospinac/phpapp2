<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Winner;
use Inertia\Inertia;

class WinnerController extends Controller
{
     public function index()
    {
        $winners = Winner::with('user:id,nombres,apellidos,identification_number')
            ->latest('win_date')
            ->get();

        return Inertia::render('Winners/Index', [
            'winners' => $winners,
        ]);
    }
}
