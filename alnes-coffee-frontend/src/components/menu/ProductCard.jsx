import { useState } from 'react'
import useCartStore from '@/store/cartStore'
import { formatPriceShort } from '@/utils/format'

export default function ProductCard({ product }) {
  const addItem    = useCartStore(s => s.addItem)
  const items      = useCartStore(s => s.items)
  const updateQty  = useCartStore(s => s.updateQty)
  const [adding, setAdding]     = useState(false)
  const [imgError, setImgError] = useState(false)

  const cartItem = items.find(i => i.id === product.id)
  const qty      = cartItem?.qty || 0

  const handleAdd = () => {
    setAdding(true)
    addItem(product)
    setTimeout(() => setAdding(false), 300)
  }

  const imageUrl = product.image_url && !imgError ? product.image_url : null

  return (
    <div className="flex gap-3 py-4 border-b border-[#F0EDE8] last:border-0">

      {/* Image */}
      <div className="w-24 h-24 rounded-xl overflow-hidden bg-[#F0EDE8] flex-shrink-0">
        {imageUrl ? (
          <img
            src={imageUrl}
            alt={product.name}
            className="w-full h-full object-cover"
            loading="lazy"
            width={96}
            height={96}
            onError={() => setImgError(true)}
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#F0EDE8] to-[#E5DDD3]">
            <span className="text-3xl">{product.category?.icon || '☕'}</span>
          </div>
        )}
      </div>

      {/* Info */}
      <div className="flex-1 min-w-0">

        {/* Badges */}
        <div className="flex items-start gap-1 flex-wrap mb-1">
          {product.is_best_seller && (
            <span className="text-[10px] bg-[#FFF3E0] text-[#C8872A] px-1.5 py-0.5 rounded-full font-medium">
              Best Seller
            </span>
          )}
          {product.is_special && (
            <span className="text-[10px] bg-[#F3E8FF] text-[#7C3AED] px-1.5 py-0.5 rounded-full font-medium">
              Chef's Special
            </span>
          )}
          {product.is_popular && !product.is_best_seller && (
            <span className="text-[10px] bg-[#FFF0F0] text-[#E53E3E] px-1.5 py-0.5 rounded-full font-medium">
              Favorit
            </span>
          )}
          {product.is_recommended && !product.is_special && (
            <span className="text-[10px] bg-[#E8F5E9] text-[#2E7D32] px-1.5 py-0.5 rounded-full font-medium">
              Rekomendasi
            </span>
          )}
        </div>

        {/* Nama */}
        <h3 className="text-sm font-semibold text-[#1C1008] leading-snug line-clamp-2">
          {product.name}
        </h3>

        {/* Deskripsi */}
        {product.description && (
          <p className="text-xs text-[#8B7355] mt-0.5 line-clamp-2">
            {product.description}
          </p>
        )}

        {/* Harga + tombol */}
        <div className="flex items-center justify-between mt-2">
          <span className="text-sm font-bold text-[#1C1008]">
            {product.formatted_price || formatPriceShort(product.price)}
          </span>

          {qty === 0 ? (
            <button
              onClick={handleAdd}
              className={`w-8 h-8 rounded-full flex items-center justify-center font-bold text-lg transition-all
                ${adding ? 'bg-[#C8872A] scale-90' : 'bg-[#1C1008] hover:bg-[#2D1F0E]'} text-white shadow-sm`}
            >
              +
            </button>
          ) : (
            <div className="flex items-center gap-2">
              <button
                onClick={() => updateQty(product.id, qty - 1)}
                className="w-7 h-7 rounded-full border border-[#1C1008] flex items-center justify-center text-[#1C1008] font-bold text-sm"
              >
                −
              </button>
              <span className="text-sm font-bold text-[#1C1008] w-4 text-center">{qty}</span>
              <button
                onClick={handleAdd}
                className="w-7 h-7 rounded-full bg-[#1C1008] flex items-center justify-center text-white font-bold text-sm"
              >
                +
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}