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
        Schema::table('transactions', function (Blueprint $table) {
            //
            // Menambahkan kolom 'category' pada tabel 'menus'
            $table->string('code')->nullable()->after('id'); // Sesuaikan posisi kolom jika perlu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
            // Menghapus kolom 'category' jika rollback
            $table->dropColumn('code');
        });
    }
};
