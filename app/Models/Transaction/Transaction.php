<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes
use App\Models\Master\Supplier;
use App\Models\Master\Employee;


class Transaction extends Model
{
    // protected $table = 'categories'; // Nama tabel yang sesuai
    use HasFactory, SoftDeletes;

    protected $fillable = ['code','name', 'transaction_type', 'total_amount', 'supplier_id', 'employee_id', 'created_by', 'updated_by'];


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function transactionDetail()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
