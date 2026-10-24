<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { PackageSearch, RefreshCw, Search, TriangleAlert, X } from 'lucide-vue-next'
import api from '@/api'
import ProdukCard from '@/components/ProdukCard.vue'
import ProdukGridSkeleton from '@/components/ProdukGridSkeleton.vue'

const route = useRoute()
const router = useRouter()

const URUTAN = ['terbaru', 'harga_asc', 'harga_desc']

function bacaQuery(nilai) {
  return typeof nilai === 'string' ? nilai : ''
}

function urutValid(nilai) {
  const teks = bacaQuery(nilai)
  return URUTAN.includes(teks) ? teks : 'terbaru'
}

const pencarian = ref(bacaQuery(route.query.q))
const merekAktif = ref(bacaQuery(route.query.merek))
const urut = ref(urutValid(route.query.urut))
const produk = ref([])
const merek = ref([])
const memuat = ref(true)
const memuatMerek = ref(true)
const galat = ref('')
const inputCari = ref(null)

let timerCari = null
let permintaanTerakhir = 0

const adaFilter = computed(
  () => Boolean(pencarian.value.trim() || merekAktif.value) || urut.value !== 'terbaru',
)

function terapkanQuery() {
  const query = {}
  const teks = pencarian.value.trim()

  if (teks) query.q = teks
  if (merekAktif.value) query.merek = merekAktif.value
  if (urut.value !== 'terbaru') query.urut = urut.value

  router.replace({ name: 'katalog', query })
}

async function muatMerek() {
  memuatMerek.value = true

  try {
    const { data } = await api.get('/merek')
    merek.value = data.data ?? []
  } catch {
    merek.value = [...new Set(produk.value.map((item) => item.merek))].sort()
  } finally {
    memuatMerek.value = false
  }
}

async function muatProduk() {
  const nomorPermintaan = ++permintaanTerakhir
  memuat.value = true
  galat.value = ''

  try {
    const { data } = await api.get('/produk', {
      params: {
        q: pencarian.value.trim() || undefined,
        merek: merekAktif.value || undefined,
        urut: urut.value,
      },
    })

    if (nomorPermintaan !== permintaanTerakhir) return

    produk.value = data.data ?? []

    if (!merek.value.length) {
      merek.value = [...new Set(produk.value.map((item) => item.merek))].sort()
    }
  } catch {
    if (nomorPermintaan !== permintaanTerakhir) return

    produk.value = []
    galat.value = 'Produk belum bisa dimuat. Periksa koneksi lalu coba lagi.'
  } finally {
    if (nomorPermintaan === permintaanTerakhir) memuat.value = false
  }
}

function pilihMerek(nama) {
  merekAktif.value = nama
  terapkanQuery()
}

function cariSekarang() {
  clearTimeout(timerCari)
  terapkanQuery()
}

function bersihkanCari() {
  pencarian.value = ''
  cariSekarang()
  nextTick(() => inputCari.value?.focus())
}

function resetFilter() {
  const perluMuatUlang = !adaFilter.value
  pencarian.value = ''
  merekAktif.value = ''
  urut.value = 'terbaru'

  if (perluMuatUlang) {
    muatProduk()
  } else {
    terapkanQuery()
  }
}

watch(pencarian, () => {
  clearTimeout(timerCari)
  timerCari = setTimeout(() => {
    if (pencarian.value.trim() === bacaQuery(route.query.q)) return
    terapkanQuery()
  }, 300)
})

watch(
  () => route.query,
  () => {
    if (route.name !== 'katalog') return

    pencarian.value = bacaQuery(route.query.q)
    merekAktif.value = bacaQuery(route.query.merek)
    urut.value = urutValid(route.query.urut)
    muatProduk()
  },
)

onMounted(() => {
  muatMerek()
  muatProduk()
})
</script>

<template>
  <div class="container katalog">
    <header class="page-head">
      <div>
        <p class="eyebrow">Katalog</p>
        <h1>Semua HP, satu perbandingan.</h1>
        <p class="lede">Gunakan pencarian dan merek untuk mempersempit pilihan.</p>
      </div>
    </header>

    <section class="filter-bar" aria-label="Filter katalog">
      <div class="filter-bar__utama">
        <form class="field search-field" role="search" @submit.prevent="cariSekarang">
          <label for="cari-produk">Cari HP</label>
          <div class="search-field__kotak">
            <Search class="search-field__ikon" :size="18" aria-hidden="true" />
            <input
              id="cari-produk"
              ref="inputCari"
              v-model="pencarian"
              class="input"
              type="search"
              placeholder="Cari nama HP atau merek"
              autocomplete="off"
            />
            <button
              v-if="pencarian"
              class="search-field__hapus"
              type="button"
              aria-label="Hapus kata pencarian"
              @click="bersihkanCari"
            >
              <X :size="16" />
            </button>
          </div>
        </form>

        <div class="field sort-field">
          <label for="urut-produk">Urutkan</label>
          <select id="urut-produk" v-model="urut" class="select">
            <option value="terbaru">Terbaru</option>
            <option value="harga_asc">Harga terendah</option>
            <option value="harga_desc">Harga tertinggi</option>
          </select>
        </div>
      </div>

      <div class="filter-bar__merek">
        <p id="label-merek" class="field-label">Merek</p>
        <div class="chip-row" role="group" aria-labelledby="label-merek">
          <button
            class="chip"
            :class="{ 'chip--aktif': !merekAktif }"
            type="button"
            :aria-pressed="!merekAktif"
            @click="pilihMerek('')"
          >
            Semua
          </button>
          <button
            v-for="namaMerek in merek"
            :key="namaMerek"
            class="chip"
            :class="{ 'chip--aktif': merekAktif === namaMerek }"
            type="button"
            :aria-pressed="merekAktif === namaMerek"
            @click="pilihMerek(namaMerek)"
          >
            {{ namaMerek }}
          </button>
          <span v-if="memuatMerek" class="chip chip--menunggu" aria-hidden="true">Memuat merek…</span>
        </div>
      </div>
    </section>

    <div class="hasil-head">
      <p class="result-count" aria-live="polite">
        <template v-if="memuat">Memuat produk…</template>
        <template v-else-if="galat">Produk gagal dimuat</template>
        <template v-else>{{ produk.length }} produk ditemukan</template>
      </p>
      <button v-if="adaFilter" class="btn btn--ghost btn--small" type="button" @click="resetFilter">
        <X :size="15" />
        Hapus filter
      </button>
    </div>

    <ProdukGridSkeleton v-if="memuat" :jumlah="6" />

    <div v-else-if="galat" class="state-panel state-panel--error" role="alert">
      <span class="state-panel__icon" aria-hidden="true"><TriangleAlert :size="22" /></span>
      <div class="state-panel__body">
        <h2>Katalog belum bisa dibuka</h2>
        <p>{{ galat }}</p>
      </div>
      <button class="btn btn--secondary" type="button" @click="muatProduk">
        <RefreshCw :size="16" />
        Coba lagi
      </button>
    </div>

    <div v-else-if="produk.length" class="grid-produk">
      <ProdukCard v-for="item in produk" :key="item.id" :produk="item" />
    </div>

    <div v-else class="state-panel">
      <span class="state-panel__icon" aria-hidden="true"><PackageSearch :size="22" /></span>
      <div class="state-panel__body">
        <h2>Belum ada produk yang cocok</h2>
        <p v-if="adaFilter">
          Coba kata kunci lain, pilih merek yang berbeda, atau hapus filter untuk melihat semua HP.
        </p>
        <p v-else>Belum ada produk aktif di katalog saat ini. Silakan cek kembali nanti.</p>
      </div>
      <button class="btn btn--secondary" type="button" @click="resetFilter">
        {{ adaFilter ? 'Tampilkan semua produk' : 'Muat ulang' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.katalog {
  padding-block: 32px 24px;
}

.page-head {
  margin-bottom: 24px;
}

.page-head .lede {
  max-width: 56ch;
}

.filter-bar {
  display: grid;
  gap: 16px;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
  padding: 16px;
}

.filter-bar__utama {
  display: grid;
  gap: 12px;
}

.search-field__kotak {
  position: relative;
}

.search-field__ikon {
  position: absolute;
  top: 50%;
  left: 14px;
  color: var(--ink-muted);
  pointer-events: none;
  transform: translateY(-50%);
}

.search-field .input {
  padding-right: 44px;
  padding-left: 42px;
}

.search-field__hapus {
  position: absolute;
  top: 50%;
  right: 6px;
  display: grid;
  width: 36px;
  height: 36px;
  place-items: center;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: var(--ink-muted);
  transform: translateY(-50%);
}

.search-field__hapus:hover {
  background: var(--surface-muted);
  color: var(--ink);
}

.filter-bar__merek {
  display: grid;
  gap: 8px;
}

.field-label {
  margin: 0;
}

.chip--menunggu {
  border-style: dashed;
  color: var(--ink-muted);
  pointer-events: none;
}

.hasil-head {
  display: flex;
  min-height: 56px;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding-block: 12px;
}

.hasil-head .btn {
  margin-left: auto;
}

@media (min-width: 640px) {
  .filter-bar {
    padding: 20px;
  }

  .filter-bar__utama {
    grid-template-columns: minmax(0, 1fr) 220px;
    align-items: end;
  }
}

@media (min-width: 900px) {
  .katalog {
    padding-block: 48px 24px;
  }

  .filter-bar {
    grid-template-columns: minmax(0, 1.5fr) minmax(0, 2fr);
    align-items: end;
    gap: 24px;
  }

  .filter-bar__merek {
    padding-bottom: 1px;
  }

  .filter-bar__merek .chip-row {
    flex-wrap: wrap;
    overflow: visible;
    padding-bottom: 0;
  }
}
</style>
