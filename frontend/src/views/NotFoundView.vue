<script setup>
import { ArrowLeft, Compass } from 'lucide-vue-next'
</script>

<template>
  <section class="container not-found">
    <div class="not-found__visual" aria-hidden="true">
      <Compass :size="48" />
      <span>404</span>
    </div>
    <div class="not-found__body">
      <p class="eyebrow">Halaman tidak ditemukan</p>
      <h1>Sepertinya alamat ini salah ketik.</h1>
      <p class="lede">
        Halaman yang Anda cari tidak tersedia. Kembali ke beranda atau lihat katalog HP yang sedang
        tersedia.
      </p>
      <div class="not-found__aksi">
        <RouterLink class="btn btn--primary" :to="{ name: 'beranda' }">
          <ArrowLeft :size="18" />
          Kembali ke beranda
        </RouterLink>
        <RouterLink class="btn btn--secondary" :to="{ name: 'katalog' }">Lihat katalog</RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.not-found {
  display: grid;
  max-width: 880px;
  gap: 24px;
  padding-block: 64px 96px;
}

.not-found__visual {
  display: flex;
  width: fit-content;
  align-items: center;
  gap: 12px;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface-muted);
  color: var(--accent);
  padding: 20px 24px;
}

.not-found__visual span {
  color: var(--ink);
  font-family: var(--font-heading);
  font-size: 32px;
  font-weight: 600;
  letter-spacing: -0.04em;
}

.not-found__body {
  display: grid;
  gap: 12px;
}

.not-found__body h1 {
  max-width: 18ch;
}

.not-found__body .lede {
  max-width: 58ch;
}

.not-found__aksi {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 8px;
}

@media (min-width: 900px) {
  .not-found {
    grid-template-columns: auto 1fr;
    align-items: start;
    gap: 48px;
    padding-block: 96px 120px;
  }
}
</style>
