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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->date('order_date');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'cancelled']);
            $table->decimal('total_amount', 10, 2);
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
        Schema::dropIfExists('purchase_orders');
    }
};
