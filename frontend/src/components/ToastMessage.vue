<script setup>
import { computed } from 'vue'
import { AlertCircle, CheckCircle2, Info, X } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'

const { toast, tutupToast, tahanToast, lanjutToast } = useToast()

const ikon = computed(() => {
  if (toast.value?.type === 'danger') return AlertCircle
  if (toast.value?.type === 'info') return Info
  return CheckCircle2
})

const peran = computed(() => (toast.value?.type === 'danger' ? 'alert' : 'status'))
</script>

<template>
  <Teleport to="body">
    <Transition name="toast">
      <div
        v-if="toast"
        :key="toast.id"
        class="toast"
        :class="`toast--${toast.type}`"
        :role="peran"
        aria-atomic="true"
        @mouseenter="tahanToast"
        @mouseleave="lanjutToast"
        @focusin="tahanToast"
        @focusout="lanjutToast"
      >
        <component :is="ikon" class="toast__ikon" :size="20" aria-hidden="true" />
        <p class="toast__pesan">{{ toast.message }}</p>
        <button class="toast__tutup" type="button" aria-label="Tutup notifikasi" @click="tutupToast">
          <X :size="18" />
        </button>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.toast {
  position: fixed;
  z-index: 120;
  right: 16px;
  bottom: 16px;
  left: 16px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  border: 1px solid var(--line);
  border-left: 4px solid var(--success);
  border-radius: var(--radius-control);
  background: var(--surface);
  padding: 12px 12px 12px 14px;
  box-shadow: var(--shadow-dialog);
}

.toast--info {
  border-left-color: var(--accent);
}

.toast--danger {
  border-left-color: var(--danger);
}

.toast__ikon {
  flex-shrink: 0;
  margin-top: 2px;
  color: var(--success);
}

.toast--info .toast__ikon {
  color: var(--accent);
}

.toast--danger .toast__ikon {
  color: var(--danger);
}

.toast__pesan {
  flex: 1;
  color: var(--ink);
  font-size: 14px;
  font-weight: 600;
  line-height: 1.45;
}

.toast__tutup {
  display: grid;
  width: 30px;
  height: 30px;
  flex-shrink: 0;
  place-items: center;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: var(--ink-muted);
}

.toast__tutup:hover {
  background: var(--surface-muted);
  color: var(--ink);
}

.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 180ms ease,
    transform 180ms ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

@media (min-width: 640px) {
  .toast {
    right: 24px;
    bottom: 24px;
    left: auto;
    width: min(380px, calc(100% - 48px));
  }
}
</style>
