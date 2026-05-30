import { create } from 'zustand'
import { persist } from 'zustand/middleware'

const useCartStore = create(
  persist(
    (set, get) => ({
      items: [],
      tableSlug: null,
      tableId: null,
      tableNumber: null,

      setTable: (slug, id, number) => set({ tableSlug: slug, tableId: id, tableNumber: number }),

      addItem: (product) => {
        const items = get().items
        const existing = items.find(i => i.id === product.id)
        if (existing) {
          set({ items: items.map(i => i.id === product.id ? { ...i, qty: i.qty + 1 } : i) })
        } else {
          set({ items: [...items, { ...product, qty: 1, notes: '' }] })
        }
      },

      removeItem: (id) => set({ items: get().items.filter(i => i.id !== id) }),

      updateQty: (id, qty) => {
        if (qty <= 0) { get().removeItem(id); return }
        set({ items: get().items.map(i => i.id === id ? { ...i, qty } : i) })
      },

      updateNotes: (id, notes) =>
        set({ items: get().items.map(i => i.id === id ? { ...i, notes } : i) }),

      clearCart: () => set({ items: [] }),

      totalItems: () => get().items.reduce((s, i) => s + i.qty, 0),
      totalPrice: () => get().items.reduce((s, i) => s + i.price * i.qty, 0),
    }),
    { name: 'alnes-cart' }
  )
)

export default useCartStore