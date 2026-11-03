<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 30)->unique();
            $table->string('nama', 150)->index();
            $table->foreignId('category_id')->index()->constrained('categories')->restrictOnDelete();
            $table->foreignId('unit_id')->index()->constrained('units')->restrictOnDelete();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->string('lokasi_rak', 50)->nullable();
            $table->string('foto', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['stok', 'stok_minimum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
