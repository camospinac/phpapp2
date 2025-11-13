<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RankRequest;
use App\Models\Rank;
use Inertia\Inertia;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function index()
    {
        $ranks = Rank::orderBy('required_referrals')->get();
        return Inertia::render('Admin/Ranks/Index', [
            'ranks' => $ranks,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Ranks/Create');
    }

    public function store(RankRequest $request)
    {
        Rank::create($request->validated());
        return redirect()->route('admin.ranks.index')->with('success', 'Rango creado exitosamente.');
    }

    public function edit(Rank $rank)
    {
        return Inertia::render('Admin/Ranks/Edit', [
            'rank' => $rank,
        ]);
    }

    public function update(RankRequest $request, Rank $rank)
    {
        $rank->update($request->validated());
        return redirect()->route('admin.ranks.index')->with('success', 'Rango actualizado exitosamente.');
    }

    public function destroy(Rank $rank)
    {
        $rank->delete();
        return redirect()->route('admin.ranks.index')->with('success', 'Rango eliminado exitosamente.');
    }
}