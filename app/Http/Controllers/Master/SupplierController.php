<?php
// app/Http/Controllers/SupplierController.php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Supplier;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create suppliers')->only(['create', 'store']);
        $this->middleware('check.permission:edit suppliers')->only(['edit', 'update']);
        $this->middleware('check.permission:delete suppliers')->only(['destroy']);
        $this->middleware('check.permission:view suppliers')->only(['index']);
    }

    // Menampilkan daftar supplier
    public function index(Request $request)
    {
        $title="suppliers";
        $titleShow="Supplier";
        $suppliers = Supplier::all(); // Bisa diganti dengan query untuk menampilkan supplier tertentu

        if ($request->ajax()) {
        $suppliers = Supplier::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($suppliers)
        ->addColumn('action', function($row) use($title) {
            $editUrl = route('suppliers.edit', $row->id);
            $deleteUrl = route('suppliers.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('master.suppliers.index', compact('suppliers','title','titleShow'));
}

    // Menampilkan form untuk membuat supplier baru
public function create()
{
    return view('master.suppliers.create');
}

    // Menyimpan supplier baru
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'contact_phone' => 'required|string|max:255',
        'contact_name' => 'required|string|max:255',
        'email' => 'required|string|max:255',
        'address' => 'required|string|max:255'
    ]);

    $supplier = Supplier::create([
        'name' => $request->name,
        'contact_name' => $request->contact_name,
        'contact_phone' => $request->contact_phone,
        'email' => $request->email,
        'address' => $request->address,
        'user_id' => Auth::id(), // Menyimpan ID pengguna yang membuat supplier
        'created_by' => Auth::id(), // Menyimpan ID pengguna yang membuat supplier
        ]);

    return response()->json([
        'message' => 'Data berhasil disimpan.', 
        'icon' => 'success',
        'title' => 'Berhasil',
        'supplier' => $supplier
    ]);
}

    // Menampilkan form untuk mengedit supplier

public function edit(Supplier $supplier)
{
    // Mengembalikan data supplier dalam format JSON untuk digunakan dalam AJAX
    return response()->json($supplier);
}

    // Menyimpan perubahan supplier
public function update(Request $request, Supplier $supplier)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'contact_phone' => 'required|string|max:255',
        'contact_name' => 'required|string|max:255',
        'email' => 'required|string|max:255',
        'address' => 'required|string|max:255'
    ]);

    $supplier->update([
        'name' => $request->name,
        'contact_name' => $request->contact_name,
        'contact_phone' => $request->contact_phone,
        'email' => $request->email,
        'address' => $request->address,
        'updated_by' => Auth::id(), // Menyimpan ID pengguna yang membuat supplier
    ]);

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'supplier' => $supplier,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus supplier
public function destroy(Supplier $supplier)
{
    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $supplier->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $supplier->save();  // Simpan perubahan

    //Delete
    $supplier->delete();
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
    $supplier = Supplier::select('id','name')->when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%");  // Filter berdasarkan nama produk
        })
        ->get();
    
    return response()->json($supplier->map(function ($suppliers) {
        return [
            'id' => $suppliers->id,
            'text' => $suppliers->name  // Ganti 'name' dengan field yang sesuai
        ];
    }));
}
}
