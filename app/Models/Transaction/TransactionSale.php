<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes
use App\Models\Master\Supplier;
use App\Models\Master\Employee;


class TransactionSale extends Model
{
    // protected $table = 'categories'; // Nama tabel yang sesuai
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'supplier_id','total_amount', 'created_by', 'updated_by'];


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactionSaleDetail()
    {
        return $this->hasMany(TransactionSaleDetail::class);
    }
}
