<script setup>
import { computed, onMounted, ref } from 'vue'
import {
  Eye,
  EyeOff,
  Package,
  Pencil,
  Plus,
  RefreshCw,
  Search,
  Trash2,
  TriangleAlert,
  X,
} from 'lucide-vue-next'
import api from '@/api'
import { formatRupiah } from '@/utils/format'
import { colorwayMerek, urlGambarAman } from '@/utils/produk'
import AdminLayout from '@/components/AdminLayout.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import PhoneVisual from '@/components/PhoneVisual.vue'
import StokBadge from '@/components/StokBadge.vue'
import { useToast } from '@/composables/useToast'

const { tampilkanToast } = useToast()

const semuaProduk = ref([])
const pencarian = ref('')
const memuat = ref(true)
const galat = ref('')
const dialogTerbuka = ref(false)
const kandidatHapus = ref(null)
const menghapus = ref(false)

const terfilter = computed(() => {
  const kunci = pencarian.value.trim().toLowerCase()

  if (!kunci) return semuaProduk.value

  return semuaProduk.value.filter(
    (item) =>
      item.nama.toLowerCase().includes(kunci) || item.merek.toLowerCase().includes(kunci),
  )
})

const jumlahTampil = computed(() => semuaProduk.value.filter((item) => item.is_aktif).length)
const jumlahHabis = computed(() => semuaProduk.value.filter((item) => item.stok === 0).length)

async function muatProduk() {
  memuat.value = true
  galat.value = ''

  try {
    const { data } = await api.get('/admin/produk')
    semuaProduk.value = data.data ?? []
  } catch {
    semuaProduk.value = []
    galat.value = 'Daftar produk belum bisa dimuat. Periksa koneksi lalu coba lagi.'
  } finally {
    memuat.value = false
  }
}

function sembunyikanGambar(event) {
  event.currentTarget.remove()
}

function bukaHapus(item) {
  kandidatHapus.value = item
  dialogTerbuka.value = true
}

function batalHapus() {
  if (menghapus.value) return

  dialogTerbuka.value = false
  kandidatHapus.value = null
}

async function konfirmasiHapus() {
  if (!kandidatHapus.value) return

  menghapus.value = true

  try {
    await api.delete(`/produk/${kandidatHapus.value.id}`)
    semuaProduk.value = semuaProduk.value.filter((item) => item.id !== kandidatHapus.value.id)
    tampilkanToast(`Produk ${kandidatHapus.value.nama} sudah dihapus.`, 'success')
    dialogTerbuka.value = false
    kandidatHapus.value = null
  } catch {
    tampilkanToast('Produk belum bisa dihapus. Silakan coba lagi.', 'danger')
  } finally {
    menghapus.value = false
  }
}

onMounted(muatProduk)
</script>

<template>
  <AdminLayout>
    <header class="page-head">
      <div>
        <p class="eyebrow">Panel admin / Produk</p>
        <h1>Daftar produk</h1>
        <p class="lede">Kelola katalog, stok, dan status tampil produk.</p>
      </div>
      <RouterLink class="btn btn--primary" :to="{ name: 'admin-produk-baru' }">
        <Plus :size="18" />
        Tambah produk
      </RouterLink>
    </header>

    <div class="ringkasan-admin" aria-label="Ringkasan produk">
      <div>
        <span>Total produk</span>
        <strong>{{ memuat ? '—' : semuaProduk.length }}</strong>
      </div>
      <div>
        <span>Tampil di toko</span>
        <strong>{{ memuat ? '—' : jumlahTampil }}</strong>
      </div>
      <div>
        <span>Stok habis</span>
        <strong>{{ memuat ? '—' : jumlahHabis }}</strong>
      </div>
    </div>

    <div class="toolbar-admin">
      <div class="cari-admin">
        <Search class="cari-admin__ikon" :size="18" aria-hidden="true" />
        <label class="visually-hidden" for="cari-admin">Cari produk</label>
        <input
          id="cari-admin"
          v-model="pencarian"
          class="input"
          type="search"
          placeholder="Cari nama atau merek"
          autocomplete="off"
        />
        <button
          v-if="pencarian"
          class="cari-admin__hapus"
          type="button"
          aria-label="Hapus pencarian"
          @click="pencarian = ''"
        >
          <X :size="16" />
        </button>
      </div>
      <p class="toolbar-admin__jumlah" aria-live="polite">
        <template v-if="memuat">Memuat produk…</template>
        <template v-else>{{ terfilter.length }} dari {{ semuaProduk.length }} produk</template>
      </p>
    </div>

    <div v-if="memuat" class="table-wrap table-loading" aria-busy="true">
      <span class="visually-hidden">Memuat daftar produk…</span>
      <div v-for="nomor in 6" :key="nomor" class="baris-skeleton">
        <div class="skeleton skeleton-bulat"></div>
        <div class="skeleton skeleton-line"></div>
        <div class="skeleton skeleton-line skeleton-line--short"></div>
        <div class="skeleton skeleton-line skeleton-line--short"></div>
      </div>
    </div>

    <div v-else-if="galat" class="state-panel state-panel--error" role="alert">
      <span class="state-panel__icon" aria-hidden="true"><TriangleAlert :size="22" /></span>
      <div class="state-panel__body">
        <h2>Daftar belum bisa dibuka</h2>
        <p>{{ galat }}</p>
      </div>
      <button class="btn btn--secondary" type="button" @click="muatProduk">
        <RefreshCw :size="16" />
        Coba lagi
      </button>
    </div>

    <div v-else-if="!terfilter.length" class="state-panel">
      <span class="state-panel__icon" aria-hidden="true"><Package :size="22" /></span>
      <div class="state-panel__body">
        <h2>{{ pencarian ? 'Produk tidak ditemukan' : 'Belum ada produk' }}</h2>
        <p v-if="pencarian">
          Tidak ada produk yang cocok dengan “{{ pencarian }}”. Coba kata kunci lain.
        </p>
        <p v-else>Tambahkan produk pertama untuk mulai mengisi katalog toko.</p>
      </div>
      <button v-if="pencarian" class="btn btn--secondary" type="button" @click="pencarian = ''">
        Hapus pencarian
      </button>
      <RouterLink v-else class="btn btn--primary" :to="{ name: 'admin-produk-baru' }">
        <Plus :size="17" />
        Tambah produk
      </RouterLink>
    </div>

    <div v-else class="table-wrap">
      <table class="table-admin">
        <caption class="visually-hidden">
          Daftar seluruh produk di panel admin
        </caption>
        <thead>
          <tr>
            <th scope="col">Produk</th>
            <th scope="col" class="num">Harga</th>
            <th scope="col">Stok</th>
            <th scope="col">Status</th>
            <th scope="col"><span class="visually-hidden">Aksi</span></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in terfilter" :key="item.id">
            <td>
              <div class="table-admin__nama">
                <span class="table-admin__visual">
                  <PhoneVisual
                    :colorway="colorwayMerek(item.merek)"
                    :size="44"
                    :nama="item.nama"
                    dekoratif
                  />
                  <img
                    v-if="urlGambarAman(item.gambar_url)"
                    :src="urlGambarAman(item.gambar_url)"
                    alt=""
                    loading="lazy"
                    @error="sembunyikanGambar"
                  />
                </span>
                <span>
                  <strong>{{ item.nama }}</strong>
                  <small>{{ item.merek }}</small>
                </span>
              </div>
            </td>
            <td class="num">{{ formatRupiah(item.harga) }}</td>
            <td>
              <div class="stok-admin">
                <StokBadge :stok="item.stok" />
                <small>{{ item.stok }} unit</small>
              </div>
            </td>
            <td>
              <span class="status-admin" :class="{ 'status-admin--nonaktif': !item.is_aktif }">
                <Eye v-if="item.is_aktif" :size="14" aria-hidden="true" />
                <EyeOff v-else :size="14" aria-hidden="true" />
                {{ item.is_aktif ? 'Tampil' : 'Disembunyikan' }}
              </span>
            </td>
            <td>
              <div class="table-admin__aksi">
                <RouterLink
                  class="btn btn--ghost btn--icon btn--small"
                  :to="{ name: 'admin-produk-edit', params: { id: item.id } }"
                  :aria-label="`Edit ${item.nama}`"
                  title="Edit produk"
                >
                  <Pencil :size="17" />
                </RouterLink>
                <button
                  class="btn btn--ghost btn--icon btn--small aksi-hapus"
                  type="button"
                  :aria-label="`Hapus ${item.nama}`"
                  title="Hapus produk"
                  @click="bukaHapus(item)"
                >
                  <Trash2 :size="17" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmDialog
      :terbuka="dialogTerbuka"
      judul="Hapus produk ini?"
      :deskripsi="`${kandidatHapus?.nama ?? 'Produk'} akan dihapus permanen dari katalog. Tindakan ini tidak bisa dibatalkan.`"
      label-konfirmasi="Hapus produk"
      label-batal="Batalkan"
      :sedang-proses="menghapus"
      @konfirmasi="konfirmasiHapus"
      @batal="batalHapus"
    />
  </AdminLayout>
</template>

<style scoped>
.ringkasan-admin {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  overflow: hidden;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
  margin-bottom: 16px;
}

.ringkasan-admin > div {
  display: grid;
  gap: 2px;
  border-right: 1px solid var(--line);
  padding: 12px 14px;
}

.ringkasan-admin > div:last-child {
  border-right: 0;
}

.ringkasan-admin span {
  color: var(--ink-muted);
  font-size: 11px;
  font-weight: 600;
}

.ringkasan-admin strong {
  font-family: var(--font-heading);
  font-size: 22px;
  font-weight: 600;
  line-height: 1.2;
}

.toolbar-admin {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.cari-admin {
  position: relative;
  width: min(100%, 380px);
}

.cari-admin__ikon {
  position: absolute;
  top: 50%;
  left: 13px;
  color: var(--ink-muted);
  pointer-events: none;
  transform: translateY(-50%);
}

.cari-admin .input {
  padding-right: 40px;
  padding-left: 40px;
}

.cari-admin__hapus {
  position: absolute;
  top: 50%;
  right: 5px;
  display: grid;
  width: 34px;
  height: 34px;
  place-items: center;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: var(--ink-muted);
  transform: translateY(-50%);
}

.cari-admin__hapus:hover {
  background: var(--surface-muted);
  color: var(--ink);
}

.toolbar-admin__jumlah {
  color: var(--ink-muted);
  font-size: 12px;
}

.table-admin {
  min-width: 780px;
}

.table-admin__visual {
  position: relative;
  display: grid;
  width: 44px;
  height: 44px;
  flex-shrink: 0;
  place-items: center;
  overflow: hidden;
  border: 1px solid var(--line);
  border-radius: 8px;
  background: var(--surface-muted);
}

.table-admin__visual img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  background: var(--surface-muted);
  object-fit: contain;
}

.stok-admin {
  display: grid;
  gap: 3px;
  justify-items: start;
}

.stok-admin small {
  color: var(--ink-muted);
  font-size: 11px;
}

.status-admin {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: var(--success);
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.status-admin--nonaktif {
  color: var(--ink-muted);
}

.aksi-hapus:hover {
  background: var(--danger-bg);
  color: var(--danger);
}

.table-loading {
  display: grid;
  gap: 0;
  padding: 8px 16px;
}

.baris-skeleton {
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr) 90px 80px;
  align-items: center;
  gap: 16px;
  border-bottom: 1px solid var(--line);
  padding-block: 12px;
}

.baris-skeleton:last-child {
  border-bottom: 0;
}

.skeleton-bulat {
  width: 44px;
  height: 44px;
  border-radius: 8px;
}

@media (min-width: 640px) {
  .ringkasan-admin > div {
    padding: 14px 20px;
  }

  .ringkasan-admin span {
    font-size: 12px;
  }

  .ringkasan-admin strong {
    font-size: 26px;
  }
}

@media (min-width: 900px) {
  .table-admin__nama {
    min-width: 280px;
  }

  .table-admin th,
  .table-admin td {
    padding: 14px 20px;
  }
}
</style>
