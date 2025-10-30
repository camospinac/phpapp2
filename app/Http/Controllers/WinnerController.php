<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Winner;
use Inertia\Inertia;

class WinnerController extends Controller
{
     public function index()
    {
       $winners = Winner::latest('win_date')
              ->get();

        return Inertia::render('Winners/Index', [
            'winners' => $winners,
        ]);
    }
}
