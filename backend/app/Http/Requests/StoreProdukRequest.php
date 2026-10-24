<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProdukRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:255'],
            'merek' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'chipset' => ['required', 'string', 'max:255'],
            'ram_gb' => ['required', 'integer', 'min:1'],
            'penyimpanan_gb' => ['required', 'integer', 'min:1'],
            'baterai_mah' => ['required', 'integer', 'min:1'],
            'kamera_mp' => ['required', 'integer', 'min:1'],
            'layar_inci' => ['required', 'numeric'],
            'gambar_url' => ['nullable', 'url', 'max:2048'],
            'is_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
