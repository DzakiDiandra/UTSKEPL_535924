import { ref } from 'vue'

const toast = ref(null)
const DURASI = 4000
let timer = null
let sisaWaktu = DURASI
let mulai = 0

function matikanTimer() {
  if (timer) {
    clearTimeout(timer)
    timer = null
  }
}

function jadwalkanTimer(durasi = DURASI) {
  matikanTimer()
  sisaWaktu = durasi
  mulai = Date.now()
  timer = setTimeout(() => {
    toast.value = null
    timer = null
  }, durasi)
}

export function useToast() {
  function tampilkanToast(message, type = 'success') {
    toast.value = {
      id: Date.now(),
      message,
      type,
    }
    jadwalkanTimer()
  }

  function tutupToast() {
    matikanTimer()
    toast.value = null
  }

  function tahanToast() {
    if (!toast.value) return

    matikanTimer()
    sisaWaktu = Math.max(1200, sisaWaktu - (Date.now() - mulai))
  }

  function lanjutToast() {
    if (!toast.value) return

    jadwalkanTimer(sisaWaktu)
  }

  return {
    toast,
    tampilkanToast,
    tutupToast,
    tahanToast,
    lanjutToast,
  }
}
