<script setup>
import { MessageCircle, Smartphone } from 'lucide-vue-next'

const tahun = new Date().getFullYear()
const nomorWhatsapp = String(import.meta.env.VITE_WA_NUMBER ?? '').replace(/\D/g, '')
const tautanWhatsapp = nomorWhatsapp ? `https://wa.me/${nomorWhatsapp}` : ''
</script>

<template>
  <footer class="footer">
    <div class="container footer__grid">
      <div class="footer__brand">
        <RouterLink class="footer__logo" :to="{ name: 'beranda' }">
          <span class="footer__mark" aria-hidden="true">
            <Smartphone :size="18" />
          </span>
          <strong>LokaHP</strong>
        </RouterLink>
        <p>Spesifikasi jelas, stok terlihat, dan harga tanpa tebak-tebakan.</p>
      </div>

      <nav class="footer__nav" aria-label="Navigasi footer">
        <p class="eyebrow">Jelajahi</p>
        <RouterLink :to="{ name: 'beranda' }">Beranda</RouterLink>
        <RouterLink :to="{ name: 'katalog' }">Katalog HP</RouterLink>
        <RouterLink :to="{ name: 'admin-login' }">Admin</RouterLink>
      </nav>

      <div class="footer__kontak">
        <p class="eyebrow">Butuh bantuan?</p>
        <p>Tim kami siap membantu memilih HP yang sesuai kebutuhan.</p>
        <a v-if="tautanWhatsapp" class="footer__wa" :href="tautanWhatsapp" target="_blank" rel="noopener">
          <MessageCircle :size="18" />
          Chat lewat WhatsApp
        </a>
        <RouterLink v-else class="footer__wa" :to="{ name: 'katalog' }">
          <MessageCircle :size="18" />
          Lihat katalog
        </RouterLink>
      </div>
    </div>

    <div class="container footer__bottom">
      <p>© {{ tahun }} LokaHP. Semua harga dalam rupiah.</p>
      <p>Toko pilihan untuk kebutuhan HP sehari-hari.</p>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  margin-top: 64px;
  border-top: 1px solid var(--line);
  background: var(--surface-muted);
  padding-block: 40px 24px;
}

.footer__grid {
  display: grid;
  gap: 32px;
}

.footer__brand {
  display: grid;
  max-width: 360px;
  gap: 10px;
}

.footer__logo {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--ink);
}

.footer__mark {
  display: grid;
  width: 32px;
  height: 32px;
  place-items: center;
  border-radius: 8px;
  background: var(--accent);
  color: var(--on-accent);
}

.footer__logo strong {
  font-family: var(--font-heading);
  font-size: 19px;
  font-weight: 600;
}

.footer__brand p,
.footer__kontak p:not(.eyebrow) {
  color: var(--ink-muted);
  font-size: 14px;
}

.footer__nav {
  display: grid;
  align-content: start;
  gap: 8px;
}

.footer__nav .eyebrow,
.footer__kontak .eyebrow {
  margin-bottom: 2px;
}

.footer__nav a {
  justify-self: start;
  color: var(--ink);
  font-size: 14px;
  font-weight: 600;
}

.footer__nav a:hover {
  color: var(--accent);
}

.footer__kontak {
  display: grid;
  align-content: start;
  gap: 8px;
}

.footer__wa {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  justify-self: start;
  gap: 8px;
  border: 1px solid var(--control-line);
  border-radius: var(--radius-control);
  background: var(--surface);
  color: var(--ink);
  padding: 8px 12px;
  font-size: 14px;
  font-weight: 700;
}

.footer__wa:hover {
  border-color: var(--accent);
  background: var(--accent-tint);
  color: var(--accent-hover);
}

.footer__bottom {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 6px 24px;
  margin-top: 32px;
  border-top: 1px solid var(--line);
  padding-top: 20px;
}

.footer__bottom p {
  color: var(--ink-muted);
  font-size: 12px;
}

@media (min-width: 768px) {
  .footer {
    margin-top: 96px;
    padding-top: 56px;
  }

  .footer__grid {
    grid-template-columns: 1.5fr 0.8fr 1.2fr;
    gap: 48px;
  }
}
</style>
