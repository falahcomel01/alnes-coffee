    import { create } from 'zustand'
    import { persist } from 'zustand/middleware'

    const useLoyaltyStore = create(
    persist(
        (set) => ({
        customer:  null,
        nextTier:  null,
        history:   [],

        setCustomer: (customer, nextTier) => set({ customer, nextTier }),
        setHistory:  (history)            => set({ history }),
        clearLoyalty: ()                  => set({ customer: null, nextTier: null, history: [] }),
        }),
        { name: 'alnes-loyalty' }
    )
    )

    export default useLoyaltyStore