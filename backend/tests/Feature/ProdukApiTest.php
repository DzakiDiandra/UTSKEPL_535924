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

    public function test_semua_endpoint_get_tanpa_token_menghasilkan_401(): void
    {
        $produk = Produk::factory()->create();

        foreach (['/api/produk', "/api/produk/{$produk->id}", '/api/merek', '/api/me', '/api/admin/produk'] as $url) {
            $this->getJson($url)->assertUnauthorized();
        }
    }

    public function test_tanpa_header_accept_tetap_menghasilkan_json_401(): void
    {
        $this->get('/api/produk')
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_user_biasa_dapat_melihat_katalog(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => false]));
        $produk = Produk::factory()->create(['nama' => 'Produk Untuk Pelanggan']);

        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonFragment(['nama' => 'Produk Untuk Pelanggan']);

        $this->getJson("/api/produk/{$produk->id}")
            ->assertOk()
            ->assertJsonPath('data.nama', 'Produk Untuk Pelanggan');

        $this->getJson('/api/merek')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.is_admin', false);
    }

    public function test_user_biasa_tidak_boleh_menulis_produk(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => false]));
        $produk = Produk::factory()->create();

        $this->postJson('/api/produk', $this->dataProduk())->assertForbidden();
        $this->putJson("/api/produk/{$produk->id}", ['harga' => 1750000])->assertForbidden();
        $this->deleteJson("/api/produk/{$produk->id}")->assertForbidden();
        $this->getJson('/api/admin/produk')->assertForbidden();
    }

    public function test_admin_dapat_menambah_produk_dengan_status_201(): void
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

    public function test_admin_mendapat_422_untuk_harga_negatif_dan_nama_kosong(): void
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

    public function test_daftar_produk_dapat_difilter_berdasarkan_merek(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Produk::factory()->create(['merek' => 'Samsung']);
        Produk::factory()->create(['merek' => 'Xiaomi']);
        Produk::factory()->nonaktif()->create(['merek' => 'Samsung']);

        $this->getJson('/api/produk?merek=Samsung')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.merek', 'Samsung');
    }

    public function test_daftar_produk_dapat_dicari_dan_diurutkan(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Produk::factory()->create([
            'nama' => 'Samsung Galaxy A15',
            'merek' => 'Samsung',
            'harga' => 2000000,
        ]);
        Produk::factory()->create([
            'nama' => 'Xiaomi Redmi Note 13',
            'merek' => 'Xiaomi',
            'harga' => 3000000,
        ]);

        $this->getJson('/api/produk?q=galaxy')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Samsung Galaxy A15');

        $this->getJson('/api/produk?urut=harga_asc')
            ->assertOk()
            ->assertJsonPath('data.0.harga', 2000000)
            ->assertJsonPath('data.1.harga', 3000000);
    }

    public function test_daftar_merek_hanya_berisi_merek_unik_dari_produk_aktif(): void
    {
        Sanctum::actingAs(User::factory()->create());
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

    public function test_produk_nonaktif_tidak_tampil_di_publik(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $produk = Produk::factory()->nonaktif()->create();

        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson("/api/produk/{$produk->id}")
            ->assertNotFound();
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

    public function test_status_stok_menghasilkan_habis_menipis_dan_tersedia(): void
    {
        $habis = Produk::factory()->create(['stok' => 0]);
        $menipis = Produk::factory()->create(['stok' => 5]);
        $tersedia = Produk::factory()->create(['stok' => 6]);

        $this->assertSame('habis', $habis->status_stok);
        $this->assertSame('menipis', $menipis->status_stok);
        $this->assertSame('tersedia', $tersedia->status_stok);
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
