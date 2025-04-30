<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes

class Product extends Model
{
    // protected $table = 'categories'; // Nama tabel yang sesuai
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'category_id', 'supplier_id', 'quantity_in_stock', 'price', 'created_by', 'updated_by'];


    // Relasi ke model Category (Satu Product hanya memiliki satu Category)
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }
}
