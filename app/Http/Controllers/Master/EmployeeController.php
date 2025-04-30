<?php
// app/Http/Controllers/EmployeeController.php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Employee;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create employees')->only(['create', 'store']);
        $this->middleware('check.permission:edit employees')->only(['edit', 'update']);
        $this->middleware('check.permission:delete employees')->only(['destroy']);
        $this->middleware('check.permission:view employees')->only(['index']);
    }

    // Menampilkan daftar employee
    public function index(Request $request)
    {
        $title="employees";
        $titleShow="Karyawan";
        $employees = Employee::all(); // Bisa diganti dengan query untuk menampilkan employee tertentu

        if ($request->ajax()) {
        $employees = Employee::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($employees)
        ->addColumn('action', function($row) use($title) {
            $editUrl = route('employees.edit', $row->id);
            $deleteUrl = route('employees.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('master.employees.index', compact('employees','title','titleShow'));
}

    // Menampilkan form untuk membuat employee baru
public function create()
{
    return view('employees.create');
}

    // Menyimpan employee baru
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'phone' => 'required|string|max:255',
        'email' => 'nullable|string|max:255',
    ]);

    $employee = Employee::create([
        'name' => $request->name,
        'position' => $request->position,
        'phone' => $request->phone,
        'email' => $request->email,
        'user_id' => Auth::id(), // Menyimpan ID pengguna yang membuat employee
        'created_by' => Auth::id(), // Menyimpan ID pengguna yang membuat employee
        ]);

    return response()->json([
        'message' => 'Data berhasil disimpan.', 
        'icon' => 'success',
        'title' => 'Berhasil',
        'employee' => $employee
    ]);
}

    // Menampilkan form untuk mengedit employee

public function edit(Employee $employee)
{
    // Mengembalikan data employee dalam format JSON untuk digunakan dalam AJAX
    return response()->json($employee);
}

    // Menyimpan perubahan employee
public function update(Request $request, Employee $employee)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'phone' => 'required|string|max:255',
        'email' => 'nullable|string|max:255',
    ]);

    $employee->update([
        'name' => $request->name,
        'position' => $request->position,
        'phone' => $request->phone,
        'email' => $request->email,
        'updated_by' => Auth::id(), // Menyimpan ID pengguna yang membuat employee
    ]);

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'employee' => $employee,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus employee
public function destroy(Employee $employee)
{
    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $employee->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $employee->save();  // Simpan perubahan

    //Delete
    $employee->delete();
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
    $employee = Employee::select('id','name')->when($search, function ($query, $search) {
            return $query->where('name', 'like', "%$search%");  // Filter berdasarkan nama produk
        })
        ->get();
    
    return response()->json($employee->map(function ($employees) {
        return [
            'id' => $employees->id,
            'text' => $employees->name  // Ganti 'name' dengan field yang sesuai
        ];
    }));
}
}
