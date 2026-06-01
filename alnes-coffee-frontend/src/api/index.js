import api from './axios'

export const settingApi = {
  get: () => api.get('/settings'),
}

export const categoryApi = {
  list: () => api.get('/categories'),
}

export const productApi = {
  list:     (params) => api.get('/products', { params }),
  featured: ()       => api.get('/products/featured'),
  search:   (q)      => api.get('/products/search', { params: { q } }),
  show:     (slug)   => api.get(`/products/${slug}`),
}

export const tableApi = {
  show: (slug) => api.get(`/tables/${slug}`),
}

export const orderApi = {
  store: (data)    => api.post('/orders', data),
  show:  (invoice) => api.get(`/orders/${invoice}`),
}

export const promoApi = {
  check: (data) => api.post('/promos/check', data),
}

export const kitchenApi = {
  index:        ()             => api.get('/kitchen'),
  updateStatus: (id, status)   => api.patch(`/kitchen/${id}/status`, { status }),
}

export const bannerApi = {
  list: () => api.get('/banners'),
}

export const paymentApi = {
  createToken: (data) => api.post('/payment/token', data),
}

export const loyaltyApi = {
  check:   (phone) => api.post('/loyalty/check',   { phone }),
  rewards: (phone) => api.post('/loyalty/rewards', { phone }),
  history: (phone) => api.post('/loyalty/history', { phone }),
  redeem:  (data)  => api.post('/loyalty/redeem',  data),
}

export const reservationApi = {
  checkAvailability: (data) => api.post('/reservations/check-availability', data),
  store:             (data) => api.post('/reservations', data),
  checkByPhone:      (data) => api.post('/reservations/check-by-phone', data),
  cancel:            (id, data) => api.post(`/reservations/${id}/cancel`, data),
}