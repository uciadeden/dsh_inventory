<?php
// app/Http/Controllers/ReceiptController.php
namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Receipt;
use App\Models\Transaction\ReceiptDetail;
use App\Models\Master\Product;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Carbon\Carbon;

class ReceiptController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create receipts')->only(['create', 'store']);
        $this->middleware('check.permission:edit receipts')->only(['edit', 'update']);
        $this->middleware('check.permission:delete receipts')->only(['destroy']);
        $this->middleware('check.permission:view receipts')->only(['index']);
    }

    // Menampilkan daftar transaction
    public function index(Request $request)
    {
        $title="receipts";
        $titleShow="Penerimaan";
        $receipt = ReceiptDetail::all(); // Bisa diganti dengan query untuk menampilkan transaction tertentu

        if ($request->ajax()) {
        $receipt = ReceiptDetail::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($receipt)
        ->addColumn('supplier_name', function($receipt) {
                // Akses nama kategori melalui relasi
            return $receipt->receipt->transaction->supplier ? $receipt->receipt->transaction->supplier->name: '';
        })->addColumn('product_name', function($receipt) {
                // Akses nama kategori melalui relasi
            return $receipt->product ? $receipt->product->name : '';
        })->addColumn('transactionCode', function($receipt) {
                // Akses nama kategori melalui relasi
            return $receipt->receipt->transaction ? $receipt->receipt->transaction->code : 'No Category';
        })->addColumn('code', function($receipt) {
                // Akses nama kategori melalui relasi
            return $receipt->receipt ? $receipt->receipt->code : 'No Category';
        })->addColumn('date', function($receipt) {
                // Akses nama kategori melalui relasi
            return $receipt->receipt->date ? date('d-m-Y',strtotime($receipt->receipt->date)) : '';
        })->addColumn('action', function($row) use($title) {
            $editUrl = route('receipts.edit', $row->id);
            $deleteUrl = route('receipts.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('transaction.receipts.index', compact('receipt','title','titleShow'));
}

    // Menampilkan form untuk membuat transaction baru
public function create()
{
    return view('receipt.create');
}

    // Menyimpan transaction baru
public function store(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'transaction_id' => 'nullable|integer',
        'product_id' => 'required|array',
        'quantity' => 'required|array',
        'detail_id' => 'required|array',
    ]);
 // Mulai transaksi dengan DB Receipt untuk memastikan atomicity
        DB::transaction(function () use ($request) {
            // Membuat transaksi utama
            $receipt = Receipt::create([
                'date' => $request->date,
                'transaction_id' => $request->transaction_id,
                'code' => $this->generateKodeTransaksi(),
                'created_by' => Auth::id(),
            ]);

            // Menyimpan detail transaksi
            $detail_id = $request->input('detail_id');
            $products = $request->input('product_id');
            $quantities = $request->input('quantity');

            // Loop untuk menyimpan setiap detail produk
            foreach ($products as $index => $productId) {
                if($quantities[$index] != 0){
                    $receipt->receiptDetail()->create([
                        'product_id' => $productId,
                        'transaction_detail_id' => $detail_id[$index],
                        'quantity' => $quantities[$index],
                    ]);
                                    // Update quantity_in_stock pada produk yang relevan
                    $product = Product::find($productId);
                    if ($product) {
                    $product->quantity_in_stock += $quantities[$index]; // Menambahkan quantity yang diterima
                    $product->save(); // Simpan perubahan
                }
                }
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

public function edit(ReceiptDetail $receipt)
{
    // Mengambil data Receipt dengan eager loading pada relasi yang dibutuhkan
    $receiptData = Receipt::with([
        'transaction',
        'receiptDetail.product',
        'receiptDetail.transactionDetail'
    ])
    ->where('id', $receipt->receipt_id)
    ->first();

    // Menambahkan total_received untuk setiap receiptDetail
    $receiptData->receiptDetail = $receiptData->receiptDetail->map(function ($detail) {
        // Menghitung total received_quantity berdasarkan transaction_detail_id
        $totalReceived = $detail->where('transaction_detail_id', $detail->transaction_detail_id)
                                ->sum('quantity');

        // Menambahkan total_received ke dalam data detail
        $detail->receipt_sum = $totalReceived;

        return $detail;
    });

    // Mengembalikan data dengan total penerimaan
    return response()->json($receiptData);
}

    // Menyimpan perubahan transaction
public function update(Request $request, Receipt $receipt)
{
    $request->validate([
        'transaction_id' => 'nullable|integer',
        'product_id' => 'required|array', // Produk yang dipilih
        'date' => 'required|date', // Produk yang dipilih
        'quantity' => 'required|array', // Kuantitas yang dimasukkan
        'deleted' => 'nullable|array', // Tambahkan validasi untuk deleted[] jika ada
    ]);

    // Memperbarui data transaksi utama
    $receipt->update([
        'transaction_id' => $request->transaction_id,
        'date' => $request->date,
        'updated_by' => Auth::id(), // ID pengguna yang mengupdate
    ]);

    // Mengupdate detail transaksi
    foreach ($request->detail_id as $index => $detail_id) {
        // Mencari detail transaksi berdasarkan ID (Anda bisa menyesuaikan bagaimana Anda mengambil detail transaksi)
        $transactionDetail = ReceiptDetail::find($request->detail_id[$index]); // Detail ID berasal dari inputan hidden `detail_id[]`

        if ($transactionDetail) {
            // Simpan perubahan jumlah stok sebelumnya (jika ada)
            $oldQuantity = $transactionDetail->quantity;
            $product = $transactionDetail->product;

            // Update detail transaksi jika ditemukan
            $transactionDetail->update([
                'product_id' => $request->product_id[$index],
                'quantity' => $request->quantity[$index],
                'updated_by' => Auth::id(), // ID pengguna yang mengupdate
            ]);

            // Update quantity_in_stock pada produk
            if ($product) {
                // Kurangi quantity yang lama
                $product->quantity_in_stock -= $oldQuantity;
                // Tambahkan quantity yang baru
                $product->quantity_in_stock += $request->quantity[$index];
                $product->save(); // Simpan perubahan
            }
        }
    }

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'receipt' => $receipt,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus transaction
public function destroy(ReceiptDetail $receipt)
{
    $oldQuantity = $receipt->quantity;
    $product = $receipt->product;
                // Kurangi quantity yang lama
    $product->quantity_in_stock -= $oldQuantity;
    
                $product->save(); // Simpan perubahan

    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $receipt->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $receipt->save();  // Simpan perubahan

    //Delete
    $receipt->delete();

    return response()->json([
        'message' => 'Data berhasil dihapus.',
        'icon' => 'success',
        'title' => 'Hapus!',
    ]);
}

    // show
public function show()
{
    $transaction = Receipt::select('id','name')->get();
    
    return response()->json($transaction->map(function ($receipt) {
        return [
            'id' => $receipt->id,
            'text' => $receipt->name  // Ganti 'name' dengan field yang sesuai
        ];
    }));
}
function generateKodeTransaksi() {
    // Ambil bulan dan tahun sekarang
    $bulanTahun = Carbon::now()->format('ymd'); // Contoh: 202304
    
    // Cari transaksi terakhir di bulan yang sama
    $lastReceipt = Receipt::where('code', 'like', 'PNR' . $bulanTahun . '%')
                                ->withoutTrashed()
                                ->orderBy('id', 'desc') // Urutkan berdasarkan ID, yang biasanya auto-increment
                                ->first();

    // Tentukan angka urutan
    $urutan = $lastReceipt ? (intval(substr($lastReceipt->code, -3)) + 1) : 1;

    // Format angka urutan agar selalu 5 digit
    $urutanFormatted = str_pad($urutan, 3, '0', STR_PAD_LEFT);

    // Buat kode transaksi baru
    $kodeTransaksi = 'PNR' . $bulanTahun . '' . $urutanFormatted;

    return $kodeTransaksi;
}

}
