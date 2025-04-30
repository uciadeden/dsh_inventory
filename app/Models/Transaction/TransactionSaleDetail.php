<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\Product;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes

class TransactionSaleDetail extends Model
{
    use HasFactory, SoftDeletes;

    // Kolom yang bisa diisi
    protected $fillable = [
        'transaction_sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'created_by',
        'updated_by'
    ];

    /**
     * Relasi balik ke transaksi
     */
    public function transactionSale()
    {
        return $this->belongsTo(TransactionSale::class);
    }


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
