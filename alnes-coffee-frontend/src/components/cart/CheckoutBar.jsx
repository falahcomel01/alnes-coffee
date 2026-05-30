import { useNavigate } from 'react-router-dom'
import useCartStore from '@/store/cartStore'
import { formatPriceShort } from '@/utils/format'

export default function CheckoutBar() {
  const navigate = useNavigate()
  const items = useCartStore(s => s.items)
  const totalItems = useCartStore(s => s.totalItems)
  const totalPrice = useCartStore(s => s.totalPrice)

  const count = totalItems()
  const price = totalPrice()

  if (count === 0) return null

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 px-4 pb-5 pt-2 pointer-events-none">
      <button
        onClick={() => navigate('/checkout')}
        className="w-full max-w-md mx-auto flex items-center justify-between
          bg-[#1C1008] text-white px-5 py-4 rounded-2xl shadow-2xl
          pointer-events-auto active:scale-[0.98] transition-transform"
      >
        <div className="flex items-center gap-3">
          <div className="w-7 h-7 rounded-full bg-[#C8872A] flex items-center justify-center text-xs font-bold">
            {count}
          </div>
          <span className="text-sm font-medium">Lihat Pesanan</span>
        </div>
        <span className="text-sm font-bold">{formatPriceShort(price)}</span>
      </button>
    </div>
  )
}