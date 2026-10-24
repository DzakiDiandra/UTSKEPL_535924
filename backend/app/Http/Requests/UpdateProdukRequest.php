<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProdukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'merek' => ['sometimes', 'required', 'string', 'max:255'],
            'deskripsi' => ['sometimes', 'required', 'string'],
            'harga' => ['sometimes', 'required', 'integer', 'min:0'],
            'stok' => ['sometimes', 'required', 'integer', 'min:0'],
            'chipset' => ['sometimes', 'required', 'string', 'max:255'],
            'ram_gb' => ['sometimes', 'required', 'integer', 'min:1'],
            'penyimpanan_gb' => ['sometimes', 'required', 'integer', 'min:1'],
            'baterai_mah' => ['sometimes', 'required', 'integer', 'min:1'],
            'kamera_mp' => ['sometimes', 'required', 'integer', 'min:1'],
            'layar_inci' => ['sometimes', 'required', 'numeric'],
            'gambar_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'is_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
