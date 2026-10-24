<script setup>
defineProps({
  jumlah: {
    type: Number,
    default: 6,
  },
})
</script>

<template>
  <div class="grid-produk" aria-busy="true">
    <span class="visually-hidden">Memuat produk…</span>
    <div v-for="nomor in jumlah" :key="nomor" class="skeleton-card" aria-hidden="true">
      <div class="skeleton skeleton-card__visual"></div>
      <div class="skeleton-card__body">
        <div class="skeleton skeleton-line skeleton-line--short"></div>
        <div class="skeleton skeleton-line"></div>
        <div class="skeleton skeleton-line skeleton-line--short"></div>
        <div class="skeleton skeleton-line skeleton-line--price"></div>
      </div>
    </div>
  </div>
</template>
