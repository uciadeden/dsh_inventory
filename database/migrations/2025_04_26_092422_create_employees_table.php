<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('phone');
            $table->string('email')->nullable();
            // Menyimpan pengguna yang membuat post
                  $table->unsignedBigInteger('created_by')->nullable();  // Menambahkan kolom created_by
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');  // Menambahkan foreign key
            $table->timestamps();
            // Menyimpan pengguna yang membuat post
                  $table->unsignedBigInteger('updated_by')->nullable();  // Menambahkan kolom updated_by
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');  // Menambahkan foreign key
            $table->softDeletes();  // Menambahkan kolom 'deleted_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
