<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes

class Employee extends Model
{
    // protected $table = 'categories'; // Nama tabel yang sesuai
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'position', 'phone', 'email', 'created_by', 'updated_by'];
}
