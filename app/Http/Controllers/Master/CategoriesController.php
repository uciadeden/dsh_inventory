<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class CategoriesController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create categories')->only(['create', 'store']);
        $this->middleware('check.permission:edit categories')->only(['edit', 'update']);
        $this->middleware('check.permission:delete categories')->only(['destroy']);
        $this->middleware('check.permission:view categories')->only(['index']);
    }

    // Menampilkan daftar categories
    public function index(Request $request)
    {
        $title="categories";
        $titleShow="Kategori";
        $categories = Categories::all(); // Bisa diganti dengan query untuk menampilkan categories tertentu

        if ($request->ajax()) {
        $categories = Categories::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($categories)
        ->addColumn('action', function($row) use($title) {
            $editUrl = route('categories.edit', $row->id);
            $deleteUrl = route('categories.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('categories.index', compact('categories','title','titleShow'));
}

    // Menampilkan form untuk membuat categories baru
public function create()
{
    return view('categories.create');
}

    // Menyimpan categories baru
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    $categories = Categories::create([
        'name' => $request->name,
        'description' => $request->description,
        'user_id' => Auth::id(), // Menyimpan ID pengguna yang membuat categories
        'created_by' => Auth::id(), // Menyimpan ID pengguna yang membuat categories
    ]);

    return response()->json([
        'message' => 'Data berhasil disimpan.', 
        'icon' => 'success',
        'title' => 'Berhasil',
        'categories' => $categories
    ]);
}

    // Menampilkan form untuk mengedit categories

public function edit(Categories $category)
{
    // Mengembalikan data categories dalam format JSON untuk digunakan dalam AJAX
    return response()->json($category);
}

    // Menyimpan perubahan categories
public function update(Request $request, Categories $category)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    $category->update([
        'name' => $request->name,
        'description' => $request->description,
        'updated_by' => Auth::id(), // Menyimpan ID pengguna yang membuat categories
    ]);

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'category' => $category,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus categories
public function destroy(Categories $category)
{
    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $category->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $category->save();  // Simpan perubahan

    //Delete
    $category->delete();
    return response()->json([
        'message' => 'Data berhasil dihapus.',
        'icon' => 'success',
        'title' => 'Hapus!',
    ]);
}

    // show
public function show()
{
    $categories = Categories::select('id','name')->get();
    
    return response()->json($categories->map(function ($category) {
        return [
            'id' => $category->id,
            'text' => $category->name  // Ganti 'name' dengan field yang sesuai
        ];
    }));
}
}
