<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => Campaign::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Campaigns/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
        ]);

        // Si se activa esta campaña, desactivamos todas las demás
        if ($validated['is_active']) {
            Campaign::query()->update(['is_active' => false]);
        }

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('campaigns', 'public');
        }

        Campaign::create([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_active' => $validated['is_active'],
            'image_url' => $path,
        ]);

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaña creada.');
    }

    public function edit(Campaign $campaign)
    {
        return Inertia::render('Admin/Campaigns/Edit', ['campaign' => $campaign]);
    }

public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048', // La imagen es opcional al actualizar
            'is_active' => 'required|boolean',
        ]);

        // Si se activa esta campaña, desactivamos todas las demás
        if ($validated['is_active']) {
            Campaign::where('id', '!=', $campaign->id)->update(['is_active' => false]);
        }

        $path = $campaign->image_url;
        if ($request->hasFile('image')) {
            // Si ya existía una imagen, la borramos
            if ($campaign->image_url) {
                Storage::disk('public')->delete($campaign->image_url);
            }
            // Guardamos la nueva imagen
            $path = $request->file('image')->store('campaigns', 'public');
        }

        $campaign->update([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_active' => $validated['is_active'],
            'image_url' => $path,
        ]);

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaña actualizada con éxito.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->image_url) {
            Storage::disk('public')->delete($campaign->image_url);
        }
        $campaign->delete();
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaña eliminada.');
    }
}