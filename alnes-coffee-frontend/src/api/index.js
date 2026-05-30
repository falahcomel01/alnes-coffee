import api from './axios'

export const settingApi = {
  get: () => api.get('/settings'),
}

export const categoryApi = {
  list: () => api.get('/categories'),
}

export const productApi = {
  list: (params) => api.get('/products', { params }),
  featured: () => api.get('/products/featured'),
  search: (q) => api.get('/products/search', { params: { q } }),
  show: (slug) => api.get(`/products/${slug}`),
}

export const tableApi = {
  show: (slug) => api.get(`/tables/${slug}`),
}

export const orderApi = {
  store: (data) => api.post('/orders', data),
  show: (invoice) => api.get(`/orders/${invoice}`),
}

export const promoApi = {
  check: (data) => api.post('/promos/check', data),
}

export const kitchenApi = {
  index: () => api.get('/kitchen'),
  updateStatus: (id, status) => api.patch(`/kitchen/${id}/status`, { status }),
}

export const bannerApi = {
  list: () => api.get('/banners'),
}

export const paymentApi = {
  createToken: (data) => api.post('/payment/token', data),
}