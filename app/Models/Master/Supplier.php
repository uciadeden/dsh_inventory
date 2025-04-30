<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes

class Supplier extends Model
{
    // protected $table = 'categories'; // Nama tabel yang sesuai
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'contact_name', 'contact_phone', 'address', 'email', 'created_by', 'updated_by'];
}
