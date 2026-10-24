<script setup>
import { computed, onMounted, ref } from 'vue'
import {
  ArrowRight,
  MessageCircle,
  Package,
  RefreshCw,
  Tags,
  TriangleAlert,
  Wallet,
} from 'lucide-vue-next'
import api from '@/api'
import { formatRupiah } from '@/utils/format'
import { buatLinkWhatsapp } from '@/utils/produk'
import PhoneVisual from '@/components/PhoneVisual.vue'
import ProdukCard from '@/components/ProdukCard.vue'
import ProdukGridSkeleton from '@/components/ProdukGridSkeleton.vue'
import StokBadge from '@/components/StokBadge.vue'

const produk = ref([])
const merek = ref([])
const memuat = ref(true)
const galat = ref('')

const unggulan = computed(() => produk.value.slice(0, 4))
const sorotan = computed(() => produk.value[0])
const nomorWhatsapp = String(import.meta.env.VITE_WA_NUMBER ?? '').replace(/\D/g, '')
const tautanWhatsapp = computed(() => buatLinkWhatsapp(nomorWhatsapp, 'HP di LokaHP'))

function awalan(nama) {
  return String(nama ?? '?').charAt(0).toUpperCase()
}

async function muatBeranda() {
  memuat.value = true
  galat.value = ''

  const [hasilProduk, hasilMerek] = await Promise.allSettled([
    api.get('/produk'),
    api.get('/merek'),
  ])

  if (hasilProduk.status === 'fulfilled') {
    produk.value = hasilProduk.value.data.data ?? []
  } else {
    produk.value = []
    galat.value = 'Daftar HP belum bisa dimuat. Periksa koneksi lalu coba lagi.'
  }

  if (hasilMerek.status === 'fulfilled') {
    merek.value = hasilMerek.value.data.data ?? []
  } else {
    merek.value = [...new Set(produk.value.map((item) => item.merek))].sort()
  }

  memuat.value = false
}

onMounted(muatBeranda)
</script>

<template>
  <div>
    <section class="hero">
      <div class="container hero__grid">
        <div class="hero__copy">
          <p class="eyebrow">Toko HP pilihan</p>
          <h1 class="display hero__title">HP yang cocok, stoknya jelas.</h1>
          <p class="lede hero__lede">
            Bandingkan spesifikasi dan harga dalam satu tempat. Setiap unit punya keterangan stok
            yang bisa langsung Anda cek sebelum bertanya.
          </p>
          <div class="hero__actions">
            <RouterLink class="btn btn--primary" :to="{ name: 'katalog' }">
              Lihat katalog
              <ArrowRight :size="18" />
            </RouterLink>
            <a class="btn btn--secondary" href="#pilih-merek">Pilih berdasarkan merek</a>
          </div>
          <p class="hero__catatan">
            Harga dalam rupiah. Spesifikasi ditampilkan apa adanya, tanpa singkatan yang
            membingungkan.
          </p>
        </div>

        <div class="hero__visual">
          <span class="hero__label">Pilihan terbaru</span>
          <div class="hero__phones" aria-hidden="true">
            <div class="hero__phone-wrap hero__phone-wrap--belakang">
              <PhoneVisual view="back" colorway="sage" :size="170" />
            </div>
            <div class="hero__phone-wrap hero__phone-wrap--depan">
              <PhoneVisual colorway="terakota" :size="210" />
            </div>
          </div>
          <div v-if="sorotan" class="hero__note">
            <p class="eyebrow">Sedang dilihat</p>
            <strong>{{ sorotan.nama }}</strong>
            <span class="price">{{ formatRupiah(sorotan.harga) }}</span>
            <StokBadge :stok="sorotan.stok" />
          </div>
          <div v-else class="hero__note">
            <p class="eyebrow">Katalog LokaHP</p>
            <strong>Spesifikasi dan stok dalam satu halaman</strong>
          </div>
        </div>
      </div>
    </section>

    <section class="container facts" aria-label="Ringkasan katalog">
      <div class="fact">
        <span class="fact__ikon" aria-hidden="true"><Package :size="20" /></span>
        <p><strong>{{ memuat ? '—' : produk.length }}</strong><span>HP di katalog</span></p>
      </div>
      <div class="fact">
        <span class="fact__ikon" aria-hidden="true"><Tags :size="20" /></span>
        <p><strong>{{ memuat ? '—' : merek.length }}</strong><span>merek tersedia</span></p>
      </div>
      <div class="fact">
        <span class="fact__ikon" aria-hidden="true"><Wallet :size="20" /></span>
        <p><strong>Rupiah</strong><span>harga transparan</span></p>
      </div>
    </section>

    <section id="pilih-merek" class="section container">
      <div class="section-head">
        <div>
          <p class="eyebrow">Mulai dari yang familiar</p>
          <h2>Pilih merek favorit Anda</h2>
        </div>
        <RouterLink class="link-arrow" :to="{ name: 'katalog' }">
          Semua produk
          <ArrowRight :size="16" />
        </RouterLink>
      </div>

      <div v-if="memuat" class="merek-grid" aria-busy="true">
        <div v-for="nomor in 6" :key="nomor" class="skeleton merek-skeleton"></div>
      </div>
      <div v-else-if="merek.length" class="merek-grid">
        <RouterLink
          v-for="(namaMerek, indeks) in merek"
          :key="namaMerek"
          class="merek-card"
          :class="`merek-card--${(indeks % 4) + 1}`"
          :to="{ name: 'katalog', query: { merek: namaMerek } }"
        >
          <span class="merek-card__awalan" aria-hidden="true">{{ awalan(namaMerek) }}</span>
          <span class="merek-card__nama">{{ namaMerek }}</span>
          <ArrowRight :size="16" aria-hidden="true" />
        </RouterLink>
      </div>
      <p v-else class="muted">Belum ada merek yang bisa ditampilkan.</p>
    </section>

    <section class="section container">
      <div class="section-head">
        <div>
          <p class="eyebrow">Baru masuk</p>
          <h2>Pilihan yang bisa Anda lihat sekarang</h2>
        </div>
        <RouterLink class="link-arrow" :to="{ name: 'katalog' }">
          Lihat semua HP
          <ArrowRight :size="16" />
        </RouterLink>
      </div>

      <ProdukGridSkeleton v-if="memuat" :jumlah="4" />

      <div v-else-if="galat" class="state-panel state-panel--error" role="alert">
        <span class="state-panel__icon" aria-hidden="true"><TriangleAlert :size="22" /></span>
        <div class="state-panel__body">
          <h2>Katalog belum bisa dibuka</h2>
          <p>{{ galat }}</p>
        </div>
        <button class="btn btn--secondary" type="button" @click="muatBeranda">
          <RefreshCw :size="16" />
          Coba lagi
        </button>
      </div>

      <div v-else-if="unggulan.length" class="grid-produk">
        <ProdukCard v-for="item in unggulan" :key="item.id" :produk="item" />
      </div>

      <div v-else class="state-panel">
        <span class="state-panel__icon" aria-hidden="true"><Package :size="22" /></span>
        <div class="state-panel__body">
          <h2>Belum ada HP yang tampil</h2>
          <p>Produk sedang tidak tersedia untuk ditampilkan. Silakan cek kembali nanti.</p>
        </div>
      </div>
    </section>

    <section class="section section--tight container">
      <div class="alasan">
        <div class="alasan__intro">
          <p class="eyebrow">Sebelum memilih</p>
          <h2>Yang kami tampilkan, itu yang Anda dapat lihat.</h2>
          <p class="muted">
            Tidak perlu menebak dari nama seri. Buka detail produk untuk membaca angka penting dan
            sisa stoknya.
          </p>
        </div>
        <div class="alasan__daftar">
          <div class="alasan__item">
            <span class="alasan__nomor">01</span>
            <div>
              <h3>Spesifikasi terbaca</h3>
              <p>Chipset, RAM, penyimpanan, layar, baterai, dan kamera ditulis dalam satu tabel.</p>
            </div>
          </div>
          <div class="alasan__item">
            <span class="alasan__nomor">02</span>
            <div>
              <h3>Stok per unit</h3>
              <p>Jumlah stok terlihat langsung, lengkap dengan penanda tersedia, menipis, atau habis.</p>
            </div>
          </div>
          <div class="alasan__item">
            <span class="alasan__nomor">03</span>
            <div>
              <h3>Tanya tanpa berputar</h3>
              <p>Tombol WhatsApp membawa nama HP yang Anda tanyakan, jadi percakapan langsung jelas.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container">
      <div class="ajakan">
        <div>
          <p class="eyebrow">Masih membandingkan?</p>
          <h2>Lihat semua spesifikasi sebelum menentukan pilihan.</h2>
        </div>
        <div class="ajakan__aksi">
          <RouterLink class="btn btn--primary" :to="{ name: 'katalog' }">
            Buka katalog
            <ArrowRight :size="18" />
          </RouterLink>
          <a
            v-if="tautanWhatsapp"
            class="btn btn--outline-terang"
            :href="tautanWhatsapp"
            target="_blank"
            rel="noopener"
          >
            <MessageCircle :size="18" />
            Tanya lewat WhatsApp
          </a>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.hero {
  border-bottom: 1px solid var(--line);
  padding-block: 40px 64px;
}

.hero__grid {
  display: grid;
  gap: 40px;
}

.hero__copy {
  display: grid;
  align-content: center;
  gap: 20px;
}

.hero__title {
  max-width: 14ch;
  text-wrap: balance;
}

.hero__lede {
  max-width: 54ch;
}

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.hero__catatan {
  max-width: 52ch;
  border-left: 3px solid var(--accent);
  color: var(--ink-muted);
  padding-left: 12px;
  font-size: 13px;
  line-height: 1.55;
}

.hero__visual {
  position: relative;
  display: grid;
  min-height: 380px;
  place-items: center;
  overflow: hidden;
  border: 1px solid #c9dce1;
  border-radius: 28px;
  background: var(--accent-tint);
  padding: 40px 24px 100px;
}

.hero__visual::before,
.hero__visual::after {
  position: absolute;
  border: 1px solid rgba(21, 94, 117, 0.15);
  border-radius: 50%;
  content: '';
}

.hero__visual::before {
  width: 330px;
  height: 330px;
  top: -110px;
  right: -80px;
}

.hero__visual::after {
  width: 230px;
  height: 230px;
  bottom: -120px;
  left: -70px;
}

.hero__label {
  position: absolute;
  top: 20px;
  left: 20px;
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

.hero__phones {
  position: relative;
  z-index: 1;
  display: flex;
  width: min(100%, 320px);
  align-items: center;
  justify-content: center;
}

.hero__phone-wrap {
  flex-shrink: 0;
}

.hero__phone-wrap--belakang {
  width: 44%;
  margin-right: -14%;
  transform: rotate(-6deg);
}

.hero__phone-wrap--depan {
  z-index: 2;
  width: 55%;
}

.hero__note {
  position: absolute;
  z-index: 2;
  right: 16px;
  bottom: 16px;
  left: 16px;
  display: grid;
  gap: 4px;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
  padding: 14px;
}

.hero__note strong {
  display: -webkit-box;
  overflow: hidden;
  font-family: var(--font-heading);
  font-size: 15px;
  font-weight: 600;
  line-height: 1.3;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 1;
  line-clamp: 1;
}

.hero__note .stock-badge {
  justify-self: start;
  margin-top: 2px;
}

.facts {
  display: grid;
  border-bottom: 1px solid var(--line);
  padding-block: 20px;
}

.fact {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-block: 12px;
}

.fact + .fact {
  border-top: 1px solid var(--line);
}

.fact__ikon {
  display: grid;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 10px;
  background: var(--surface-muted);
  color: var(--accent);
}

.fact p {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 6px;
}

.fact strong {
  font-family: var(--font-heading);
  font-size: 18px;
  font-weight: 600;
}

.fact p span {
  color: var(--ink-muted);
  font-size: 13px;
}

.merek-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.merek-skeleton {
  height: 72px;
  border-radius: var(--radius-control);
}

.merek-card {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 10px;
  border: 1px solid var(--line);
  border-radius: var(--radius-control);
  background: var(--surface);
  color: var(--ink);
  padding: 12px;
  transition:
    border-color 180ms ease,
    background-color 180ms ease;
}

.merek-card:hover {
  border-color: var(--accent);
  background: var(--surface-muted);
  color: var(--ink);
}

.merek-card > svg {
  flex-shrink: 0;
  margin-left: auto;
  color: var(--ink-muted);
}

.merek-card__awalan {
  display: grid;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 8px;
  background: var(--surface-muted);
  color: var(--accent);
  font-family: var(--font-heading);
  font-size: 16px;
  font-weight: 600;
}

.merek-card--2 .merek-card__awalan {
  background: var(--accent-tint);
}

.merek-card__nama {
  overflow: hidden;
  font-size: 14px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.alasan {
  display: grid;
  gap: 28px;
  border-block: 1px solid var(--line);
  padding-block: 40px;
}

.alasan__intro {
  display: grid;
  max-width: 560px;
  gap: 10px;
}

.alasan__intro h2 {
  text-wrap: balance;
}

.alasan__daftar {
  display: grid;
}

.alasan__item {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 12px;
  border-top: 1px solid var(--line);
  padding-block: 20px;
}

.alasan__item:last-child {
  border-bottom: 1px solid var(--line);
}

.alasan__nomor {
  color: var(--accent);
  font-family: var(--font-heading);
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.alasan__item h3 {
  margin-bottom: 4px;
  font-size: 17px;
}

.alasan__item p {
  max-width: 52ch;
  color: var(--ink-muted);
  font-size: 14px;
}

.ajakan {
  display: grid;
  gap: 24px;
  border-radius: var(--radius-panel);
  background: var(--ink);
  color: var(--bg);
  padding: 28px;
}

.ajakan .eyebrow {
  color: #cfc7b8;
}

.ajakan h2 {
  max-width: 22ch;
  color: var(--bg);
  font-size: 24px;
}

.ajakan__aksi {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
}

.btn--outline-terang {
  border-color: #8f887a;
  color: var(--bg);
}

.btn--outline-terang:hover {
  border-color: var(--bg);
  background: rgba(255, 255, 255, 0.08);
  color: var(--bg);
}

@media (min-width: 640px) {
  .facts {
    grid-template-columns: repeat(3, 1fr);
    padding-block: 24px;
  }

  .fact {
    padding-block: 0;
    padding-inline: 20px;
  }

  .fact:first-child {
    padding-left: 0;
  }

  .fact + .fact {
    border-top: 0;
    border-left: 1px solid var(--line);
  }

  .merek-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .hero__note {
    right: auto;
    bottom: 20px;
    left: 20px;
    width: 250px;
  }

  .hero__visual {
    padding-bottom: 40px;
  }
}

@media (min-width: 900px) {
  .hero {
    padding-block: 64px 80px;
  }

  .hero__grid {
    grid-template-columns: 1.05fr 0.95fr;
    align-items: center;
    gap: 56px;
  }

  .hero__visual {
    min-height: 480px;
    padding: 40px 32px;
  }

  .hero__phones {
    width: 330px;
  }

  .merek-grid {
    grid-template-columns: repeat(6, minmax(0, 1fr));
  }

  .merek-card {
    flex-wrap: wrap;
    gap: 8px;
    padding: 14px;
  }

  .merek-card > svg {
    display: none;
  }

  .merek-card__nama {
    width: 100%;
    font-size: 13px;
  }

  .alasan {
    grid-template-columns: 0.9fr 1.1fr;
    gap: 56px;
    padding-block: 56px;
  }

  .ajakan {
    grid-template-columns: 1.2fr auto;
    align-items: end;
    padding: 40px;
  }

  .ajakan h2 {
    font-size: 30px;
  }
}
</style>
