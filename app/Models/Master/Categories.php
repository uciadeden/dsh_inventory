<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes

class Categories extends Model
{
    // protected $table = 'categories'; // Nama tabel yang sesuai
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'created_by', 'updated_by'];
    
    // Relasi ke model Product (Satu Category memiliki banyak Product)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
