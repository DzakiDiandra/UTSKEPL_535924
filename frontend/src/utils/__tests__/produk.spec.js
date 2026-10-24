import { describe, expect, it } from 'vitest'
import {
  buatLinkWhatsapp,
  colorwayMerek,
  labelStok,
  ringkasSpek,
  statusStok,
  urlGambarAman,
} from '@/utils/produk'

describe('statusStok', () => {
  it('menandai stok kosong sebagai habis', () => {
    expect(statusStok(0)).toBe('habis')
    expect(statusStok(-1)).toBe('habis')
  })

  it('menandai stok 1 sampai 5 sebagai menipis', () => {
    expect(statusStok(1)).toBe('menipis')
    expect(statusStok(5)).toBe('menipis')
    expect(statusStok('3')).toBe('menipis')
  })

  it('menandai stok di atas 5 sebagai tersedia', () => {
    expect(statusStok(6)).toBe('menipis')
    expect(statusStok(12)).toBe('tersedia')
  })
})

describe('labelStok', () => {
  it('mengembalikan label sesuai jumlah stok', () => {
    expect(labelStok(0)).toBe('Habis')
    expect(labelStok(4)).toBe('Menipis')
    expect(labelStok(10)).toBe('Tersedia')
  })
})

describe('ringkasSpek', () => {
  it('menggabungkan RAM dan penyimpanan', () => {
    expect(ringkasSpek({ ram_gb: 8, penyimpanan_gb: 256 })).toBe('8/256 GB')
  })

  it('memakai tanda hubung untuk data yang kosong', () => {
    expect(ringkasSpek({ ram_gb: null, penyimpanan_gb: 128 })).toBe('-/128 GB')
    expect(ringkasSpek(null)).toBe('')
  })
})

describe('buatLinkWhatsapp', () => {
  it('membersihkan nomor dan menyertakan nama produk', () => {
    const tautan = buatLinkWhatsapp('+62 812-3456-7890', 'Xiaomi Redmi Note 13')

    expect(tautan).toContain('https://wa.me/6281234567890?text=')
    expect(decodeURIComponent(tautan.split('?text=')[1])).toBe(
      'Halo, saya ingin menanyakan stok Xiaomi Redmi Note 13. Apakah masih tersedia?',
    )
  })

  it('mengembalikan string kosong bila data tidak lengkap', () => {
    expect(buatLinkWhatsapp('', 'Samsung Galaxy A15')).toBe('')
    expect(buatLinkWhatsapp('6281234567890', '')).toBe('')
    expect(buatLinkWhatsapp('tanpa-angka', 'Samsung Galaxy A15')).toBe('')
  })
})

describe('colorwayMerek', () => {
  it('memilih warna ilustrasi berdasarkan merek', () => {
    expect(colorwayMerek('Samsung')).toBe('terakota')
    expect(colorwayMerek('Apple')).toBe('grafit')
    expect(colorwayMerek('Merek Baru')).toBe('grafit')
  })
})

describe('urlGambarAman', () => {
  it('hanya menerima alamat http dan https', () => {
    expect(urlGambarAman('https://contoh.com/hp.jpg')).toBe('https://contoh.com/hp.jpg')
    expect(urlGambarAman('http://contoh.com/hp.jpg')).toBe('http://contoh.com/hp.jpg')
    expect(urlGambarAman('javascript:alert(1)')).toBe('')
    expect(urlGambarAman('bukan-url')).toBe('')
    expect(urlGambarAman(null)).toBe('')
  })
})
