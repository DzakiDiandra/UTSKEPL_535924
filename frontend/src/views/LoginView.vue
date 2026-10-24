<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Check,
  Eye,
  EyeOff,
  LoaderCircle,
  LockKeyhole,
  Smartphone,
  TriangleAlert,
} from 'lucide-vue-next'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import PhoneVisual from '@/components/PhoneVisual.vue'

const route = useRoute()
const router = useRouter()
const { login } = useAuth()
const { tampilkanToast } = useToast()

const form = reactive({
  email: '',
  password: '',
})
const errors = reactive({
  email: '',
  password: '',
})
const galat = ref('')
const memuat = ref(false)
const lihatSandi = ref(false)

function validasi() {
  errors.email = ''
  errors.password = ''

  const email = form.email.trim()

  if (!email) {
    errors.email = 'Email wajib diisi.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    errors.email = 'Masukkan alamat email yang valid.'
  }

  if (!form.password) {
    errors.password = 'Password wajib diisi.'
  }

  const pertama = ['email', 'password'].find((field) => errors[field])

  if (pertama) {
    document.getElementById(pertama)?.focus()
    return false
  }

  return true
}

function tujuanSetelahMasuk(akun) {
  if (!akun.is_admin) return { name: 'katalog' }

  const tujuan = route.query.redirect

  if (typeof tujuan === 'string' && tujuan.startsWith('/admin') && tujuan !== '/login') {
    return tujuan
  }

  return { name: 'admin-produk' }
}

async function masuk() {
  galat.value = ''

  if (!validasi() || memuat.value) return

  memuat.value = true

  try {
    const akun = await login(form.email.trim(), form.password)

    tampilkanToast(`Selamat datang, ${akun.name}.`, 'success')
    await router.replace(tujuanSetelahMasuk(akun))
  } catch (error) {
    if (error.response?.status === 401) {
      galat.value = 'Email atau password tidak cocok. Periksa kembali lalu coba lagi.'
    } else if (error.response?.status === 422) {
      galat.value = 'Email dan password belum lengkap. Periksa kembali isian Anda.'
    } else if (!error.response) {
      galat.value = 'Server belum bisa dihubungi. Pastikan backend berjalan lalu coba lagi.'
    } else {
      galat.value = 'Login belum bisa diproses. Silakan coba lagi.'
    }
  } finally {
    memuat.value = false
  }
}
</script>

<template>
  <div class="login">
    <div class="login__kartu">
      <section class="login__intro">
        <div class="login__brand">
          <span class="login__brand-mark" aria-hidden="true">
            <Smartphone :size="20" />
          </span>
          <span>
            <strong>LokaHP</strong>
            <small>Toko HP</small>
          </span>
        </div>

        <div class="login__salinan">
          <p class="eyebrow">Ruang akun</p>
          <h1>Satu akun untuk katalog dan pengelolaan.</h1>
          <p>
            Masuk untuk melihat katalog HP. Akun admin dapat menambah, mengubah, dan menghapus
            produk dari panel.
          </p>
        </div>

        <ul class="login__daftar">
          <li><Check :size="16" aria-hidden="true" /> Pelanggan melihat katalog dan detail HP</li>
          <li><Check :size="16" aria-hidden="true" /> Admin mengelola produk dan stok</li>
          <li><Check :size="16" aria-hidden="true" /> Sesi memakai token Sanctum</li>
        </ul>

        <div class="login__visual" aria-hidden="true">
          <PhoneVisual colorway="terakota" :size="170" />
        </div>
      </section>

      <section class="login__form-panel">
        <div class="login__form-head">
          <p class="eyebrow">Masuk</p>
          <h2>Masuk ke LokaHP</h2>
          <p>Gunakan akun admin atau pelanggan untuk melanjutkan.</p>
        </div>

        <div v-if="galat" class="login__galat" role="alert">
          <TriangleAlert :size="18" aria-hidden="true" />
          <p>{{ galat }}</p>
        </div>

        <form class="login__form" novalidate @submit.prevent="masuk">
          <div class="field">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              class="input"
              :class="{ 'input--error': errors.email }"
              type="email"
              name="email"
              autocomplete="username"
              placeholder="nama@email.com"
              :aria-invalid="Boolean(errors.email)"
              :aria-describedby="errors.email ? 'error-email' : undefined"
            />
            <p v-if="errors.email" id="error-email" class="field-error">
              <TriangleAlert :size="14" aria-hidden="true" />
              {{ errors.email }}
            </p>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <div class="login__sandi">
              <LockKeyhole class="login__sandi-ikon" :size="18" aria-hidden="true" />
              <input
                id="password"
                v-model="form.password"
                class="input"
                :class="{ 'input--error': errors.password }"
                :type="lihatSandi ? 'text' : 'password'"
                name="password"
                autocomplete="current-password"
                placeholder="Password akun"
                :aria-invalid="Boolean(errors.password)"
                :aria-describedby="errors.password ? 'error-password' : undefined"
              />
              <button
                class="login__sandi-tombol"
                type="button"
                :aria-label="lihatSandi ? 'Sembunyikan password' : 'Tampilkan password'"
                @click="lihatSandi = !lihatSandi"
              >
                <EyeOff v-if="lihatSandi" :size="18" />
                <Eye v-else :size="18" />
              </button>
            </div>
            <p v-if="errors.password" id="error-password" class="field-error">
              <TriangleAlert :size="14" aria-hidden="true" />
              {{ errors.password }}
            </p>
          </div>

          <button class="btn btn--primary btn--block" type="submit" :disabled="memuat">
            <LoaderCircle v-if="memuat" class="spin" :size="18" />
            {{ memuat ? 'Memeriksa akun…' : 'Masuk' }}
          </button>
        </form>
      </section>
    </div>
  </div>
</template>

<style scoped>
.login {
  display: grid;
  min-height: 100vh;
  place-items: center;
  background: var(--bg);
  padding: 16px;
}

.login__kartu {
  display: grid;
  width: min(100%, 1040px);
  overflow: hidden;
  border: 1px solid var(--line);
  border-radius: 24px;
  background: var(--surface);
  box-shadow: 0 32px 80px -64px rgba(23, 21, 15, 0.5);
}

.login__intro {
  position: relative;
  display: grid;
  gap: 20px;
  overflow: hidden;
  background: var(--ink);
  color: var(--bg);
  padding: 28px;
}

.login__brand {
  display: inline-flex;
  width: fit-content;
  align-items: center;
  gap: 10px;
  color: var(--bg);
}

.login__brand:hover {
  color: var(--bg);
}

.login__brand-mark {
  display: grid;
  width: 38px;
  height: 38px;
  place-items: center;
  border-radius: 10px;
  background: var(--accent);
  color: var(--on-accent);
}

.login__brand strong {
  display: block;
  font-family: var(--font-heading);
  font-size: 19px;
  font-weight: 600;
  line-height: 1.1;
}

.login__brand small {
  display: block;
  color: #bdb5a6;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.login__salinan {
  display: grid;
  gap: 10px;
}

.login__salinan .eyebrow {
  color: #bdb5a6;
}

.login__salinan h1 {
  max-width: 14ch;
  color: var(--bg);
  font-size: 30px;
  line-height: 1.12;
  text-wrap: balance;
}

.login__salinan p:not(.eyebrow) {
  max-width: 40ch;
  color: #d8d1c4;
  font-size: 14px;
}

.login__daftar {
  display: none;
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.login__daftar li {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #e8e2d8;
  font-size: 13px;
}

.login__daftar svg {
  flex-shrink: 0;
  color: #8fc5d0;
}

.login__visual {
  display: none;
  align-self: end;
  justify-self: end;
  width: 190px;
  margin: -20px -30px -56px 0;
  transform: rotate(8deg);
}

.login__form-panel {
  display: grid;
  align-content: center;
  gap: 20px;
  padding: 28px;
}

.login__form-head {
  display: grid;
  gap: 6px;
}

.login__form-head h2 {
  font-size: 24px;
}

.login__form-head p:not(.eyebrow) {
  color: var(--ink-muted);
  font-size: 14px;
}

.login__galat {
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

.login__galat svg {
  flex-shrink: 0;
  margin-top: 1px;
}

.login__form {
  display: grid;
  gap: 16px;
}

.login__sandi {
  position: relative;
}

.login__sandi-ikon {
  position: absolute;
  top: 50%;
  left: 14px;
  color: var(--ink-muted);
  pointer-events: none;
  transform: translateY(-50%);
}

.login__sandi .input {
  padding-right: 46px;
  padding-left: 42px;
}

.login__sandi-tombol {
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

.login__sandi-tombol:hover {
  background: var(--surface-muted);
  color: var(--ink);
}

.login__kembali {
  display: inline-flex;
  align-items: center;
  justify-self: start;
  gap: 6px;
  color: var(--ink-muted);
  font-size: 13px;
  font-weight: 700;
}

.login__kembali:hover {
  color: var(--accent);
}

@media (min-width: 720px) {
  .login {
    padding: 32px;
  }

  .login__intro {
    padding: 36px;
  }

  .login__form-panel {
    padding: 40px;
  }
}

@media (min-width: 900px) {
  .login__kartu {
    grid-template-columns: 0.92fr 1.08fr;
  }

  .login__intro {
    min-height: 620px;
    align-content: start;
    padding: 44px;
  }

  .login__salinan {
    margin-top: 32px;
  }

  .login__salinan h1 {
    font-size: 38px;
  }

  .login__daftar {
    display: grid;
    margin-top: 8px;
  }

  .login__visual {
    display: block;
  }

  .login__form-panel {
    padding: 56px;
  }
}
</style>
