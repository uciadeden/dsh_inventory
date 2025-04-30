<?php
// app/Http/Controllers/TransactionController.php
namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use App\Models\Transaction\ReceiptDetail;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Carbon\Carbon;

class TransactionController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create transactions')->only(['create', 'store']);
        $this->middleware('check.permission:edit transactions')->only(['edit', 'update']);
        $this->middleware('check.permission:delete transactions')->only(['destroy']);
        $this->middleware('check.permission:view transactions')->only(['index']);
    }

    // Menampilkan daftar transaction
    public function index(Request $request)
    {
        $title="transactions";
        $titleShow="Pembelian";
        $transactions = TransactionDetail::all(); // Bisa diganti dengan query untuk menampilkan transaction tertentu

        if ($request->ajax()) {
        $transactions = TransactionDetail::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($transactions)
        ->addColumn('supplier_name', function($transactions) {
                // Akses nama kategori melalui relasi
            return $transactions->transaction->supplier ? $transactions->transaction->supplier->name : 'No Category';
        })->addColumn('transaction_type', function($transactions) {
                // Akses nama kategori melalui relasi
            return $transactions->transaction ? ($transactions->transaction->transaction_type == "purchase") ? 'pembelian' : 'penjualan' : 'No Category';
        })->addColumn('employee_name', function($transactions) {
                // Akses nama kategori melalui relasi
            return $transactions->transaction->employee ? $transactions->transaction->employee->name : 'No Category';
        })->addColumn('product_name', function($transactions) {
                // Akses nama kategori melalui relasi
            return $transactions->product ? $transactions->product->name : 'No Category';
        })->addColumn('code', function($transactions) {
                // Akses nama kategori melalui relasi
            return $transactions->transaction ? $transactions->transaction->code : 'No Category';
        })->addColumn('action', function($row) use($title) {
            $editUrl = route('transactions.edit', $row->id);
            $deleteUrl = route('transactions.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('transaction.transactions.index', compact('transactions','title','titleShow'));
}

    // Menampilkan form untuk membuat transaction baru
public function create()
{
    return view('transactions.create');
}

    // Menyimpan transaction baru
public function store(Request $request)
{
    $request->validate([
        'transaction_type' => 'required|string|max:255',
        'supplier_id' => 'required|integer',
        'employee_id' => 'required|integer',
        'total_amount' => 'required|integer',
        'product_id' => 'required|array',
        'quantity' => 'required|array',
        'unit_price' => 'required|array',
        'subtotal' => 'required|array',
    ]);
 // Mulai transaksi dengan DB Transaction untuk memastikan atomicity
    DB::transaction(function () use ($request) {
            // Membuat transaksi utama
        $transaction = Transaction::create([
            'code' => $this->generateKodeTransaksi(),
            'transaction_type' => $request->transaction_type,
            'supplier_id' => $request->supplier_id,
            'employee_id' => $request->employee_id,
            'total_amount' => $request->total_amount,
            'user_id' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

            // Menyimpan detail transaksi
        $products = $request->input('product_id');
        $quantities = $request->input('quantity');
        $unitPrices = $request->input('unit_price');
        $subtotals = $request->input('subtotal');

            // Loop untuk menyimpan setiap detail produk
        foreach ($products as $index => $productId) {
            $transaction->transactionDetail()->create([
                'product_id' => $productId,
                'quantity' => $quantities[$index],
                'unit_price' => $unitPrices[$index],
                'subtotal' => $subtotals[$index],
            ]);
        }
    });

        // Return response sukses
    return response()->json([
        'message' => 'Data berhasil disimpan.',
        'icon' => 'success',
        'title' => 'Berhasil'
    ]);
}

    // Menampilkan form untuk mengedit transaction

public function edit(TransactionDetail $transaction)
{
    $transaction = Transaction::with('transactionDetail','transactionDetail.product','employee','supplier')->where('id',$transaction->transaction_id)->first();

    return response()->json($transaction);
}

    // Menyimpan perubahan transaction
public function update(Request $request, Transaction $transaction)
{
    $request->validate([
        'transaction_type' => 'required|in:purchase,sale', // Tipe transaksi
        'supplier_id' => 'required|exists:suppliers,id', // Validasi supplier
        'employee_id' => 'required|exists:employees,id', // Validasi karyawan
        'total_amount' => 'required|numeric|min:0', // Validasi total
        'product_id' => 'required|array', // Produk yang dipilih
        'quantity' => 'required|array', // Kuantitas yang dimasukkan
        'unit_price' => 'required|array', // Harga satuan
        'subtotal' => 'required|array', // Subtotal
    'deleted' => 'nullable|array', // Tambahkan validasi untuk deleted[] jika ada
]);
    
    // Menghapus transaksi detail yang ditandai untuk dihapus
    if ($request->has('deleted')) {
        foreach ($request->deleted as $deletedId) {
            $transaction->transactionDetail()->where('id', $deletedId)->delete();
        }
    }
    
    // Memperbarui data transaksi utama
    $transaction->update([
        'transaction_type' => $request->transaction_type,
        'supplier_id' => $request->supplier_id,
        'employee_id' => $request->employee_id,
        'total_amount' => $request->total_amount,
        'updated_by' => Auth::id(), // ID pengguna yang mengupdate
    ]);

    // Memperbarui detail transaksi
    foreach ($request->product_id as $index => $product_id) {
        // Mencari detail transaksi berdasarkan ID (Anda bisa menyesuaikan bagaimana Anda mengambil detail transaksi)
        $transactionDetail = TransactionDetail::find($request->detail_id[$index]); // Detail ID berasal dari inputan hidden `detail_id[]`
        
        if ($transactionDetail) {
            // Update detail transaksi jika ditemukan
            $transactionDetail->update([
                'product_id' => $product_id,
                'quantity' => $request->quantity[$index],
                'unit_price' => $request->unit_price[$index],
                'subtotal' => $request->subtotal[$index],
            ]);
        } else {
            // Jika tidak ada ID, berarti transaksi ini adalah transaksi baru, jadi buat transaksi detail baru
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product_id,
                'quantity' => $request->quantity[$index],
                'unit_price' => $request->unit_price[$index],
                'subtotal' => $request->subtotal[$index],
            ]);
        }
    }

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'transaction' => $transaction,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus transaction
public function destroy(TransactionDetail $transaction)
{
    $transaction_id = $transaction->transaction_id;
    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $transaction->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $transaction->save();  // Simpan perubahan

    //Delete
    $transaction->delete();

    $new = TransactionDetail::where('transaction_id',$transaction_id)->get();

    $totalAmount = 0;

    foreach($new as $d){
        $totalAmount+=$d->subtotal;
    }

    Transaction::where('id',$transaction_id)->update(['total_amount' => $totalAmount]);

    return response()->json([
        'message' => 'Data berhasil dihapus.',
        'icon' => 'success',
        'title' => 'Hapus!',
    ]);
}

    // show
public function show()
{
$transaction = Transaction::whereDoesntHave('transactionDetail.receiptDetail')
    ->orWhereHas('transactionDetail', function ($query) {
        $query->selectRaw('transaction_details.id, transaction_details.quantity, sum(receipt_details.quantity) as total_received')
              ->join('receipt_details', 'transaction_details.id', '=', 'receipt_details.transaction_detail_id')
              ->groupBy('transaction_details.id')
              ->havingRaw('sum(receipt_details.quantity) < transaction_details.quantity');
    })
    ->get();


return response()->json($transaction->map(function ($transactions) {
    return [
        'id' => $transactions->id,
            'text' => $transactions->code  // Ganti 'name' dengan field yang sesuai
        ];
    }));
}
function generateKodeTransaksi() {
    // Ambil bulan dan tahun sekarang
    $bulanTahun = Carbon::now()->format('ymd'); // Contoh: 202304
    
    // Cari transaksi terakhir di bulan yang sama
    $lastTransaction = Transaction::where('code', 'like', 'TRK' . $bulanTahun . '%')
    ->withoutTrashed()
                                ->orderBy('id', 'desc') // Urutkan berdasarkan ID, yang biasanya auto-increment
                                ->first();

    // Tentukan angka urutan
                                $urutan = $lastTransaction ? (intval(substr($lastTransaction->code, -5)) + 1) : 1;

    // Format angka urutan agar selalu 5 digit
                                $urutanFormatted = str_pad($urutan, 3, '0', STR_PAD_LEFT);

    // Buat kode transaksi baru
                                $kodeTransaksi = 'TRK' . $bulanTahun . '' . $urutanFormatted;

                                return $kodeTransaksi;
                            }


                            public function details($id)
                            {
                                $transactionDetail = TransactionDetail::with('transaction','product')->where('transaction_id',$id)->get();
        // Logic to show details of the transaction sale
        // You can load a transaction sale by $id or return a view, etc.

    // Loop untuk menambahkan sum ke masing-masing detail transaksi
    foreach ($transactionDetail as $detail) {
        $detail->receipt_sum = ReceiptDetail::where('transaction_detail_id', $detail->id)
                                            ->sum('quantity'); // Ganti 'amount' sesuai kolom yang diinginkan
    }

                                return response()->json($transactionDetail);
                            }

                        }
