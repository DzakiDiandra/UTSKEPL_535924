<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { LoaderCircle, TriangleAlert, X } from 'lucide-vue-next'

const props = defineProps({
  terbuka: {
    type: Boolean,
    default: false,
  },
  judul: {
    type: String,
    default: 'Konfirmasi tindakan',
  },
  deskripsi: {
    type: String,
    default: '',
  },
  labelKonfirmasi: {
    type: String,
    default: 'Lanjutkan',
  },
  labelBatal: {
    type: String,
    default: 'Batal',
  },
  sedangProses: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['konfirmasi', 'batal'])

const dialog = ref(null)
const tombolBatal = ref(null)
let overflowAwal = ''

function tutup() {
  if (props.sedangProses) return
  emit('batal')
}

function tanganiTombol(event) {
  if (event.key === 'Escape') {
    event.preventDefault()
    tutup()
    return
  }

  if (event.key !== 'Tab' || !dialog.value) return

  const fokusable = dialog.value.querySelectorAll(
    'button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex="-1"])',
  )

  if (!fokusable.length) {
    event.preventDefault()
    dialog.value.focus()
    return
  }

  const pertama = fokusable[0]
  const terakhir = fokusable[fokusable.length - 1]

  if (event.shiftKey && document.activeElement === pertama) {
    event.preventDefault()
    terakhir.focus()
  } else if (!event.shiftKey && document.activeElement === terakhir) {
    event.preventDefault()
    pertama.focus()
  }
}

watch(
  () => props.terbuka,
  async (terbuka) => {
    if (terbuka) {
      overflowAwal = document.body.style.overflow
      document.body.style.overflow = 'hidden'
      await nextTick()
      tombolBatal.value?.focus()
    } else {
      document.body.style.overflow = overflowAwal
    }
  },
)

onBeforeUnmount(() => {
  document.body.style.overflow = overflowAwal
})
</script>

<template>
  <Teleport to="body">
    <Transition name="dialog">
      <div v-if="terbuka" class="dialog__backdrop" @click.self="tutup">
        <div
          ref="dialog"
          class="dialog"
          role="alertdialog"
          aria-modal="true"
          aria-labelledby="dialog-judul"
          aria-describedby="dialog-deskripsi"
          tabindex="-1"
          @keydown="tanganiTombol"
        >
          <button class="dialog__tutup" type="button" aria-label="Tutup dialog" @click="tutup">
            <X :size="18" />
          </button>

          <span class="dialog__ikon" aria-hidden="true">
            <TriangleAlert :size="22" />
          </span>
          <h2 id="dialog-judul">{{ judul }}</h2>
          <p id="dialog-deskripsi">{{ deskripsi }}</p>

          <div class="dialog__aksi">
            <button ref="tombolBatal" class="btn btn--secondary" type="button" :disabled="sedangProses" @click="tutup">
              {{ labelBatal }}
            </button>
            <button
              class="btn btn--danger"
              type="button"
              :disabled="sedangProses"
              @click="emit('konfirmasi')"
            >
              <LoaderCircle v-if="sedangProses" class="spin" :size="18" />
              {{ sedangProses ? 'Menghapus…' : labelKonfirmasi }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.dialog__backdrop {
  position: fixed;
  z-index: 110;
  inset: 0;
  display: grid;
  place-items: center;
  overflow-y: auto;
  background: var(--overlay);
  padding: 16px;
}

.dialog {
  position: relative;
  display: grid;
  width: min(100%, 440px);
  gap: 12px;
  border: 1px solid var(--line);
  border-radius: var(--radius-panel);
  background: var(--surface);
  padding: 28px;
  box-shadow: var(--shadow-dialog);
}

.dialog__tutup {
  position: absolute;
  top: 12px;
  right: 12px;
  display: grid;
  width: 36px;
  height: 36px;
  place-items: center;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: var(--ink-muted);
}

.dialog__tutup:hover {
  background: var(--surface-muted);
  color: var(--ink);
}

.dialog__ikon {
  display: grid;
  width: 44px;
  height: 44px;
  place-items: center;
  border-radius: 50%;
  background: var(--danger-bg);
  color: var(--danger);
}

.dialog h2 {
  padding-right: 32px;
  font-size: 22px;
}

.dialog p {
  color: var(--ink-muted);
}

.dialog__aksi {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 8px;
}

.dialog-enter-active,
.dialog-leave-active {
  transition: opacity 160ms ease;
}

.dialog-enter-active .dialog,
.dialog-leave-active .dialog {
  transition: transform 160ms ease;
}

.dialog-enter-from,
.dialog-leave-to {
  opacity: 0;
}

.dialog-enter-from .dialog,
.dialog-leave-to .dialog {
  transform: translateY(8px);
}
</style>
