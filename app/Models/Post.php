<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'user_id', 'created_by', 'updated_by'];

    // Relasi ke User (User yang membuat post)
    public function user()
    {
        return $this->belongsTo(User::class);
}

    // Relasi dengan model User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi dengan model User
    public function updater()
    {
        return $this->belongsTo(User::class, 'update_by');
    }

}