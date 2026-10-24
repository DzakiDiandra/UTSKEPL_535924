<script setup>
import { computed } from 'vue'
import { AlertTriangle, CheckCircle2, XCircle } from 'lucide-vue-next'
import { labelStok, statusStok } from '@/utils/produk'

const props = defineProps({
  stok: {
    type: Number,
    required: true,
  },
})

const status = computed(() => statusStok(props.stok))
const ikon = computed(() => {
  if (status.value === 'habis') return XCircle
  if (status.value === 'menipis') return AlertTriangle
  return CheckCircle2
})
</script>

<template>
  <span class="stock-badge" :class="`stock-badge--${status}`">
    <component :is="ikon" :size="14" aria-hidden="true" />
    {{ labelStok(stok) }}
  </span>
</template>
