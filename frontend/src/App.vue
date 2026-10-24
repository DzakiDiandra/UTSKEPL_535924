<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppFooter from '@/components/AppFooter.vue'
import AppHeader from '@/components/AppHeader.vue'
import ToastMessage from '@/components/ToastMessage.vue'

const route = useRoute()
const tampilShell = computed(() => !route.meta.admin)
</script>

<template>
  <a class="skip-link" href="#konten-utama">Lewati ke konten</a>

  <AppHeader v-if="tampilShell" />

  <main id="konten-utama" tabindex="-1">
    <RouterView />
  </main>

  <AppFooter v-if="tampilShell" />
  <ToastMessage />
</template>
