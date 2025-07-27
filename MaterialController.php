<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    $materials = Material::orderBy('created_at', 'desc')->get();
    return view('admin.materials.index', compact('materials'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('materials.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:article,pdf,audio,video,image',
        'file' => 'required|file|max:20480', // 20MB
    ]);

    $path = $request->file('file')->store('materials', 'public');

    $material = Material::create([
        'title' => $request->title,
        'slug' => Str::slug($request->title) . '-' . uniqid(),
        'description' => $request->description,
        'type' => $request->type,
        'file_path' => $path,
        'user_id' => auth()->id(),
        'is_approved' => false,
    ]);

    return redirect()->route('dashboard')->with('success', 'Materi berhasil diunggah dan menunggu persetujuan.');
    }

    public function approve($id)
    {
        $material = Material::findOrFail($id);
        $material->is_approved = true;
        $material->save();

        return redirect()->route('admin.materials.index')->with('success', 'Materi telah disetujui.');
    }




    /**
     * Display the specified resource.
     */
public function show($id)
{
    $material = Material::findOrFail($id);
    $comments = $material->comments()->with('replies.user', 'user')->get();
   if (auth()->check() && auth()->user()->hasRole('admin')) {
        return view('admin.materials.show', compact('material', 'comments'));
    } else {
        return view('materials.show', compact('material', 'comments'));
    }
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $material = Material::findOrFail($id);
        return view('admin.materials.edit', compact('material'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, $id)
{
    $material = Material::findOrFail($id);

    // Validasi input
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_approved' => 'required|in:0,1',
        'file' => 'nullable|file|max:20480', // max 20MB
    ]);

    // Update data field
    $material->title = $request->title;
    $material->description = $request->description;
    $material->is_approved = $request->is_approved;


    // Handle file upload (jika ada file baru diupload)
    if ($request->hasFile('file')) {
        // Hapus file lama jika perlu
        if ($material->file_path && \Storage::disk('public')->exists($material->file_path)) {
            \Storage::disk('public')->delete($material->file_path);
        }
        // Simpan file baru
        $path = $request->file('file')->store('materials', 'public');
        $material->file_path = $path;
    }

    $material->save();

    return redirect()->route('admin.materials.index')->with('success', 'Materi berhasil diupdate!');
}




    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $material = Material::findOrFail($id);

    // Hapus file jika ada
    if ($material->file_path && \Storage::disk('public')->exists($material->file_path)) {
        \Storage::disk('public')->delete($material->file_path);
    }

    $material->delete();

    return redirect()->route('admin.materials.index')->with('success', 'Materi berhasil dihapus!');
}

}
