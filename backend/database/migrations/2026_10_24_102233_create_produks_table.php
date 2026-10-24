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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('merek');
            $table->text('deskripsi');
            $table->unsignedInteger('harga');
            $table->unsignedInteger('stok');
            $table->string('chipset');
            $table->unsignedInteger('ram_gb');
            $table->unsignedInteger('penyimpanan_gb');
            $table->unsignedInteger('baterai_mah');
            $table->unsignedInteger('kamera_mp');
            $table->decimal('layar_inci', 3, 1);
            $table->string('gambar_url')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->index('merek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
