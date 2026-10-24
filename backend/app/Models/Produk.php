<?php

namespace App\Models;

use Database\Factories\ProdukFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    /** @use HasFactory<ProdukFactory> */
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'nama',
        'merek',
        'deskripsi',
        'harga',
        'stok',
        'chipset',
        'ram_gb',
        'penyimpanan_gb',
        'baterai_mah',
        'kamera_mp',
        'layar_inci',
        'gambar_url',
        'is_aktif',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
            'ram_gb' => 'integer',
            'penyimpanan_gb' => 'integer',
            'baterai_mah' => 'integer',
            'kamera_mp' => 'integer',
            'layar_inci' => 'decimal:1',
            'is_aktif' => 'boolean',
        ];
    }

    /**
     * Status ketersediaan stok untuk tampilan katalog.
     */
    protected function statusStok(): Attribute
    {
        return Attribute::get(fn (): string => match (true) {
            $this->stok === 0 => 'habis',
            $this->stok <= 5 => 'menipis',
            default => 'tersedia',
        });
    }

    /**
     * Apakah produk masih bisa dipesan.
     */
    protected function tersedia(): Attribute
    {
        return Attribute::get(fn (): bool => $this->stok > 0);
    }
}
