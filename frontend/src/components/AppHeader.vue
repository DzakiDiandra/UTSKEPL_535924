<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  LayoutDashboard,
  LogIn,
  LogOut,
  Menu,
  Search,
  Smartphone,
  X,
} from 'lucide-vue-next'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const { isAuthenticated, isAdmin, user, logout } = useAuth()
const { tampilkanToast } = useToast()

const menuTerbuka = ref(false)
const sedangKeluar = ref(false)

const namaDepan = computed(() => user.value?.name?.split(' ')[0] ?? '')

watch(
  () => route.fullPath,
  () => {
    menuTerbuka.value = false
  },
)

watch(isAuthenticated, (masuk) => {
  if (!masuk) menuTerbuka.value = false
})

async function keluar() {
  sedangKeluar.value = true

  try {
    await logout()
    tampilkanToast('Anda sudah keluar dari akun.', 'info')
    await router.push({ name: 'login' })
  } finally {
    sedangKeluar.value = false
  }
}
</script>

<template>
  <header class="header">
    <div class="container header__inner">
      <RouterLink class="brand" :to="{ name: 'beranda' }" aria-label="LokaHP, kembali ke beranda">
        <span class="brand__mark" aria-hidden="true">
          <Smartphone :size="20" />
        </span>
        <span class="brand__text">
          <strong>LokaHP</strong>
          <small>HP pilihan, harga jelas</small>
        </span>
      </RouterLink>

      <nav class="header__nav" aria-label="Navigasi utama">
        <RouterLink class="nav-link" :to="{ name: 'beranda' }">Beranda</RouterLink>
        <RouterLink class="nav-link" :to="{ name: 'katalog' }">Katalog</RouterLink>
      </nav>

      <div class="header__actions">
        <RouterLink
          class="btn btn--ghost btn--icon header__cari"
          :to="{ name: 'katalog' }"
          aria-label="Cari HP di katalog"
        >
          <Search :size="20" />
        </RouterLink>

        <template v-if="isAuthenticated">
          <span v-if="isAdmin" class="header__user">{{ namaDepan }}</span>
          <RouterLink
            v-if="isAdmin"
            class="btn btn--secondary btn--small header__admin"
            :to="{ name: 'admin-produk' }"
          >
            <LayoutDashboard :size="16" />
            Admin
          </RouterLink>
          <button
            class="btn btn--ghost btn--small header__keluar"
            type="button"
            :disabled="sedangKeluar"
            @click="keluar"
          >
            <LogOut :size="16" />
            {{ sedangKeluar ? 'Keluar…' : 'Keluar' }}
          </button>
        </template>

        <RouterLink v-else class="btn btn--secondary btn--small header__masuk" :to="{ name: 'login' }">
          <LogIn :size="16" />
          Masuk
        </RouterLink>

        <button
          class="btn btn--ghost btn--icon header__menu"
          type="button"
          :aria-expanded="menuTerbuka"
          aria-controls="menu-utama"
          :aria-label="menuTerbuka ? 'Tutup menu' : 'Buka menu'"
          @click="menuTerbuka = !menuTerbuka"
        >
          <X v-if="menuTerbuka" :size="22" />
          <Menu v-else :size="22" />
        </button>
      </div>
    </div>

    <div v-if="menuTerbuka" id="menu-utama" class="header__mobile">
      <nav class="header__mobile-nav" aria-label="Navigasi seluler">
        <RouterLink :to="{ name: 'beranda' }">Beranda</RouterLink>
        <RouterLink :to="{ name: 'katalog' }">Katalog HP</RouterLink>
        <RouterLink v-if="isAdmin" :to="{ name: 'admin-produk' }">Panel admin</RouterLink>
        <RouterLink v-if="!isAuthenticated" :to="{ name: 'login' }">Masuk</RouterLink>
      </nav>
      <button
        v-if="isAuthenticated"
        class="btn btn--secondary btn--block"
        type="button"
        :disabled="sedangKeluar"
        @click="keluar"
      >
        <LogOut :size="18" />
        {{ sedangKeluar ? 'Keluar…' : 'Keluar dari akun' }}
      </button>
    </div>
  </header>
</template>

<style scoped>
.header {
  position: sticky;
  top: 0;
  z-index: 40;
  border-bottom: 1px solid var(--line);
  background: var(--bg);
}

.header__inner {
  display: flex;
  min-height: 72px;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.brand {
  display: inline-flex;
  min-width: 0;
  align-items: center;
  gap: 10px;
  color: var(--ink);
}

.brand:hover {
  color: var(--ink);
}

.brand__mark {
  display: grid;
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 10px;
  background: var(--accent);
  color: var(--on-accent);
}

.brand__text {
  display: grid;
  gap: 1px;
  line-height: 1.15;
}

.brand__text strong {
  font-family: var(--font-heading);
  font-size: 20px;
  font-weight: 600;
  letter-spacing: -0.03em;
}

.brand__text small {
  display: none;
  color: var(--ink-muted);
  font-size: 11px;
  font-weight: 600;
}

.header__nav {
  display: none;
  align-items: center;
  gap: 4px;
}

.nav-link {
  border-radius: var(--radius-xs);
  color: var(--ink-muted);
  padding: 8px 12px;
  font-size: 14px;
  font-weight: 700;
}

.nav-link:hover {
  background: var(--surface-muted);
  color: var(--ink);
}

.nav-link.router-link-exact-active {
  color: var(--accent);
}

.header__actions {
  display: flex;
  align-items: center;
  gap: 4px;
}

.header__cari,
.header__masuk,
.header__admin,
.header__keluar,
.header__user {
  display: none;
}

.header__menu {
  margin-left: 2px;
}

.header__mobile {
  display: grid;
  gap: 16px;
  border-top: 1px solid var(--line);
  background: var(--surface);
  padding: 16px;
}

.header__mobile-nav {
  display: grid;
}

.header__mobile-nav a {
  border-bottom: 1px solid var(--line);
  color: var(--ink);
  padding: 12px 4px;
  font-weight: 700;
}

.header__mobile-nav a:last-child {
  border-bottom: 0;
}

.header__mobile-nav a.router-link-exact-active {
  color: var(--accent);
}

@media (min-width: 480px) {
  .brand__text small {
    display: block;
  }
}

@media (min-width: 900px) {
  .header__nav {
    display: flex;
  }

  .header__cari,
  .header__masuk,
  .header__admin,
  .header__keluar,
  .header__user {
    display: inline-flex;
  }

  .header__user {
    max-width: 120px;
    overflow: hidden;
    color: var(--ink-muted);
    padding-inline: 8px;
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .header__menu,
  .header__mobile {
    display: none;
  }
}
</style>
