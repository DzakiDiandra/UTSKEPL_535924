import { describe, expect, it } from 'vitest'
import { formatRupiah } from '@/utils/format'

describe('formatRupiah', () => {
  it('memformat angka menjadi rupiah tanpa desimal', () => {
    expect(formatRupiah(2499000)).toBe('Rp 2.499.000')
    expect(formatRupiah(0)).toBe('Rp 0')
  })

  it('memakai nol untuk nilai yang bukan angka', () => {
    expect(formatRupiah('bukan-angka')).toBe('Rp 0')
    expect(formatRupiah(null)).toBe('Rp 0')
  })
})
