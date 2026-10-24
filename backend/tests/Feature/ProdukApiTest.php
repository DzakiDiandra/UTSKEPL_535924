<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProdukApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_publik_menampilkan_semua_produk_aktif(): void
    {
        Produk::factory()->create(['nama' => 'Produk Aktif Satu']);
        Produk::factory()->create(['nama' => 'Produk Aktif Dua']);
        Produk::factory()->nonaktif()->create(['nama' => 'Produk Nonaktif']);

        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['nama' => 'Produk Nonaktif']);
    }

    public function test_show_publik_mengembalikan_detail_produk(): void
    {
        $produk = Produk::factory()->create([
            'nama' => 'Samsung Galaxy Uji',
            'merek' => 'Samsung',
            'stok' => 3,
        ]);

        $this->getJson("/api/produk/{$produk->id}")
            ->assertOk()
            ->assertJsonPath('data.nama', 'Samsung Galaxy Uji')
            ->assertJsonPath('data.status_stok', 'menipis')
            ->assertJsonPath('data.tersedia', true)
            ->assertJsonStructure([
                'data' => [
                    'id',
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
                    'status_stok',
                    'tersedia',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_produk_nonaktif_tidak_tampil_di_publik(): void
    {
        $produk = Produk::factory()->nonaktif()->create();

        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson("/api/produk/{$produk->id}")
            ->assertNotFound();
    }

    public function test_daftar_produk_dapat_difilter_berdasarkan_merek(): void
    {
        Produk::factory()->create(['merek' => 'Samsung']);
        Produk::factory()->create(['merek' => 'Xiaomi']);
        Produk::factory()->nonaktif()->create(['merek' => 'Samsung']);

        $this->getJson('/api/produk?merek=Samsung')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.merek', 'Samsung');
    }

    public function test_daftar_produk_dapat_dicari_berdasarkan_nama_atau_merek(): void
    {
        Produk::factory()->create([
            'nama' => 'Samsung Galaxy A15',
            'merek' => 'Samsung',
        ]);
        Produk::factory()->create([
            'nama' => 'Xiaomi Redmi Note 13',
            'merek' => 'Xiaomi',
        ]);

        $this->getJson('/api/produk?q=galaxy')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Samsung Galaxy A15');

        $this->getJson('/api/produk?q=Xiaomi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.merek', 'Xiaomi');
    }

    public function test_daftar_produk_dapat_diurutkan_berdasarkan_harga(): void
    {
        Produk::factory()->create(['nama' => 'Termurah', 'harga' => 1000000]);
        Produk::factory()->create(['nama' => 'Menengah', 'harga' => 2000000]);
        Produk::factory()->create(['nama' => 'Termahal', 'harga' => 3000000]);

        $this->getJson('/api/produk?urut=harga_asc')
            ->assertOk()
            ->assertJsonPath('data.0.harga', 1000000)
            ->assertJsonPath('data.1.harga', 2000000)
            ->assertJsonPath('data.2.harga', 3000000);

        $this->getJson('/api/produk?urut=harga_desc')
            ->assertOk()
            ->assertJsonPath('data.0.harga', 3000000)
            ->assertJsonPath('data.1.harga', 2000000)
            ->assertJsonPath('data.2.harga', 1000000);
    }

    public function test_daftar_merek_hanya_berisi_merek_unik_dari_produk_aktif(): void
    {
        Produk::factory()->create(['merek' => 'Samsung']);
        Produk::factory()->create(['merek' => 'Samsung']);
        Produk::factory()->create(['merek' => 'Xiaomi']);
        Produk::factory()->nonaktif()->create(['merek' => 'Nokia']);

        $this->getJson('/api/merek')
            ->assertOk()
            ->assertExactJson([
                'data' => ['Samsung', 'Xiaomi'],
            ]);
    }

    public function test_store_produk_tanpa_login_menghasilkan_401(): void
    {
        $this->postJson('/api/produk', $this->dataProduk())
            ->assertUnauthorized();
    }

    public function test_store_produk_oleh_non_admin_menghasilkan_403(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => false]));

        $this->postJson('/api/produk', $this->dataProduk())
            ->assertForbidden();
    }

    public function test_store_produk_oleh_admin_menghasilkan_201(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));

        $nama = 'Samsung Galaxy Baru';

        $this->postJson('/api/produk', $this->dataProduk(['nama' => $nama]))
            ->assertCreated()
            ->assertJsonPath('data.nama', $nama)
            ->assertJsonPath('data.status_stok', 'tersedia');

        $this->assertDatabaseHas('produk', [
            'nama' => $nama,
            'merek' => 'Samsung',
        ]);
    }

    public function test_store_produk_menghasilkan_422_untuk_harga_negatif_dan_nama_kosong(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));

        $this->postJson('/api/produk', $this->dataProduk(['harga' => -1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('harga');

        $this->postJson('/api/produk', $this->dataProduk(['nama' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nama');
    }

    public function test_admin_dapat_memperbarui_produk(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $produk = Produk::factory()->create(['stok' => 10]);

        $this->putJson("/api/produk/{$produk->id}", [
            'harga' => 1750000,
            'stok' => 0,
            'is_aktif' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.harga', 1750000)
            ->assertJsonPath('data.status_stok', 'habis')
            ->assertJsonPath('data.tersedia', false)
            ->assertJsonPath('data.is_aktif', false);

        $this->assertDatabaseHas('produk', [
            'id' => $produk->id,
            'harga' => 1750000,
            'stok' => 0,
            'is_aktif' => false,
        ]);
    }

    public function test_admin_dapat_menghapus_produk(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        $produk = Produk::factory()->create();

        $this->deleteJson("/api/produk/{$produk->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('produk', [
            'id' => $produk->id,
        ]);
    }

    public function test_status_stok_menghasilkan_habis_menipis_dan_tersedia(): void
    {
        $habis = Produk::factory()->create(['stok' => 0]);
        $menipis = Produk::factory()->create(['stok' => 5]);
        $tersedia = Produk::factory()->create(['stok' => 6]);

        $this->assertSame('habis', $habis->status_stok);
        $this->assertSame('menipis', $menipis->status_stok);
        $this->assertSame('tersedia', $tersedia->status_stok);

        $this->assertFalse($habis->tersedia);
        $this->assertTrue($menipis->tersedia);
        $this->assertTrue($tersedia->tersedia);
    }

    public function test_admin_mendapat_semua_produk_termasuk_yang_nonaktif(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));
        Produk::factory()->create(['nama' => 'Produk Aktif']);
        Produk::factory()->nonaktif()->create(['nama' => 'Produk Nonaktif']);

        $this->getJson('/api/admin/produk')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['nama' => 'Produk Nonaktif']);
    }

    public function test_non_admin_tidak_dapat_mengakses_daftar_admin(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => false]));

        $this->getJson('/api/admin/produk')
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function dataProduk(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Samsung Galaxy Uji',
            'merek' => 'Samsung',
            'deskripsi' => 'Ponsel untuk kebutuhan pengujian API toko online.',
            'harga' => 2500000,
            'stok' => 8,
            'chipset' => 'MediaTek Helio G99',
            'ram_gb' => 8,
            'penyimpanan_gb' => 256,
            'baterai_mah' => 5000,
            'kamera_mp' => 50,
            'layar_inci' => 6.5,
            'gambar_url' => null,
            'is_aktif' => true,
        ], $overrides);
    }
}
