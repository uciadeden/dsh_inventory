<?php
// app/Http/Controllers/ProductController.php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create products')->only(['create', 'store']);
        $this->middleware('check.permission:edit products')->only(['edit', 'update']);
        $this->middleware('check.permission:delete products')->only(['destroy']);
        $this->middleware('check.permission:view products')->only(['index']);
    }

    // Menampilkan daftar product
    public function index(Request $request)
    {
        $title="products";
        $titleShow="Produk";
        $products = Product::all(); // Bisa diganti dengan query untuk menampilkan product tertentu

        if ($request->ajax()) {
        $products = Product::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($products)
        ->addColumn('category_name', function($product) {
                // Akses nama kategori melalui relasi
            return $product->category ? $product->category->name : 'No Category';
        })
        ->addColumn('action', function($row) use($title) {
            $editUrl = route('products.edit', $row->id);
            $deleteUrl = route('products.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('master.products.index', compact('products','title','titleShow'));
}

    // Menampilkan form untuk membuat product baru
public function create()
{
    return view('products.create');
}

    // Menyimpan product baru
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|integer',
        'category_id' => 'required|integer|max:255',
        'description' => 'nullable|string',
    ]);

    $product = Product::create([
        'name' => $request->name,
        'price' => $request->price,
        'category_id' => $request->category_id,
        'description' => $request->description,
        'user_id' => Auth::id(), // Menyimpan ID pengguna yang membuat product
        'created_by' => Auth::id(), // Menyimpan ID pengguna yang membuat product
    ]);

    return response()->json([
        'message' => 'Data berhasil disimpan.', 
        'icon' => 'success',
        'title' => 'Berhasil',
        'product' => $product
    ]);
}

    // Menampilkan form untuk mengedit product

public function edit(Product $product)
{
    $product->load('category');
    // Mengembalikan data product dalam format JSON untuk digunakan dalam AJAX
    return response()->json($product);
}

    // Menyimpan perubahan product
public function update(Request $request, Product $product)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|integer',
        'category_id' => 'required|integer|max:255',
        'description' => 'nullable|string',
    ]);

    $product->update([
        'name' => $request->name,
        'price' => $request->price,
        'category_id' => $request->category_id,
        'description' => $request->description,
        'updated_by' => Auth::id(), // Menyimpan ID pengguna yang membuat product
    ]);

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'product' => $product,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus product
public function destroy(Product $product)
{
    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $product->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $product->save();  // Simpan perubahan

    //Delete
    $product->delete();
    return response()->json([
        'message' => 'Data berhasil dihapus.',
        'icon' => 'success',
        'title' => 'Hapus!',
    ]);
}
    // show
public function show(Request $request)
{
    $search = $request->get('q');  // Mendapatkan parameter pencarian
    
    $product = Product::with('category')->select('id', 'name', 'category_id', 'price')->when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%");  // Filter berdasarkan nama produk
        })
        ->get();

    return response()->json($product->map(function ($products) {
        return [
            'id' => $products->id,
            'category' => $products->category,  // Tampilkan seluruh data category untuk debug
            'category_name' => $products->category?->name,
            'text' => $products->name,
            'price' => $products->price,
        ];
    }));
}
}
