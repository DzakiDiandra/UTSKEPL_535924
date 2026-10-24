const formatterRupiah = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
})

export function formatRupiah(nilai) {
  const angka = Number(nilai)

  return formatterRupiah.format(Number.isFinite(angka) ? angka : 0).replace(/\u00a0/g, ' ')
}
