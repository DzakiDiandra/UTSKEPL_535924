const COLORWAY_MEREK = {
  samsung: 'terakota',
  xiaomi: 'sage',
  oppo: 'pasir',
  vivo: 'sage',
  realme: 'terakota',
  apple: 'grafit',
}

export function statusStok(stok) {
  const jumlah = Number(stok)

  if (!Number.isFinite(jumlah) || jumlah <= 0) return 'habis'
  if (jumlah <= 5) return 'menipis'
  return 'tersedia'
}

export function labelStok(stok) {
  const status = statusStok(stok)

  if (status === 'habis') return 'Habis'
  if (status === 'menipis') return 'Menipis'
  return 'Tersedia'
}

export function ringkasSpek(produk) {
  if (!produk) return ''

  const ram = produk.ram_gb ?? '-'
  const penyimpanan = produk.penyimpanan_gb ?? '-'

  return `${ram}/${penyimpanan} GB`
}

export function buatLinkWhatsapp(nomor, nama) {
  if (!nomor || !nama) return ''

  const nomorBersih = String(nomor).replace(/\D/g, '')

  if (!nomorBersih) return ''

  const pesan = `Halo, saya ingin menanyakan stok ${nama}. Apakah masih tersedia?`

  return `https://wa.me/${nomorBersih}?text=${encodeURIComponent(pesan)}`
}

export function colorwayMerek(merek) {
  const kunci = String(merek ?? '').toLowerCase()

  return COLORWAY_MEREK[kunci] ?? 'grafit'
}

export function urlGambarAman(url) {
  if (!url) return ''

  try {
    const alamat = new URL(String(url))

    return ['http:', 'https:'].includes(alamat.protocol) ? alamat.href : ''
  } catch {
    return ''
  }
}
