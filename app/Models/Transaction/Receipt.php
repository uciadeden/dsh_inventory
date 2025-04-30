<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes
use App\Models\Master\Supplier;
use App\Models\Master\Employee;


class Receipt extends Model
{
    // protected $table = 'categories'; // Nama tabel yang sesuai
    use HasFactory, SoftDeletes;

    protected $fillable = ['code','date', 'transaction_id', 'created_by', 'updated_by'];


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function receiptDetail()
    {
        return $this->hasMany(ReceiptDetail::class);
    }
}
