<?php
// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class PostController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create posts')->only(['create', 'store']);
        $this->middleware('check.permission:edit posts')->only(['edit', 'update']);
        $this->middleware('check.permission:delete posts')->only(['destroy']);
        $this->middleware('check.permission:view posts')->only(['index']);
    }

    // Menampilkan daftar post
    public function index(Request $request)
    {
        $title="posts";
        $titleShow="Postingan";
        $posts = Post::all(); // Bisa diganti dengan query untuk menampilkan post tertentu

        if ($request->ajax()) {
        $posts = Post::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($posts)
        ->addColumn('action', function($row) use($title) {
            $editUrl = route('posts.edit', $row->id);
            $deleteUrl = route('posts.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('posts.index', compact('posts','title','titleShow'));
}

    // Menampilkan form untuk membuat post baru
public function create()
{
    return view('posts.create');
}

    // Menyimpan post baru
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    $post = Post::create([
        'name' => $request->name,
        'description' => $request->description,
        'user_id' => Auth::id(), // Menyimpan ID pengguna yang membuat post
        'created_by' => Auth::id(), // Menyimpan ID pengguna yang membuat post
        ]);

    return response()->json([
        'message' => 'Data berhasil disimpan.', 
        'icon' => 'success',
        'title' => 'Berhasil',
        'post' => $post
    ]);
}

    // Menampilkan form untuk mengedit post

public function edit(Post $post)
{
    // Mengembalikan data post dalam format JSON untuk digunakan dalam AJAX
    return response()->json($post);
}

    // Menyimpan perubahan post
public function update(Request $request, Post $post)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    $post->update([
        'name' => $request->name,
        'description' => $request->description,
        'updated_by' => Auth::id(), // Menyimpan ID pengguna yang membuat post
    ]);

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'post' => $post,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus post
public function destroy(Post $post)
{
    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $post->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $post->save();  // Simpan perubahan

    //Delete
    $post->delete();
    return response()->json([
        'message' => 'Data berhasil dihapus.',
        'icon' => 'success',
        'title' => 'Hapus!',
    ]);
}

    // show
public function show()
{
    $post = Post::select('id','name')->get();
    
    return response()->json($post->map(function ($posts) {
        return [
            'id' => $posts->id,
            'text' => $posts->name  // Ganti 'name' dengan field yang sesuai
        ];
    }));
}
}
