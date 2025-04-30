<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\Product;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes

class ReceiptDetail extends Model
{
    use HasFactory, SoftDeletes;

    // Kolom yang bisa diisi
    protected $fillable = [
        'receipt_id',
        'transaction_detail_id',
        'product_id',
        'quantity',
        'created_by',
        'updated_by'
    ];

    /**
     * Relasi balik ke transaksi
     */
    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    /**
     * Relasi balik ke transaksi
     */
    public function transactionDetail()
    {
        return $this->belongsTo(TransactionDetail::class);
    }


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
        // Accessor for calculating total received quantity by transaction_detail_id
    public function scopeTotalReceivedQuantity($query, $transactionDetailId)
    {
        return $query->where('transaction_detail_id', $transactionDetailId)
                     ->sum('quantity');
    }
}
