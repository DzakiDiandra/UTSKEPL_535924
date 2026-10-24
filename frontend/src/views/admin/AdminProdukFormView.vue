<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeft,
  Image,
  LoaderCircle,
  RefreshCw,
  Save,
  TriangleAlert,
} from 'lucide-vue-next'
import api from '@/api'
import { formatRupiah } from '@/utils/format'
import { colorwayMerek, urlGambarAman } from '@/utils/produk'
import AdminLayout from '@/components/AdminLayout.vue'
import PhoneVisual from '@/components/PhoneVisual.vue'
import StokBadge from '@/components/StokBadge.vue'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const { tampilkanToast } = useToast()

const DAFTAR_FIELD = [
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
]

function formKosong() {
  return {
    nama: '',
    merek: '',
    deskripsi: '',
    harga: '',
    stok: '',
    chipset: '',
    ram_gb: '',
    penyimpanan_gb: '',
    baterai_mah: '',
    kamera_mp: '',
    layar_inci: '',
    gambar_url: '',
    is_aktif: true,
  }
}

const form = reactive(formKosong())
const errors = reactive(Object.fromEntries(DAFTAR_FIELD.map((field) => [field, ''])))
const daftarMerek = ref([])
const memuatData = ref(Boolean(route.params.id))
const memuatMerek = ref(true)
const galatMuat = ref('')
const galatSimpan = ref('')
const menyimpan = ref(false)
const gambarPratinjauGagal = ref(false)

const modeEdit = computed(() => Boolean(route.params.id))
const pratinjauGambar = computed(() => urlGambarAman(form.gambar_url))
const pratinjauStok = computed(() => {
  const stok = Number(form.stok)
  return Number.isFinite(stok) && form.stok !== '' ? stok : 0
})
const pratinjauHarga = computed(() => {
  const harga = Number(form.harga)
  return form.harga !== '' && Number.isFinite(harga) ? formatRupiah(harga) : 'Harga belum diisi'
})
const pratinjauSpek = computed(() => {
  if (!form.ram_gb && !form.penyimpanan_gb) return 'Spesifikasi belum diisi'
  return `${form.ram_gb || '—'}/${form.penyimpanan_gb || '—'} GB`
})

function resetErrors() {
  DAFTAR_FIELD.forEach((field) => {
    errors[field] = ''
  })
}

function isAngka(nilai, minimal, bulat = true) {
  if (String(nilai).trim() === '') return false

  const angka = Number(nilai)

  if (!Number.isFinite(angka) || angka < minimal) return false
  return bulat ? Number.isInteger(angka) : true
}

function validasi() {
  resetErrors()

  const nama = form.nama.trim()
  const merek = form.merek.trim()
  const deskripsi = form.deskripsi.trim()
  const chipset = form.chipset.trim()
  const gambar = form.gambar_url.trim()

  if (!nama) errors.nama = 'Nama HP wajib diisi.'
  else if (nama.length > 255) errors.nama = 'Nama HP maksimal 255 karakter.'

  if (!merek) errors.merek = 'Merek wajib diisi.'
  else if (merek.length > 255) errors.merek = 'Merek maksimal 255 karakter.'

  if (!deskripsi) errors.deskripsi = 'Deskripsi wajib diisi.'

  if (form.harga === '') errors.harga = 'Harga wajib diisi.'
  else if (!isAngka(form.harga, 0)) errors.harga = 'Harga harus bilangan bulat 0 atau lebih.'

  if (form.stok === '') errors.stok = 'Stok wajib diisi.'
  else if (!isAngka(form.stok, 0)) errors.stok = 'Stok harus bilangan bulat 0 atau lebih.'

  if (!chipset) errors.chipset = 'Chipset wajib diisi.'
  else if (chipset.length > 255) errors.chipset = 'Chipset maksimal 255 karakter.'

  if (!isAngka(form.ram_gb, 1)) errors.ram_gb = 'RAM minimal 1 GB.'
  if (!isAngka(form.penyimpanan_gb, 1)) {
    errors.penyimpanan_gb = 'Penyimpanan minimal 1 GB.'
  }
  if (!isAngka(form.baterai_mah, 1)) errors.baterai_mah = 'Kapasitas baterai minimal 1 mAh.'
  if (!isAngka(form.kamera_mp, 1)) errors.kamera_mp = 'Resolusi kamera minimal 1 MP.'
  if (!isAngka(form.layar_inci, 0.01, false)) {
    errors.layar_inci = 'Ukuran layar harus lebih dari 0.'
  }

  if (gambar) {
    if (gambar.length > 2048) {
      errors.gambar_url = 'Alamat gambar maksimal 2048 karakter.'
    } else {
      try {
        const alamat = new URL(gambar)
        if (!['http:', 'https:'].includes(alamat.protocol)) {
          errors.gambar_url = 'Alamat gambar harus diawali http:// atau https://.'
        }
      } catch {
        errors.gambar_url = 'Alamat gambar tidak valid.'
      }
    }
  }

  const pertama = DAFTAR_FIELD.find((field) => errors[field])

  if (pertama) {
    nextTick(() => {
      const elemen = document.getElementById(pertama)
      elemen?.focus()
      elemen?.scrollIntoView({ block: 'center', behavior: 'smooth' })
    })
    return false
  }

  return true
}

function muatKeForm(item) {
  Object.assign(form, {
    nama: item.nama ?? '',
    merek: item.merek ?? '',
    deskripsi: item.deskripsi ?? '',
    harga: String(item.harga ?? ''),
    stok: String(item.stok ?? ''),
    chipset: item.chipset ?? '',
    ram_gb: String(item.ram_gb ?? ''),
    penyimpanan_gb: String(item.penyimpanan_gb ?? ''),
    baterai_mah: String(item.baterai_mah ?? ''),
    kamera_mp: String(item.kamera_mp ?? ''),
    layar_inci: String(item.layar_inci ?? ''),
    gambar_url: item.gambar_url ?? '',
    is_aktif: Boolean(item.is_aktif),
  })
}

async function muatMerek() {
  memuatMerek.value = true

  try {
    const { data } = await api.get('/merek')
    daftarMerek.value = data.data ?? []
  } catch {
    daftarMerek.value = []
  } finally {
    memuatMerek.value = false
  }
}

async function muatDetail() {
  if (!modeEdit.value) return

  memuatData.value = true
  galatMuat.value = ''

  try {
    const { data } = await api.get('/admin/produk')
    const item = (data.data ?? []).find((produk) => String(produk.id) === String(route.params.id))

    if (!item) {
      galatMuat.value = 'Produk tidak ditemukan. Produk mungkin sudah dihapus.'
      return
    }

    muatKeForm(item)
    document.title = `Edit ${item.nama} — LokaHP`
  } catch {
    galatMuat.value = 'Data produk belum bisa dimuat. Periksa koneksi lalu coba lagi.'
  } finally {
    memuatData.value = false
  }
}

function payload() {
  return {
    nama: form.nama.trim(),
    merek: form.merek.trim(),
    deskripsi: form.deskripsi.trim(),
    harga: Number(form.harga),
    stok: Number(form.stok),
    chipset: form.chipset.trim(),
    ram_gb: Number(form.ram_gb),
    penyimpanan_gb: Number(form.penyimpanan_gb),
    baterai_mah: Number(form.baterai_mah),
    kamera_mp: Number(form.kamera_mp),
    layar_inci: Number(form.layar_inci),
    gambar_url: form.gambar_url.trim() || null,
    is_aktif: form.is_aktif,
  }
}

function tanganiErrorSimpan(error) {
  const kesalahan = error.response?.data?.errors

  if (kesalahan) {
    let pertama = ''

    DAFTAR_FIELD.forEach((field) => {
      if (kesalahan[field]) {
        errors[field] = 'Isian ini belum sesuai. Periksa kembali.'
        if (!pertama) pertama = field
      }
    })

    galatSimpan.value = 'Ada isian yang perlu diperbaiki. Periksa kolom yang ditandai.'

    if (pertama) {
      nextTick(() => {
        const elemen = document.getElementById(pertama)
        elemen?.focus()
        elemen?.scrollIntoView({ block: 'center', behavior: 'smooth' })
      })
    }

    return
  }

  if (error.response?.status === 401) {
    galatSimpan.value = 'Sesi Anda sudah berakhir. Silakan masuk kembali.'
  } else if (!error.response) {
    galatSimpan.value = 'Server belum bisa dihubungi. Pastikan backend berjalan lalu coba lagi.'
  } else {
    galatSimpan.value = 'Produk belum bisa disimpan. Silakan coba lagi.'
  }
}

async function simpan() {
  if (menyimpan.value) return

  galatSimpan.value = ''

  if (!validasi()) return

  menyimpan.value = true

  try {
    if (modeEdit.value) {
      await api.put(`/produk/${route.params.id}`, payload())
      tampilkanToast(`Perubahan ${form.nama.trim()} sudah disimpan.`, 'success')
    } else {
      await api.post('/produk', payload())
      tampilkanToast(`Produk ${form.nama.trim()} sudah ditambahkan.`, 'success')
    }

    await router.push({ name: 'admin-produk' })
  } catch (error) {
    tanganiErrorSimpan(error)
  } finally {
    menyimpan.value = false
  }
}

watch(
  () => form.gambar_url,
  () => {
    gambarPratinjauGagal.value = false
  },
)

onMounted(() => {
  muatMerek()
  muatDetail()
})
</script>

<template>
  <AdminLayout>
    <div class="form-head">
      <RouterLink class="kembali-admin" :to="{ name: 'admin-produk' }">
        <ArrowLeft :size="16" />
        Kembali ke daftar produk
      </RouterLink>
      <div class="form-head__judul">
        <div>
          <p class="eyebrow">Panel admin / Produk</p>
          <h1>{{ modeEdit ? 'Edit produk' : 'Tambah produk' }}</h1>
          <p class="lede">
            {{ modeEdit ? 'Perbarui data produk yang sudah ada.' : 'Isi data produk baru untuk katalog toko.' }}
          </p>
        </div>
      </div>
    </div>

    <div v-if="memuatData" class="form-loading" aria-busy="true">
      <span class="visually-hidden">Memuat data produk…</span>
      <LoaderCircle class="spin" :size="24" />
      <p>Memuat data produk…</p>
    </div>

    <div v-else-if="galatMuat" class="state-panel state-panel--error" role="alert">
      <span class="state-panel__icon" aria-hidden="true"><TriangleAlert :size="22" /></span>
      <div class="state-panel__body">
        <h2>Data produk belum bisa dibuka</h2>
        <p>{{ galatMuat }}</p>
      </div>
      <div class="form-actions">
        <button class="btn btn--secondary" type="button" @click="muatDetail">
          <RefreshCw :size="16" />
          Coba lagi
        </button>
        <RouterLink class="btn btn--ghost" :to="{ name: 'admin-produk' }">Kembali</RouterLink>
      </div>
    </div>

    <div v-else class="form-layout">
      <form class="form-kartu" novalidate @submit.prevent="simpan">
        <div v-if="galatSimpan" class="form-galat" role="alert">
          <TriangleAlert :size="18" aria-hidden="true" />
          <p>{{ galatSimpan }}</p>
        </div>

        <section class="form-section">
          <h2><span>01</span> Informasi produk</h2>
          <div class="form-grid">
            <div class="field field--full">
              <label for="nama">Nama HP</label>
              <input
                id="nama"
                v-model="form.nama"
                class="input"
                :class="{ 'input--error': errors.nama }"
                type="text"
                maxlength="255"
                placeholder="Contoh: Samsung Galaxy A15"
                :aria-invalid="Boolean(errors.nama)"
                :aria-describedby="errors.nama ? 'error-nama' : undefined"
              />
              <p v-if="errors.nama" id="error-nama" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.nama }}
              </p>
            </div>

            <div class="field">
              <label for="merek">Merek</label>
              <input
                id="merek"
                v-model="form.merek"
                class="input"
                :class="{ 'input--error': errors.merek }"
                type="text"
                list="daftar-merek"
                maxlength="255"
                placeholder="Contoh: Samsung"
                :aria-invalid="Boolean(errors.merek)"
                :aria-describedby="errors.merek ? 'error-merek' : undefined"
              />
              <datalist id="daftar-merek">
                <option v-for="namaMerek in daftarMerek" :key="namaMerek" :value="namaMerek" />
              </datalist>
              <p v-if="memuatMerek" class="field-help">Memuat merek yang sudah ada…</p>
              <p v-if="errors.merek" id="error-merek" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.merek }}
              </p>
            </div>

            <div class="field">
              <label for="harga">Harga</label>
              <input
                id="harga"
                v-model="form.harga"
                class="input"
                :class="{ 'input--error': errors.harga }"
                type="number"
                min="0"
                step="1000"
                inputmode="numeric"
                placeholder="2499000"
                :aria-invalid="Boolean(errors.harga)"
                :aria-describedby="errors.harga ? 'error-harga' : 'bantuan-harga'"
              />
              <p v-if="errors.harga" id="error-harga" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.harga }}
              </p>
              <p v-else id="bantuan-harga" class="field-help">{{ pratinjauHarga }}</p>
            </div>

            <div class="field">
              <label for="stok">Stok</label>
              <input
                id="stok"
                v-model="form.stok"
                class="input"
                :class="{ 'input--error': errors.stok }"
                type="number"
                min="0"
                step="1"
                inputmode="numeric"
                placeholder="12"
                :aria-invalid="Boolean(errors.stok)"
                :aria-describedby="errors.stok ? 'error-stok' : 'bantuan-stok'"
              />
              <p v-if="errors.stok" id="error-stok" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.stok }}
              </p>
              <p v-else id="bantuan-stok" class="field-help">0 = habis, 1–5 = menipis, &gt;5 = tersedia.</p>
            </div>

            <div class="field field--full">
              <label for="deskripsi">Deskripsi</label>
              <textarea
                id="deskripsi"
                v-model="form.deskripsi"
                class="textarea"
                :class="{ 'input--error': errors.deskripsi }"
                placeholder="Tuliskan gambaran singkat produk."
                :aria-invalid="Boolean(errors.deskripsi)"
                :aria-describedby="errors.deskripsi ? 'error-deskripsi' : undefined"
              ></textarea>
              <p v-if="errors.deskripsi" id="error-deskripsi" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.deskripsi }}
              </p>
            </div>
          </div>
        </section>

        <section class="form-section">
          <h2><span>02</span> Spesifikasi</h2>
          <div class="form-grid">
            <div class="field field--full">
              <label for="chipset">Chipset</label>
              <input
                id="chipset"
                v-model="form.chipset"
                class="input"
                :class="{ 'input--error': errors.chipset }"
                type="text"
                maxlength="255"
                placeholder="Contoh: MediaTek Helio G99"
                :aria-invalid="Boolean(errors.chipset)"
                :aria-describedby="errors.chipset ? 'error-chipset' : undefined"
              />
              <p v-if="errors.chipset" id="error-chipset" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.chipset }}
              </p>
            </div>

            <div class="field">
              <label for="ram_gb">RAM (GB)</label>
              <input
                id="ram_gb"
                v-model="form.ram_gb"
                class="input"
                :class="{ 'input--error': errors.ram_gb }"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                placeholder="8"
                :aria-invalid="Boolean(errors.ram_gb)"
                :aria-describedby="errors.ram_gb ? 'error-ram' : undefined"
              />
              <p v-if="errors.ram_gb" id="error-ram" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.ram_gb }}
              </p>
            </div>

            <div class="field">
              <label for="penyimpanan_gb">Penyimpanan (GB)</label>
              <input
                id="penyimpanan_gb"
                v-model="form.penyimpanan_gb"
                class="input"
                :class="{ 'input--error': errors.penyimpanan_gb }"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                placeholder="256"
                :aria-invalid="Boolean(errors.penyimpanan_gb)"
                :aria-describedby="errors.penyimpanan_gb ? 'error-penyimpanan' : undefined"
              />
              <p v-if="errors.penyimpanan_gb" id="error-penyimpanan" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.penyimpanan_gb }}
              </p>
            </div>

            <div class="field">
              <label for="layar_inci">Layar (inci)</label>
              <input
                id="layar_inci"
                v-model="form.layar_inci"
                class="input"
                :class="{ 'input--error': errors.layar_inci }"
                type="number"
                min="0.01"
                step="0.01"
                inputmode="decimal"
                placeholder="6.5"
                :aria-invalid="Boolean(errors.layar_inci)"
                :aria-describedby="errors.layar_inci ? 'error-layar' : undefined"
              />
              <p v-if="errors.layar_inci" id="error-layar" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.layar_inci }}
              </p>
            </div>

            <div class="field">
              <label for="baterai_mah">Baterai (mAh)</label>
              <input
                id="baterai_mah"
                v-model="form.baterai_mah"
                class="input"
                :class="{ 'input--error': errors.baterai_mah }"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                placeholder="5000"
                :aria-invalid="Boolean(errors.baterai_mah)"
                :aria-describedby="errors.baterai_mah ? 'error-baterai' : undefined"
              />
              <p v-if="errors.baterai_mah" id="error-baterai" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.baterai_mah }}
              </p>
            </div>

            <div class="field">
              <label for="kamera_mp">Kamera utama (MP)</label>
              <input
                id="kamera_mp"
                v-model="form.kamera_mp"
                class="input"
                :class="{ 'input--error': errors.kamera_mp }"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                placeholder="50"
                :aria-invalid="Boolean(errors.kamera_mp)"
                :aria-describedby="errors.kamera_mp ? 'error-kamera' : undefined"
              />
              <p v-if="errors.kamera_mp" id="error-kamera" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.kamera_mp }}
              </p>
            </div>
          </div>
        </section>

        <section class="form-section">
          <h2><span>03</span> Foto dan status</h2>
          <div class="form-grid">
            <div class="field field--full">
              <label for="gambar_url">Alamat gambar <span class="field-label__wajib">Opsional</span></label>
              <input
                id="gambar_url"
                v-model="form.gambar_url"
                class="input"
                :class="{ 'input--error': errors.gambar_url }"
                type="url"
                maxlength="2048"
                placeholder="https://contoh.com/foto-hp.jpg"
                :aria-invalid="Boolean(errors.gambar_url)"
                :aria-describedby="errors.gambar_url ? 'error-gambar' : 'bantuan-gambar'"
              />
              <p v-if="errors.gambar_url" id="error-gambar" class="field-error">
                <TriangleAlert :size="14" aria-hidden="true" />
                {{ errors.gambar_url }}
              </p>
              <p v-else id="bantuan-gambar" class="field-help">
                Kosongkan untuk memakai ilustrasi HP bawaan.
              </p>
            </div>

            <div class="field field--full">
              <label class="checkbox-field" for="is_aktif">
                <input id="is_aktif" v-model="form.is_aktif" type="checkbox" />
                <span>
                  Tampilkan produk di katalog publik
                  <span class="field-help">Jika dimatikan, produk tetap tersimpan tetapi tidak tampil di toko.</span>
                </span>
              </label>
            </div>
          </div>
        </section>

        <div class="form-actions">
          <button class="btn btn--primary" type="submit" :disabled="menyimpan">
            <LoaderCircle v-if="menyimpan" class="spin" :size="18" />
            <Save v-else :size="18" />
            {{ menyimpan ? 'Menyimpan…' : modeEdit ? 'Simpan perubahan' : 'Tambah produk' }}
          </button>
          <RouterLink class="btn btn--secondary" :to="{ name: 'admin-produk' }">Batalkan</RouterLink>
        </div>
      </form>

      <aside class="pratinjau" aria-label="Pratinjau produk">
        <div class="pratinjau__head">
          <p class="eyebrow">Pratinjau</p>
          <span>{{ modeEdit ? 'Mode edit' : 'Produk baru' }}</span>
        </div>

        <div class="pratinjau__kartu">
          <div class="pratinjau__visual">
            <span class="pratinjau__badge">
              <StokBadge :stok="pratinjauStok" />
            </span>
            <img
              v-if="pratinjauGambar && !gambarPratinjauGagal"
              class="pratinjau__gambar"
              :src="pratinjauGambar"
              alt=""
              @error="gambarPratinjauGagal = true"
            />
            <PhoneVisual
              v-else
              :colorway="colorwayMerek(form.merek)"
              :size="176"
              :nama="form.nama || 'HP'"
              dekoratif
            />
          </div>
          <div class="pratinjau__isi">
            <p class="eyebrow">{{ form.merek || 'Merek' }}</p>
            <h2>{{ form.nama || 'Nama HP baru' }}</h2>
            <p class="pratinjau__spek">{{ pratinjauSpek }}</p>
            <p class="price">{{ pratinjauHarga }}</p>
          </div>
        </div>

        <p class="pratinjau__ket">
          <Image :size="15" aria-hidden="true" />
          Pratinjau mengikuti isian. Simpan untuk memperbarui katalog.
        </p>
      </aside>
    </div>
  </AdminLayout>
</template>

<style scoped>
.form-head {
  display: grid;
  gap: 16px;
  margin-bottom: 24px;
}

.kembali-admin {
  display: inline-flex;
  width: fit-content;
  align-items: center;
  gap: 6px;
  color: var(--ink-muted);
  font-size: 13px;
  font-weight: 700;
}

.kembali-admin:hover {
  color: var(--accent);
}

.form-head__judul > div {
  display: grid;
  gap: 6px;
}

.form-head .page-head {
  margin: 0;
}

.form-loading {
  display: flex;
  min-height: 280px;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
  color: var(--ink-muted);
}

.form-layout {
  display: grid;
  gap: 24px;
  align-items: start;
}

.form-kartu {
  display: grid;
  gap: 24px;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
  padding: 20px;
}

.form-galat {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  border-radius: var(--radius-control);
  background: var(--danger-bg);
  color: var(--danger);
  padding: 12px;
  font-size: 13px;
  font-weight: 600;
}

.form-galat svg {
  flex-shrink: 0;
  margin-top: 1px;
}

.form-section {
  display: grid;
  gap: 20px;
}

.form-section:first-of-type {
  border-top: 0;
  padding-top: 0;
}

.form-section h2 {
  display: flex;
  align-items: baseline;
  gap: 8px;
  font-size: 20px;
}

.form-section h2 span {
  color: var(--accent);
  font-family: var(--font-body);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.checkbox-field {
  align-items: flex-start;
}

.checkbox-field > span {
  display: grid;
  gap: 2px;
}

.pratinjau {
  display: grid;
  gap: 14px;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface-muted);
  padding: 16px;
}

.pratinjau__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.pratinjau__head > span {
  border: 1px solid var(--line);
  border-radius: var(--radius-pill);
  background: var(--surface);
  color: var(--ink-muted);
  padding: 3px 8px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.pratinjau__kartu {
  overflow: hidden;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
}

.pratinjau__visual {
  position: relative;
  display: grid;
  aspect-ratio: 4 / 5;
  place-items: center;
  background: var(--accent-tint);
  padding: 18px;
}

.pratinjau__badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 1;
}

.pratinjau__gambar {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.pratinjau__isi {
  display: grid;
  gap: 5px;
  padding: 14px;
}

.pratinjau__isi h2 {
  display: -webkit-box;
  overflow: hidden;
  font-size: 17px;
  line-height: 1.3;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
}

.pratinjau__spek {
  color: var(--ink-muted);
  font-size: 13px;
}

.pratinjau__ket {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  color: var(--ink-muted);
  font-size: 12px;
}

.pratinjau__ket svg {
  flex-shrink: 0;
  margin-top: 2px;
}

@media (min-width: 640px) {
  .form-kartu {
    padding: 28px;
  }

  .pratinjau {
    padding: 20px;
  }
}

@media (min-width: 1024px) {
  .form-layout {
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 32px;
  }

  .pratinjau {
    position: sticky;
    top: 88px;
  }
}
</style>
