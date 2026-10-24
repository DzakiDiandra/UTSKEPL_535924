<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'merek' => $this->merek,
            'deskripsi' => $this->deskripsi,
            'harga' => $this->harga,
            'stok' => $this->stok,
            'chipset' => $this->chipset,
            'ram_gb' => $this->ram_gb,
            'penyimpanan_gb' => $this->penyimpanan_gb,
            'baterai_mah' => $this->baterai_mah,
            'kamera_mp' => $this->kamera_mp,
            'layar_inci' => (float) $this->layar_inci,
            'gambar_url' => $this->gambar_url,
            'is_aktif' => $this->is_aktif,
            'status_stok' => $this->status_stok,
            'tersedia' => $this->tersedia,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
