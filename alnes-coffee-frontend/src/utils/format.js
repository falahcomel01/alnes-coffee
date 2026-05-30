export const formatPrice = (price) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)

export const formatPriceShort = (price) =>
  `Rp${new Intl.NumberFormat('id-ID').format(price)}`