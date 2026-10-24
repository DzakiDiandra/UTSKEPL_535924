<script setup>
import { computed } from 'vue'

const props = defineProps({
  view: {
    type: String,
    default: 'front',
    validator: (value) => ['front', 'back'].includes(value),
  },
  colorway: {
    type: String,
    default: 'grafit',
  },
  size: {
    type: Number,
    default: 180,
  },
  nama: {
    type: String,
    default: 'HP',
  },
  dekoratif: {
    type: Boolean,
    default: false,
  },
})

const palet = {
  grafit: {
    bodi: '#2E2B27',
    bezel: '#1C1A17',
    layar: '#F7F3EA',
  },
  sage: {
    bodi: '#7C8B79',
    bezel: '#5C6A59',
    layar: '#F8F4EA',
  },
  pasir: {
    bodi: '#C7B398',
    bezel: '#A38F74',
    layar: '#FFF9EF',
  },
  terakota: {
    bodi: '#B06A50',
    bezel: '#8C4E38',
    layar: '#FBF1E7',
  },
}

const warna = computed(() => palet[props.colorway] ?? palet.grafit)
const label = computed(() => `Ilustrasi ${props.nama} warna ${props.colorway}`)
</script>

<template>
  <div class="phone-visual" :style="{ maxWidth: `${size}px` }">
    <svg
      viewBox="0 0 240 320"
      :role="dekoratif ? undefined : 'img'"
      :aria-hidden="dekoratif ? 'true' : undefined"
      :aria-label="dekoratif ? undefined : label"
      focusable="false"
    >
      <title v-if="!dekoratif">{{ label }}</title>

      <ellipse cx="120" cy="314" rx="62" ry="6" fill="#17150F" fill-opacity="0.08" />

      <rect x="44" y="8" width="152" height="304" rx="28" :fill="warna.bodi" />
      <rect x="196" y="88" width="2.5" height="28" rx="1.25" :fill="warna.bezel" />
      <rect x="196" y="124" width="2.5" height="44" rx="1.25" :fill="warna.bezel" />
      <rect x="41.5" y="76" width="2.5" height="18" rx="1.25" :fill="warna.bezel" />

      <template v-if="view === 'front'">
        <rect
          x="52"
          y="16"
          width="136"
          height="288"
          rx="20"
          :fill="warna.layar"
          :stroke="warna.bezel"
          stroke-width="4"
        />
        <circle cx="120" cy="28" r="3.5" :fill="warna.bezel" />
        <circle cx="119" cy="27" r="0.9" fill="#FFFFFF" fill-opacity="0.6" />
        <rect x="111" y="20" width="18" height="2" rx="1" :fill="warna.bezel" />
        <polygon points="66,34 126,16 94,300 52,304" fill="#FFFFFF" fill-opacity="0.1" />
      </template>

      <template v-else>
        <rect
          x="62"
          y="30"
          width="52"
          height="52"
          rx="14"
          :fill="warna.bodi"
          :stroke="warna.bezel"
          stroke-width="2"
        />
        <circle cx="80" cy="48" r="8" :fill="warna.bezel" />
        <circle cx="80" cy="48" r="4" fill="#FFFFFF" fill-opacity="0.12" />
        <circle cx="80" cy="68" r="8" :fill="warna.bezel" />
        <circle cx="80" cy="68" r="4" fill="#FFFFFF" fill-opacity="0.12" />
        <circle cx="100" cy="58" r="3" :fill="warna.layar" />
        <line
          x1="64"
          y1="174"
          x2="176"
          y2="174"
          :stroke="warna.bezel"
          stroke-width="1"
          stroke-opacity="0.35"
        />
      </template>
    </svg>
  </div>
</template>

<style scoped>
.phone-visual {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: center;
}

.phone-visual svg {
  width: 100%;
  height: auto;
}
</style>
