<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowLeft,
  BatteryCharging,
  Camera,
  ChevronRight,
  Cpu,
  HardDrive,
  MemoryStick,
  MessageCircle,
  Monitor,
  PackageSearch,
  RefreshCw,
  ShieldCheck,
  TriangleAlert,
} from 'lucide-vue-next'
import api from '@/api'
import { formatRupiah } from '@/utils/format'
import { buatLinkWhatsapp, colorwayMerek, urlGambarAman } from '@/utils/produk'
import PhoneVisual from '@/components/PhoneVisual.vue'
import StokBadge from '@/components/StokBadge.vue'

const route = useRoute()
const produk = ref(null)
const memuat = ref(true)
const galat = ref('')
const tidakDitemukan = ref(false)
const gambarGagal = ref(false)

const nomorWhatsapp = String(import.meta.env.VITE_WA_NUMBER ?? '').replace(/\D/g, '')
const gambar = computed(() => {
  if (gambarGagal.value) return ''
  return urlGambarAman(produk.value?.gambar_url)
})
const tautanWhatsapp = computed(() =>
  produk.value ? buatLinkWhatsapp(nomorWhatsapp, produk.value.nama) : '',
)

const spesifikasi = computed(() => {
  if (!produk.value) return []

  const item = produk.value

  return [
    { label: 'Chipset', nilai: item.chipset || '—', ikon: Cpu },
    { label: 'RAM', nilai: `${item.ram_gb} GB`, ikon: MemoryStick },
    { label: 'Penyimpanan', nilai: `${item.penyimpanan_gb} GB`, ikon: HardDrive },
    { label: 'Layar', nilai: `${item.layar_inci} inci`, ikon: Monitor },
    { label: 'Baterai', nilai: `${item.baterai_mah} mAh`, ikon: BatteryCharging },
    { label: 'Kamera utama', nilai: `${item.kamera_mp} MP`, ikon: Camera },
  ]
})

async function muatProduk() {
  memuat.value = true
  galat.value = ''
  tidakDitemukan.value = false
  gambarGagal.value = false

  try {
    const { data } = await api.get(`/produk/${route.params.id}`)
    produk.value = data.data
    document.title = `${produk.value.nama} — LokaHP`
  } catch (error) {
    produk.value = null

    if (error.response?.status === 404) {
      tidakDitemukan.value = true
    } else {
      galat.value = 'Detail HP belum bisa dimuat. Periksa koneksi lalu coba lagi.'
    }
  } finally {
    memuat.value = false
  }
}

watch(
  () => route.params.id,
  () => {
    if (route.name === 'produk-detail') muatProduk()
  },
)

onMounted(muatProduk)
</script>

<template>
  <div class="container detail">
    <nav class="remah" aria-label="Breadcrumb">
      <RouterLink :to="{ name: 'beranda' }">Beranda</RouterLink>
      <ChevronRight :size="14" aria-hidden="true" />
      <RouterLink :to="{ name: 'katalog' }">Katalog</RouterLink>
      <ChevronRight :size="14" aria-hidden="true" />
      <span aria-current="page">{{ produk?.nama ?? 'Detail HP' }}</span>
    </nav>

    <div v-if="memuat" class="detail__memuat" aria-busy="true">
      <span class="visually-hidden">Memuat detail produk…</span>
      <div class="skeleton detail__skeleton-gambar"></div>
      <div class="detail__skeleton-info">
        <div class="skeleton skeleton-line skeleton-line--short"></div>
        <div class="skeleton skeleton-line detail__skeleton-judul"></div>
        <div class="skeleton skeleton-line skeleton-line--price"></div>
        <div class="skeleton skeleton-line"></div>
        <div class="skeleton skeleton-line"></div>
        <div class="skeleton detail__skeleton-tombol"></div>
      </div>
    </div>

    <div v-else-if="tidakDitemukan" class="state-panel">
      <span class="state-panel__icon" aria-hidden="true"><PackageSearch :size="24" /></span>
      <div class="state-panel__body">
        <h1>HP ini tidak tersedia</h1>
        <p>
          Produk mungkin sudah tidak aktif atau tautannya sudah berubah. Lihat katalog untuk
          menemukan pilihan lain.
        </p>
      </div>
      <RouterLink class="btn btn--primary" :to="{ name: 'katalog' }">Kembali ke katalog</RouterLink>
    </div>

    <div v-else-if="galat" class="state-panel state-panel--error" role="alert">
      <span class="state-panel__icon" aria-hidden="true"><TriangleAlert :size="24" /></span>
      <div class="state-panel__body">
        <h1>Detail belum bisa dibuka</h1>
        <p>{{ galat }}</p>
      </div>
      <button class="btn btn--secondary" type="button" @click="muatProduk">
        <RefreshCw :size="16" />
        Coba lagi
      </button>
    </div>

    <template v-else-if="produk">
      <div class="detail__grid">
        <div class="detail__galeri">
          <span class="detail__merek">{{ produk.merek }}</span>
          <img
            v-if="gambar"
            class="detail__gambar"
            :src="gambar"
            :alt="`Foto ${produk.nama}`"
            @error="gambarGagal = true"
          />
          <PhoneVisual
            v-else
            :colorway="colorwayMerek(produk.merek)"
            :size="270"
            :nama="produk.nama"
          />
          <span class="detail__garis" aria-hidden="true"></span>
        </div>

        <div class="detail__info">
          <div class="detail__judul">
            <p class="eyebrow">HP {{ produk.merek }}</p>
            <h1>{{ produk.nama }}</h1>
            <p class="detail__harga price price--lg">{{ formatRupiah(produk.harga) }}</p>
          </div>

          <div class="detail__stok">
            <div>
              <p class="field-label">Ketersediaan</p>
              <p class="detail__jumlah">
                <template v-if="produk.stok > 0">
                  Stok tersedia: <strong>{{ produk.stok }}</strong> unit
                </template>
                <template v-else>Stok sedang habis</template>
              </p>
            </div>
            <StokBadge :stok="produk.stok" />
          </div>

          <p class="detail__deskripsi">{{ produk.deskripsi }}</p>

          <div class="detail__aksi">
            <template v-if="tautanWhatsapp">
              <a
                v-if="produk.stok > 0"
                class="btn btn--primary"
                :href="tautanWhatsapp"
                target="_blank"
                rel="noopener"
              >
                <MessageCircle :size="18" />
                Tanya stok lewat WhatsApp
              </a>
              <button v-else class="btn btn--primary" type="button" disabled>
                <MessageCircle :size="18" />
                Stok sedang habis
              </button>
            </template>
            <RouterLink v-else class="btn btn--primary" :to="{ name: 'katalog' }">
              Lihat HP lain
            </RouterLink>
            <RouterLink class="btn btn--secondary" :to="{ name: 'katalog' }">
              <ArrowLeft :size="17" />
              Kembali ke katalog
            </RouterLink>
          </div>

          <p class="detail__ket">
            Jumlah stok mengikuti data saat halaman ini dimuat.
            <template v-if="tautanWhatsapp"> Konfirmasi akhir dilakukan lewat WhatsApp.</template>
          </p>
        </div>
      </div>

      <section class="spesifikasi" aria-labelledby="judul-spesifikasi">
        <div class="spesifikasi__head">
          <div>
            <p class="eyebrow">Lembar singkat</p>
            <h2 id="judul-spesifikasi">Spesifikasi {{ produk.nama }}</h2>
          </div>
          <p class="spesifikasi__catatan">
            <ShieldCheck :size="16" aria-hidden="true" />
            Data sesuai katalog toko
          </p>
        </div>

        <dl class="spek-grid">
          <div v-for="baris in spesifikasi" :key="baris.label" class="spek-baris">
            <dt>
              <component :is="baris.ikon" :size="18" aria-hidden="true" />
              {{ baris.label }}
            </dt>
            <dd>{{ baris.nilai }}</dd>
          </div>
        </dl>
      </section>
    </template>
  </div>
</template>

<style scoped>
.detail {
  padding-block: 24px 24px;
}

.remah {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  margin-bottom: 24px;
  color: var(--ink-muted);
  font-size: 13px;
}

.remah a {
  color: var(--ink-muted);
  font-weight: 600;
}

.remah a:hover {
  color: var(--accent);
}

.remah span {
  max-width: 240px;
  overflow: hidden;
  color: var(--ink);
  text-overflow: ellipsis;
  white-space: nowrap;
}

.detail__grid {
  display: grid;
  gap: 32px;
}

.detail__galeri {
  position: relative;
  display: grid;
  min-height: 360px;
  place-items: center;
  overflow: hidden;
  border: 1px solid #c9dce1;
  border-radius: 28px;
  background: var(--accent-tint);
  padding: 40px 24px;
}

.detail__galeri::before {
  position: absolute;
  width: 300px;
  height: 300px;
  border: 1px solid rgba(21, 94, 117, 0.16);
  border-radius: 50%;
  content: '';
  top: -100px;
  right: -90px;
}

.detail__merek {
  position: absolute;
  top: 18px;
  left: 18px;
  z-index: 1;
  border: 1px solid #b9d2d8;
  border-radius: var(--radius-pill);
  background: rgba(255, 255, 255, 0.72);
  color: var(--accent-hover);
  padding: 5px 10px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.detail__gambar {
  position: relative;
  z-index: 1;
  width: 100%;
  max-height: 360px;
  object-fit: contain;
}

.detail__galeri :deep(.phone-visual) {
  position: relative;
  z-index: 1;
}

.detail__garis {
  position: absolute;
  right: 32px;
  bottom: 32px;
  left: 32px;
  height: 1px;
  background: rgba(21, 94, 117, 0.18);
}

.detail__info {
  display: grid;
  align-content: start;
  gap: 20px;
}

.detail__judul {
  display: grid;
  gap: 8px;
}

.detail__judul h1 {
  max-width: 20ch;
  font-size: 30px;
  line-height: 1.15;
  text-wrap: balance;
}

.detail__harga {
  margin-top: 4px;
}

.detail__stok {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border: 1px solid var(--line);
  border-left: 3px solid var(--accent);
  border-radius: var(--radius-control);
  background: var(--surface);
  padding: 12px 14px;
}

.detail__jumlah {
  margin-top: 2px;
  color: var(--ink);
  font-size: 15px;
}

.detail__jumlah strong {
  font-family: var(--font-heading);
  font-size: 18px;
}

.detail__deskripsi {
  max-width: 64ch;
  color: var(--ink-muted);
}

.detail__aksi {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.detail__ket {
  color: var(--ink-muted);
  font-size: 12px;
}

.spesifikasi {
  margin-top: 48px;
  border-top: 1px solid var(--line);
  padding-top: 32px;
}

.spesifikasi__head {
  display: flex;
  flex-wrap: wrap;
  align-items: end;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
}

.spesifikasi__head > div {
  display: grid;
  gap: 6px;
}

.spesifikasi__head h2 {
  font-size: 24px;
}

.spesifikasi__catatan {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--ink-muted);
  font-size: 12px;
  font-weight: 600;
}

.spek-grid {
  display: grid;
  overflow: hidden;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
}

.spek-baris {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  border-bottom: 1px solid var(--line);
  padding: 14px 16px;
}

.spek-baris:last-child {
  border-bottom: 0;
}

.spek-baris dt {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--ink-muted);
  font-size: 14px;
}

.spek-baris dt svg {
  color: var(--accent);
}

.spek-baris dd {
  color: var(--ink);
  font-size: 14px;
  font-weight: 700;
  text-align: right;
}

.detail__memuat {
  display: grid;
  gap: 32px;
}

.detail__skeleton-gambar {
  min-height: 360px;
  border-radius: 28px;
}

.detail__skeleton-info {
  display: grid;
  align-content: start;
  gap: 14px;
}

.detail__skeleton-judul {
  width: 80%;
  height: 32px;
}

.detail__skeleton-tombol {
  width: 220px;
  height: 44px;
  margin-top: 8px;
  border-radius: var(--radius-control);
}

@media (min-width: 640px) {
  .spek-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .spek-baris {
    border-right: 1px solid var(--line);
    padding: 16px 20px;
  }

  .spek-baris:nth-child(2n) {
    border-right: 0;
  }

  .spek-baris:nth-last-child(-n + 2) {
    border-bottom: 0;
  }
}

@media (min-width: 900px) {
  .detail {
    padding-block: 32px 24px;
  }

  .detail__grid {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    align-items: start;
    gap: 56px;
  }

  .detail__galeri {
    min-height: 540px;
    padding: 48px 32px;
  }

  .detail__judul h1 {
    font-size: 36px;
  }

  .detail__info {
    padding-top: 24px;
  }

  .spesifikasi {
    margin-top: 64px;
    padding-top: 40px;
  }

  .detail__memuat {
    grid-template-columns: 1fr 1fr;
    gap: 56px;
  }

  .detail__skeleton-gambar {
    min-height: 540px;
  }

  .detail__skeleton-info {
    padding-top: 40px;
  }
}
</style>
