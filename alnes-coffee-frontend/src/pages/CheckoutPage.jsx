import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import useCartStore from '@/store/cartStore'
import useSettingStore from '@/store/settingStore'
import { orderApi, promoApi } from '@/api'
import { formatPriceShort } from '@/utils/format'

export default function CheckoutPage() {
  const navigate    = useNavigate()
  const { t }       = useTranslation()
  const items       = useCartStore(s => s.items)
  const tableId     = useCartStore(s => s.tableId)
  const tableNumber = useCartStore(s => s.tableNumber)
  const totalPrice  = useCartStore(s => s.totalPrice)
  const clearCart   = useCartStore(s => s.clearCart)
  const setting     = useSettingStore(s => s.setting)

  const [form, setForm]             = useState({ name: '', phone: '', order_type: 'dine_in', payment_method: 'qris' })
  const [promoCode, setPromoCode]   = useState('')
  const [promoData, setPromoData]   = useState(null)
  const [promoError, setPromoError] = useState('')
  const [loading, setLoading]       = useState(false)
  const [error, setError]           = useState('')
  const [submitted, setSubmitted]   = useState(false)

  useEffect(() => {
    if (items.length === 0 && !submitted) navigate('/menu')
  }, [items.length, submitted])

  if (items.length === 0 && !submitted) return null

  const subtotal   = totalPrice()
  const serviceFee = parseFloat(setting?.service_fee || 1000)
  const tax        = subtotal * (parseFloat(setting?.tax_percentage || 0) / 100)
  const discount   = promoData?.discount || 0
  const grandTotal = subtotal + serviceFee + tax - discount

  const handlePromo = async () => {
    setPromoError('')
    try {
      const { data } = await promoApi.check({ code: promoCode, subtotal })
      setPromoData(data.data)
    } catch (e) {
      setPromoError(e.response?.data?.message || t('promo_invalid'))
      setPromoData(null)
    }
  }

  const handleSubmit = async () => {
    if (!form.name || !form.phone) { setError(t('name_phone_required')); return }
    setLoading(true)
    setError('')
    try {
      const { data } = await orderApi.store({
        table_id:       tableId,
        customer_name:  form.name,
        customer_phone: form.phone,
        order_type:     form.order_type,
        payment_method: form.payment_method,
        promo_code:     promoCode || undefined,
        items:          items.map(i => ({ product_id: i.id, qty: i.qty, notes: i.notes })),
      })
      setSubmitted(true)
      clearCart()
      if (form.payment_method === 'cash') {
        navigate(`/order/${data.data.invoice_number}`)
      } else {
        navigate('/payment', {
          state: {
            invoice:        data.data.invoice_number,
            grand_total:    data.data.grand_total,
            payment_method: data.data.payment_method,
          }
        })
      }
    } catch (e) {
      setSubmitted(false)
      setError(e.response?.data?.message || t('order_failed'))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#FAF8F4]">

      {/* Header sticky — max-w centered */}
      <div className="sticky top-0 z-40 bg-[#FAF8F4]/95 backdrop-blur-xl border-b border-[#EDE8E0]">
        <div className="max-w-3xl mx-auto px-4 py-3.5 flex items-center gap-3">
          <button onClick={() => navigate(-1)}
            className="w-9 h-9 rounded-full bg-[#EDE8E0] flex items-center justify-center active:scale-95 transition-transform">
            <svg className="w-4 h-4 text-[#1C1008]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <div>
            <h1 style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 17, color: '#1C1008', letterSpacing: '-0.01em' }}>
              {t('checkout')}
            </h1>
            <p className="text-xs mt-0.5" style={{ color: '#8B7355' }}>
              {items.length} {t('items')} · {formatPriceShort(grandTotal)}
            </p>
          </div>
        </div>
      </div>

      {/* Content — max-w centered */}
      <div className="max-w-3xl mx-auto px-4 py-5 space-y-3 pb-36">

        {/* Tipe pemesanan */}
        <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0]">
          <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 12 }}>
            {t('order_type')}
          </p>
          <div className="grid grid-cols-2 gap-2">
            {[
              { key: 'dine_in',  labelKey: 'dine_in',  emoji: '🪑' },
              { key: 'takeaway', labelKey: 'takeaway',  emoji: '🛍️' },
            ].map(opt => (
              <button key={opt.key} onClick={() => setForm(f => ({ ...f, order_type: opt.key }))}
                className="flex items-center gap-2.5 px-3 py-3.5 rounded-xl transition-all active:scale-95"
                style={{
                  border: '1px solid',
                  borderColor: form.order_type === opt.key ? '#1C1008' : '#EDE8E0',
                  background:  form.order_type === opt.key ? '#1C1008' : '#FAF8F4',
                  color:       form.order_type === opt.key ? '#FAF2E6' : '#1C1008',
                }}>
                <span style={{ fontSize: 18 }}>{opt.emoji}</span>
                <span style={{ fontSize: 12, fontWeight: 500 }}>{t(opt.labelKey)}</span>
              </button>
            ))}
          </div>
        </div>

        {/* Info customer */}
        <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0] space-y-3">
          <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em' }}>
            {t('customer_info')}
          </p>
          {[
            { key: 'name',  labelKey: 'full_name', placeholderKey: 'full_name_placeholder', type: 'text' },
            { key: 'phone', labelKey: 'phone',      placeholderKey: 'phone',                 type: 'tel'  },
          ].map(field => (
            <div key={field.key}>
              <label style={{ fontSize: 11, color: '#8B7355', fontWeight: 500, display: 'block', marginBottom: 6 }}>
                {t(field.labelKey)}
              </label>
              <input
                value={form[field.key]}
                onChange={e => setForm(f => ({ ...f, [field.key]: e.target.value }))}
                placeholder={t(field.placeholderKey)}
                type={field.type}
                className="w-full outline-none"
                style={{
                  background: '#FAF8F4', border: '1px solid #EDE8E0', borderRadius: 12,
                  padding: '11px 16px', fontSize: 14, color: '#1C1008',
                }}
                onFocus={e => e.target.style.borderColor = '#C8872A'}
                onBlur={e => e.target.style.borderColor = '#EDE8E0'}
              />
            </div>
          ))}
          <div className="flex items-center gap-3 rounded-xl px-4 py-3"
            style={{ background: '#FDF8F3', border: '1px solid #EDD9B8' }}>
            <div className="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
              style={{ background: 'rgba(200,135,42,0.12)' }}>
              <svg className="w-4 h-4" style={{ color: '#C8872A' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M3 14h18M10 3v18" />
              </svg>
            </div>
            <div>
              <p style={{ fontSize: 10, color: '#8B7355' }}>{t('table_number')}</p>
              <p style={{ fontSize: 14, fontWeight: 700, color: '#1C1008' }}>{tableNumber}</p>
            </div>
          </div>
        </div>

        {/* Item pesanan */}
        <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0]">
          <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 12 }}>
            {t('order_items')}
          </p>
          <div className="space-y-3">
            {items.map((item, idx) => (
              <div key={item.id}>
                <div className="flex justify-between items-center">
                  <div className="flex-1 pr-3">
                    <p style={{ fontSize: 13, fontWeight: 600, color: '#1C1008' }}>{item.name}</p>
                    <p style={{ fontSize: 11, color: '#8B7355', marginTop: 2 }}>{formatPriceShort(item.price)} × {item.qty}</p>
                  </div>
                  <p style={{ fontSize: 13, fontWeight: 700, color: '#1C1008' }}>{formatPriceShort(item.price * item.qty)}</p>
                </div>
                {idx < items.length - 1 && <div className="border-b border-dashed border-[#EDE8E0] mt-3" />}
              </div>
            ))}
          </div>
        </div>

        {/* Promo */}
        <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0]">
          <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 12 }}>
            {t('promo_code')}
          </p>
          <div className="flex gap-2">
            <input value={promoCode} onChange={e => setPromoCode(e.target.value.toUpperCase())}
              placeholder={t('promo_placeholder')} className="flex-1 outline-none"
              style={{ background: '#FAF8F4', border: '1px solid #EDE8E0', borderRadius: 12,
                padding: '10px 14px', fontSize: 13, color: '#1C1008', letterSpacing: '0.05em' }} />
            <button onClick={handlePromo} className="px-4 rounded-xl active:scale-95 transition-transform"
              style={{ background: '#1C1008', color: '#FAF2E6', fontSize: 13, fontWeight: 600 }}>
              {t('use')}
            </button>
          </div>
          {promoError && <p style={{ fontSize: 11, color: '#ef4444', marginTop: 8 }}>✕ {promoError}</p>}
          {promoData && (
            <div className="flex items-center gap-2 mt-2 rounded-lg px-3 py-2"
              style={{ background: '#f0fdf4', border: '1px solid #bbf7d0' }}>
              <p style={{ fontSize: 11, color: '#15803d', fontWeight: 500 }}>
                ✓ {t('promo_save')} {formatPriceShort(promoData.discount)}!
              </p>
            </div>
          )}
        </div>

        {/* Metode pembayaran */}
        <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0]">
          <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 12 }}>
            {t('payment_method')}
          </p>
          <div className="space-y-2">
            {[
              { key: 'qris',     label: 'QRIS',     descKey: 'qris_desc',     emoji: '📱' },
              { key: 'cash',     label: 'Cash',     descKey: 'cash_desc',     emoji: '💵' },
              { key: 'transfer', label: 'Transfer', descKey: 'transfer_desc', emoji: '🏦' },
            ].map(opt => (
              <button key={opt.key} onClick={() => setForm(f => ({ ...f, payment_method: opt.key }))}
                className="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all active:scale-[0.98]"
                style={{
                  border: '1px solid',
                  borderColor: form.payment_method === opt.key ? '#1C1008' : '#EDE8E0',
                  background:  form.payment_method === opt.key ? '#1C1008' : '#FAF8F4',
                }}>
                <span style={{ fontSize: 20 }}>{opt.emoji}</span>
                <div className="flex-1 text-left">
                  <p style={{ fontSize: 13, fontWeight: 600, color: form.payment_method === opt.key ? '#FAF2E6' : '#1C1008' }}>
                    {opt.label}
                  </p>
                  <p style={{ fontSize: 11, color: form.payment_method === opt.key ? 'rgba(250,242,230,0.6)' : '#8B7355' }}>
                    {t(opt.descKey)}
                  </p>
                </div>
                {form.payment_method === opt.key && (
                  <div className="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                    style={{ background: 'rgba(255,255,255,0.2)' }}>
                    <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                )}
              </button>
            ))}
          </div>
          {form.payment_method === 'cash' && (
            <div className="mt-3 rounded-xl px-4 py-3" style={{ background: '#fffbeb', border: '1px solid #fde68a' }}>
              <p style={{ fontSize: 11, color: '#92400e' }}>💡 {t('cash_info')}</p>
            </div>
          )}
          {(form.payment_method === 'qris' || form.payment_method === 'transfer') && (
            <div className="mt-3 rounded-xl px-4 py-3" style={{ background: '#eff6ff', border: '1px solid #bfdbfe' }}>
              <p style={{ fontSize: 11, color: '#1e40af' }}>💡 {t('midtrans_info')}</p>
            </div>
          )}
        </div>

        {/* Rincian */}
        <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0]">
          <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 12 }}>
            {t('payment_detail')}
          </p>
          <div className="space-y-2.5">
            {[
              { labelKey: 'subtotal',    value: subtotal,   show: true },
              { labelKey: 'service_fee', value: serviceFee, show: true },
              { labelKey: 'tax',         value: tax,        show: tax > 0 },
            ].filter(r => r.show).map(r => (
              <div key={r.labelKey} className="flex justify-between">
                <span style={{ fontSize: 13, color: '#8B7355' }}>{t(r.labelKey)}</span>
                <span style={{ fontSize: 13, color: '#1C1008', fontWeight: 500 }}>{formatPriceShort(r.value)}</span>
              </div>
            ))}
            {discount > 0 && (
              <div className="flex justify-between">
                <span style={{ fontSize: 13, color: '#16a34a' }}>{t('promo_discount')}</span>
                <span style={{ fontSize: 13, color: '#16a34a', fontWeight: 500 }}>−{formatPriceShort(discount)}</span>
              </div>
            )}
            <div className="flex justify-between items-center pt-2.5" style={{ borderTop: '1px dashed #EDE8E0' }}>
              <span style={{ fontSize: 14, fontWeight: 700, color: '#1C1008' }}>{t('total')}</span>
              <span style={{ fontSize: 18, fontWeight: 800, color: '#C8872A', fontFamily: "'Fraunces', serif" }}>
                {formatPriceShort(grandTotal)}
              </span>
            </div>
          </div>
        </div>

        {error && (
          <div className="flex items-center gap-2 rounded-xl px-4 py-3"
            style={{ background: '#fef2f2', border: '1px solid #fecaca' }}>
            <p style={{ fontSize: 13, color: '#dc2626' }}>✕ {error}</p>
          </div>
        )}
      </div>

      {/* Bottom CTA — max-w centered */}
      <div className="fixed bottom-0 left-0 right-0 backdrop-blur-xl border-t border-[#EDE8E0]"
        style={{ background: 'rgba(250,248,244,0.95)' }}>
        <div className="max-w-3xl mx-auto px-4 pb-6 pt-3">
          <button onClick={handleSubmit} disabled={loading}
            className="w-full py-4 rounded-2xl flex items-center justify-center gap-2 transition-all active:scale-[0.98]"
            style={{
              background: loading ? '#5C4A35' : '#1C1008',
              color: '#FAF2E6', fontSize: 14, fontWeight: 700,
              boxShadow: '0 4px 20px rgba(28,16,8,0.25)',
            }}>
            {loading ? (
              <>
                <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span>{t('processing')}</span>
              </>
            ) : (
              <>
                <span>{t('order_now')}</span>
                <span style={{ opacity: 0.4 }}>·</span>
                <span>{formatPriceShort(grandTotal)}</span>
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  )
}