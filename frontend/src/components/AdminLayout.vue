<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowUpRight, LayoutDashboard, LogOut, Smartphone } from 'lucide-vue-next'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const { user, logout } = useAuth()
const { tampilkanToast } = useToast()

const sedangKeluar = ref(false)
const nama = computed(() => user.value?.name ?? 'Admin')

async function keluar() {
  sedangKeluar.value = true

  try {
    await logout()
    tampilkanToast('Anda sudah keluar dari panel admin.', 'info')
    await router.push({ name: 'admin-login' })
  } finally {
    sedangKeluar.value = false
  }
}
</script>

<template>
  <div class="admin-shell">
    <header class="admin-bar">
      <div class="admin-bar__inner">
        <div class="admin-bar__kiri">
          <RouterLink class="admin-brand" :to="{ name: 'admin-produk' }">
            <span class="admin-brand__mark" aria-hidden="true">
              <Smartphone :size="18" />
            </span>
            <span>
              <strong>LokaHP</strong>
              <small>Panel admin</small>
            </span>
          </RouterLink>

          <nav class="admin-nav" aria-label="Navigasi admin">
            <RouterLink :to="{ name: 'admin-produk' }">
              <LayoutDashboard :size="16" />
              Produk
            </RouterLink>
          </nav>
        </div>

        <div class="admin-bar__kanan">
          <span class="admin-user">{{ nama }}</span>
          <RouterLink
            class="btn btn--ghost btn--small"
            :to="{ name: 'beranda' }"
            aria-label="Lihat toko"
          >
            <ArrowUpRight :size="16" />
            <span class="admin-label">Lihat toko</span>
          </RouterLink>
          <button
            class="btn btn--secondary btn--small"
            type="button"
            :disabled="sedangKeluar"
            :aria-label="sedangKeluar ? 'Sedang keluar' : 'Keluar'"
            @click="keluar"
          >
            <LogOut :size="16" />
            <span class="admin-label">{{ sedangKeluar ? 'Keluar…' : 'Keluar' }}</span>
          </button>
        </div>
      </div>
    </header>

    <div class="admin-content">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.admin-shell {
  min-height: 100vh;
  background: var(--bg);
}

.admin-bar {
  position: sticky;
  top: 0;
  z-index: 30;
  border-bottom: 1px solid var(--line);
  background: var(--surface);
}

.admin-bar__inner {
  display: flex;
  width: min(calc(100% - 32px), var(--container));
  min-height: 64px;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-inline: auto;
}

.admin-bar__kiri,
.admin-bar__kanan {
  display: flex;
  align-items: center;
  gap: 8px;
}

.admin-brand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--ink);
}

.admin-brand__mark {
  display: grid;
  width: 32px;
  height: 32px;
  place-items: center;
  border-radius: 8px;
  background: var(--accent);
  color: var(--on-accent);
}

.admin-brand strong {
  display: block;
  font-family: var(--font-heading);
  font-size: 16px;
  font-weight: 600;
  line-height: 1.1;
}

.admin-brand small {
  color: var(--ink-muted);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.admin-nav {
  display: none;
  margin-left: 12px;
  border-left: 1px solid var(--line);
  padding-left: 12px;
}

.admin-nav a {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border-radius: 6px;
  color: var(--ink-muted);
  padding: 6px 8px;
  font-size: 14px;
  font-weight: 700;
}

.admin-nav a:hover {
  background: var(--surface-muted);
  color: var(--ink);
}

.admin-nav a.router-link-exact-active {
  background: var(--accent-tint);
  color: var(--accent);
}

.admin-label {
  display: none;
}

.admin-user {
  display: none;
  max-width: 160px;
  overflow: hidden;
  color: var(--ink-muted);
  font-size: 13px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.admin-content {
  width: min(calc(100% - 32px), var(--container));
  margin-inline: auto;
  padding-block: 24px 64px;
}

@media (min-width: 640px) {
  .admin-label {
    display: inline;
  }
}

@media (min-width: 768px) {
  .admin-bar__inner {
    width: min(calc(100% - 48px), var(--container));
  }

  .admin-nav {
    display: flex;
  }

  .admin-user {
    display: block;
  }

  .admin-content {
    width: min(calc(100% - 48px), var(--container));
    padding-block: 40px 96px;
  }
}
</style>
