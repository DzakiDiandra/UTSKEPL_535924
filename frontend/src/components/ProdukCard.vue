<script setup>
import { computed, ref } from 'vue'
import { colorwayMerek, ringkasSpek, urlGambarAman } from '@/utils/produk'
import { formatRupiah } from '@/utils/format'
import PhoneVisual from '@/components/PhoneVisual.vue'
import StokBadge from '@/components/StokBadge.vue'

const props = defineProps({
  produk: {
    type: Object,
    required: true,
  },
})

const gambarGagal = ref(false)
const gambar = computed(() => {
  if (gambarGagal.value) return ''
  return urlGambarAman(props.produk.gambar_url)
})
</script>

<template>
  <RouterLink
    class="produk-card"
    :to="{ name: 'produk-detail', params: { id: produk.id } }"
    :aria-label="`Lihat detail ${produk.nama}`"
  >
    <div class="produk-card__visual">
      <StokBadge class="produk-card__badge" :stok="produk.stok" />
      <img
        v-if="gambar"
        class="produk-card__gambar"
        :src="gambar"
        :alt="`Foto ${produk.nama}`"
        loading="lazy"
        decoding="async"
        @error="gambarGagal = true"
      />
      <PhoneVisual
        v-else
        :colorway="colorwayMerek(produk.merek)"
        :size="176"
        :nama="produk.nama"
        dekoratif
      />
    </div>

    <div class="produk-card__body">
      <p class="eyebrow">{{ produk.merek }}</p>
      <h3 class="produk-card__nama">{{ produk.nama }}</h3>
      <p class="produk-card__spek">{{ ringkasSpek(produk) }}</p>
      <p class="price">{{ formatRupiah(produk.harga) }}</p>
    </div>
  </RouterLink>
</template>

<style scoped>
.produk-card {
  display: flex;
  min-width: 0;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--line);
  border-radius: var(--radius-card);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
  color: var(--ink);
  transition:
    border-color 200ms ease,
    box-shadow 200ms ease;
}

.produk-card:hover {
  border-color: var(--accent);
  box-shadow: var(--shadow-hover);
  color: var(--ink);
}

.produk-card__visual {
  position: relative;
  display: grid;
  aspect-ratio: 4 / 5;
  place-items: center;
  background: var(--surface-muted);
  padding: 24px 14px 10px;
}

.produk-card__badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 1;
}

.produk-card__gambar {
  width: 100%;
  height: 100%;
  object-fit: contain;
  object-position: center;
}

.produk-card__body {
  display: grid;
  gap: 6px;
  padding: 16px;
}

.produk-card__nama {
  display: -webkit-box;
  overflow: hidden;
  font-size: 16px;
  line-height: 1.3;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
}

.produk-card__spek {
  color: var(--ink-muted);
  font-size: 14px;
}

.produk-card .price {
  margin-top: 2px;
}
</style>
