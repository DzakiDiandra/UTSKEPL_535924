<?php

namespace Database\Factories;

use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $merek = fake()->randomElement(['Samsung', 'Xiaomi', 'Oppo', 'Vivo', 'Realme', 'Apple']);

        return [
            'nama' => $merek.' '.fake()->unique()->numerify('Model ###'),
            'merek' => $merek,
            'deskripsi' => fake()->sentence(12).' '.fake()->sentence(10),
            'harga' => fake()->numberBetween(1_000_000, 10_000_000),
            'stok' => fake()->numberBetween(0, 20),
            'chipset' => fake()->randomElement([
                'MediaTek Helio G85',
                'Qualcomm Snapdragon 680',
                'MediaTek Dimensity 700',
                'Apple A15 Bionic',
            ]),
            'ram_gb' => fake()->randomElement([4, 6, 8]),
            'penyimpanan_gb' => fake()->randomElement([64, 128, 256]),
            'baterai_mah' => fake()->randomElement([4000, 5000]),
            'kamera_mp' => fake()->randomElement([8, 13, 50, 108]),
            'layar_inci' => fake()->randomElement([4.7, 6.1, 6.5, 6.7]),
            'gambar_url' => null,
            'is_aktif' => true,
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_aktif' => false,
        ]);
    }
}
