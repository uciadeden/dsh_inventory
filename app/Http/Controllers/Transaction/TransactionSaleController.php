<?php
// app/Http/Controllers/TransactionSaleController.php
namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\TransactionSale;
use App\Models\Transaction\TransactionSaleDetail;
use App\Models\Master\Product;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Carbon\Carbon;

class TransactionSaleController extends Controller
{

    public function __construct()
    {
        // Menggunakan middleware 'check.permission' dengan permission yang sesuai
        $this->middleware('check.permission:create transaction-sales')->only(['create', 'store']);
        $this->middleware('check.permission:edit transaction-sales')->only(['edit', 'update']);
        $this->middleware('check.permission:delete transaction-sales')->only(['destroy']);
        $this->middleware('check.permission:view transaction-sales')->only(['index']);
    }

    // Menampilkan daftar transactionsale
    public function index(Request $request)
    {
        $title="transaction-sales";
        $titleShow="Penjualan";
        $transactionsales = TransactionSaleDetail::all(); // Bisa diganti dengan query untuk menampilkan transactionsale tertentu

        if ($request->ajax()) {
        $transactionsales = TransactionSaleDetail::all(); // Atau gunakan query builder jika Anda membutuhkan data yang lebih spesifik
        return DataTables::of($transactionsales)
        ->addColumn('supplier_name', function($transactionsales) {
                // Akses nama kategori melalui relasi
            return $transactionsales->transactionsale->supplier ? $transactionsales->transactionsale->supplier->name : '';
        })->addColumn('product_name', function($transactionsales) {
                // Akses nama kategori melalui relasi
            return $transactionsales->product ? $transactionsales->product->name : '';
        })->addColumn('code', function($transactionsales) {
                // Akses nama kategori melalui relasi
            return $transactionsales->transactionsale ? $transactionsales->transactionsale->code : 'No Category';
        })->addColumn('action', function($row) use($title) {
            $editUrl = route('transaction-sales.edit', $row->id);
            $deleteUrl = route('transaction-sales.destroy', $row->id);
            return view('components.actions', compact('row', 'editUrl', 'deleteUrl', 'title'));
        })
        ->make(true);
    }
    return view('transaction.transaction-sales.index', compact('transactionsales','title','titleShow'));
}

    // Menampilkan form untuk membuat transactionsale baru
public function create()
{
    return view('transactionsales.create');
}

    // Menyimpan transactionsale baru
public function store(Request $request)
{
    $request->validate([
        'supplier_id' => 'nullable|integer',
        'total_amount' => 'required|integer',
        'product_id' => 'required|array',
        'quantity' => 'required|array',
        'unit_price' => 'required|array',
        'subtotal' => 'required|array',
    ]);
 // Mulai transaksi dengan DB TransactionSale untuk memastikan atomicity
        DB::transaction(function () use ($request) {
            // Membuat transaksi utama
            $transactionsale = TransactionSale::create([
                'supplier_id' => $request->supplier_id,
                'code' => $this->generateKodeTransaksi(),
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
                $transactionsale->transactionSaleDetail()->create([
                    'product_id' => $productId,
                    'quantity' => $quantities[$index],
                    'unit_price' => $unitPrices[$index],
                    'subtotal' => $subtotals[$index],
                ]);
                                    // Update quantity_in_stock pada produk yang relevan
                $product = Product::find($productId);
                if ($product) {
                    $product->quantity_in_stock -= $quantities[$index]; // Menambahkan quantity yang diterima
                    $product->save(); // Simpan perubahan
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

    // Menampilkan form untuk mengedit transactionsale

public function edit(TransactionSaleDetail $transaction_sale)
{
    $transaction_sale = TransactionSale::with('transactionSaleDetail','transactionSaleDetail.product','supplier')->where('id',$transaction_sale->transaction_sale_id)->first();

    return response()->json($transaction_sale);
}

    // Menyimpan perubahan transactionsale
public function update(Request $request, TransactionSale $transaction_sale)
{
    $request->validate([
        'supplier_id' => 'nullable|integer',
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
            $transaction_sale->transactionsaleDetail()->where('id', $deletedId)->delete();
        }
    }
    
    // Memperbarui data transaksi utama
    $transaction_sale->update([
        'supplier_id' => $request->supplier_id,
        'total_amount' => $request->total_amount,
        'updated_by' => Auth::id(), // ID pengguna yang mengupdate
    ]);

    // Memperbarui detail transaksi
    foreach ($request->product_id as $index => $product_id) {
        // Mencari detail transaksi berdasarkan ID (Anda bisa menyesuaikan bagaimana Anda mengambil detail transaksi)
        $transactionsaleDetail = TransactionSaleDetail::find($request->detail_id[$index]); // Detail ID berasal dari inputan hidden `detail_id[]`
        
        if ($transactionsaleDetail) {
            $productOld = Product::find($transactionsaleDetail->product_id);
            if ($productOld) {
                    $productOld->quantity_in_stock += $transactionsaleDetail->quantity; // Menambahkan quantity yang diterima
                    $productOld->save(); // Simpan perubahan
                }

            $product = Product::find($product_id);
            if ($product) {
                    $product->quantity_in_stock -= $request->quantity[$index]; // Menambahkan quantity yang diterima
                    $product->save(); // Simpan perubahan
                }
            // Update detail transaksi jika ditemukan
            $transactionsaleDetail->update([
                'product_id' => $product_id,
                'quantity' => $request->quantity[$index],
                'unit_price' => $request->unit_price[$index],
                'subtotal' => $request->subtotal[$index],
            ]);
        } else {
            // Jika tidak ada ID, berarti transaksi ini adalah transaksi baru, jadi buat transaksi detail baru
            TransactionSaleDetail::create([
                'transaction_sale_id' => $transaction_sale->id,
                'product_id' => $product_id,
                'quantity' => $request->quantity[$index],
                'unit_price' => $request->unit_price[$index],
                'subtotal' => $request->subtotal[$index],
            ]);

                                    // Update quantity_in_stock pada produk yang relevan
            $product = Product::find($product_id);
            if ($product) {
                    $product->quantity_in_stock -= $quantities[$index]; // Menambahkan quantity yang diterima
                    $product->save(); // Simpan perubahan
                }
        }
    }

    return response()->json([
        'message' => 'Data berhasil diperbaharui.', 
        'transaction_sale' => $transaction_sale,
        'icon' => 'success',
        'title' => 'Berhasil',
    ]);
}

    // Menghapus transactionsale
public function destroy(TransactionSaleDetail $transaction_sale)
{
    $transaction_sale_id = $transaction_sale->transaction_sale_id;



    $product = Product::find($transaction_sale->product_id);
    if ($product) {
                    $product->quantity_in_stock += $transaction_sale->quantity; // Menambahkan quantity yang diterima
                    $product->save(); // Simpan perubahan
                }

    // Perbarui kolom updated_by dengan ID pengguna yang sedang login
    $transaction_sale->updated_by = Auth::id();  // Menyimpan ID pengguna yang menghapus
    $transaction_sale->save();  // Simpan perubahan

    //Delete
    $transaction_sale->delete();

    $new = TransactionSaleDetail::where('transaction_sale_id',$transaction_sale_id)->get();

    $totalAmount = 0;

    foreach($new as $d){
        $totalAmount+=$d->subtotal;
    }

    TransactionSale::where('id',$transaction_sale_id)->update(['total_amount' => $totalAmount]);

    return response()->json([
        'message' => 'Data berhasil dihapus.',
        'icon' => 'success',
        'title' => 'Hapus!',
    ]);
}

    // show
public function show()
{
    $transactionsale = TransactionSale::whereHas('transactionSaleDetail')->select('id','code')->get();
    
    return response()->json($transactionsale->map(function ($transactionsales) {
        return [
            'id' => $transactionsales->id,
            'text' => $transactionsales->code  // Ganti 'name' dengan field yang sesuai
        ];
    }));
}
function generateKodeTransaksi() {
    // Ambil bulan dan tahun sekarang
    $bulanTahun = Carbon::now()->format('ymd'); // Contoh: 202304
    
    // Cari transaksi terakhir di bulan yang sama
    $lastTransactionSale = TransactionSale::where('code', 'like', 'TRJ' . $bulanTahun . '%')
                                ->withoutTrashed()
                                ->orderBy('id', 'desc') // Urutkan berdasarkan ID, yang biasanya auto-increment
                                ->first();

    // Tentukan angka urutan
    $urutan = $lastTransactionSale ? (intval(substr($lastTransactionSale->code, -3)) + 1) : 1;

    // Format angka urutan agar selalu 5 digit
    $urutanFormatted = str_pad($urutan, 3, '0', STR_PAD_LEFT);

    // Buat kode transaksi baru
    $kodeTransaksi = 'TRJ' . $bulanTahun . '' . $urutanFormatted;

    return $kodeTransaksi;
}


    public function details($id)
    {
        $transactionSaleDetail = TransactionSaleDetail::with('transactionSale','product')->where('transaction_sale_id',$id)->get();

        // Logic to show details of the transaction sale
        // You can load a transaction sale by $id or return a view, etc.
        return response()->json($transactionSaleDetail);
    }

}
