import { useEffect, useState, useCallback } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { orderApi } from '@/api'
import { formatPriceShort } from '@/utils/format'
import echo from '@/lib/echo'

const fontLink = document.getElementById('alnes-fonts')
if (!fontLink) {
  const link = document.createElement('link')
  link.id   = 'alnes-fonts'
  link.rel  = 'stylesheet'
  link.href = 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;1,9..144,300&family=DM+Sans:wght@300;400;500&display=swap'
  document.head.appendChild(link)
}

const statusSteps = [
  { key: 'pending',   label: 'Pesanan Masuk',  labelEn: 'Order Received', emoji: '📋', color: '#8B7355' },
  { key: 'confirmed', label: 'Dikonfirmasi',    labelEn: 'Confirmed',      emoji: '✅', color: '#3B82F6' },
  { key: 'cooking',   label: 'Sedang Dimasak',  labelEn: 'Being Prepared', emoji: '👨‍🍳', color: '#F59E0B' },
  { key: 'ready',     label: 'Siap Diambil',    labelEn: 'Ready',          emoji: '🔔', color: '#10B981' },
  { key: 'completed', label: 'Selesai',          labelEn: 'Completed',      emoji: '🎉', color: '#6B3F1F' },
]

const headerBg = {
  pending:   'linear-gradient(135deg, #3D2B1A 0%, #6B3F1F 100%)',
  confirmed: 'linear-gradient(135deg, #1E3A5F 0%, #2563EB 100%)',
  cooking:   'linear-gradient(135deg, #7C2D12 0%, #EA580C 100%)',
  ready:     'linear-gradient(135deg, #064E3B 0%, #059669 100%)',
  completed: 'linear-gradient(135deg, #1C1008 0%, #6B3F1F 100%)',
}

export default function OrderStatusPage() {
  const { invoice } = useParams()
  const navigate    = useNavigate()
  const { t, i18n } = useTranslation()

  const [order, setOrder]             = useState(null)
  const [loading, setLoading]         = useState(true)
  const [error, setError]             = useState('')
  const [connected, setConnected]     = useState(false)
  const [justUpdated, setJustUpdated] = useState(false)

  const fetchOrder = useCallback(() => {
    orderApi.show(invoice)
      .then(({ data }) => { setOrder(data.data); setLoading(false) })
      .catch(() => { setError('Pesanan tidak ditemukan'); setLoading(false) })
  }, [invoice])

  useEffect(() => {
    fetchOrder()
    const channel = echo.channel(`order.${invoice}`)
    channel.subscribed(() => setConnected(true))
    channel.listen('.order.status.updated', (data) => {
      setOrder(prev => prev ? { ...prev, order_status: data.order_status, payment_status: data.payment_status } : prev)
      setJustUpdated(true)
      setTimeout(() => setJustUpdated(false), 3000)
    })
    const interval = setInterval(fetchOrder, 15000)
    return () => { echo.leaveChannel(`order.${invoice}`); clearInterval(interval) }
  }, [invoice, fetchOrder])

  const currentStepIndex = order ? statusSteps.findIndex(s => s.key === order.order_status) : 0
  const currentStep      = statusSteps[currentStepIndex]
  const isEN             = i18n.language === 'en'

  if (loading) return (
    <div className="min-h-screen flex items-center justify-center"
      style={{ background: '#FAF8F4', fontFamily: "'DM Sans', sans-serif" }}>
      <div className="flex flex-col items-center gap-4">
        <div className="w-16 h-16 rounded-2xl flex items-center justify-center"
          style={{ background: '#6B3F1F' }}>
          <span style={{ fontFamily: "'Fraunces', serif", fontStyle: 'italic', color: '#FAF2E6', fontSize: 24, fontWeight: 300 }}>A</span>
        </div>
        <div className="flex gap-1.5">
          {[0,1,2].map(i => (
            <div key={i} className="w-2 h-2 rounded-full bg-[#C8872A] animate-bounce"
              style={{ animationDelay: `${i * 0.15}s` }} />
          ))}
        </div>
      </div>
    </div>
  )

  if (error) return (
    <div className="min-h-screen flex flex-col items-center justify-center px-6 text-center"
      style={{ background: '#FAF8F4', fontFamily: "'DM Sans', sans-serif" }}>
      <div className="w-16 h-16 rounded-2xl bg-[#F0EDE8] flex items-center justify-center mb-4 text-3xl">😕</div>
      <p className="font-semibold text-[#6B3F1F] mb-1">{t('order_not_found')}</p>
      <button onClick={() => navigate('/menu')}
        className="mt-4 px-6 py-2.5 text-white rounded-xl text-sm font-medium"
        style={{ background: '#6B3F1F' }}>
        {t('back_to_menu')}
      </button>
    </div>
  )

  return (
    <div style={{ fontFamily: "'DM Sans', sans-serif", background: '#FAF8F4', minHeight: '100vh' }}>

      {/* Toast notification */}
      <div className={`fixed top-4 left-1/2 -translate-x-1/2 z-50 transition-all duration-500 ${justUpdated ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4 pointer-events-none'}`}>
        <div className="flex items-center gap-2 px-5 py-3 rounded-full shadow-xl text-white text-sm font-medium"
          style={{ background: '#6B3F1F', whiteSpace: 'nowrap' }}>
          <span>🔔</span>
          <span>Status pesanan diperbarui!</span>
        </div>
      </div>

      {/* Hero header */}
      <div className="relative overflow-hidden"
        style={{ background: headerBg[order.order_status] || headerBg.pending, paddingTop: 48, paddingBottom: 40 }}>

        {/* Decorative circles */}
        <div className="absolute top-[-60px] right-[-60px] w-48 h-48 rounded-full opacity-10"
          style={{ background: 'white' }} />
        <div className="absolute bottom-[-40px] left-[-40px] w-32 h-32 rounded-full opacity-10"
          style={{ background: 'white' }} />

        <div className="relative text-center px-6">
          {/* Animated emoji */}
          <div className="w-20 h-20 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg"
            style={{ background: 'rgba(255,255,255,0.15)', backdropFilter: 'blur(10px)' }}>
            <span className="text-4xl" style={{ filter: 'drop-shadow(0 2px 4px rgba(0,0,0,0.3))' }}>
              {currentStep?.emoji}
            </span>
          </div>

          <h1 style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 26, color: 'white', letterSpacing: '-0.02em', marginBottom: 6 }}>
            {isEN ? currentStep?.labelEn : currentStep?.label}
          </h1>

          <p style={{ color: 'rgba(255,255,255,0.6)', fontSize: 12, letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 16 }}>
            {invoice}
          </p>

          {/* Realtime badge */}
          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full"
            style={{ background: 'rgba(255,255,255,0.12)', backdropFilter: 'blur(8px)' }}>
            <span className={`w-1.5 h-1.5 rounded-full ${connected ? 'bg-green-400' : 'bg-white/40'}`}
              style={connected ? { animation: 'pulse 2s infinite' } : {}} />
            <span style={{ color: 'rgba(255,255,255,0.7)', fontSize: 11, fontWeight: 500 }}>
              {connected ? t('realtime_active') : t('connecting')}
            </span>
          </div>
        </div>
      </div>

      <div className="px-4 py-5 space-y-3 pb-24 max-w-lg mx-auto">

        {/* Progress stepper */}
        <div className="bg-white rounded-2xl overflow-hidden border border-[#F0EDE8] shadow-sm">
          <div className="px-4 py-3 border-b border-[#F0EDE8]">
            <p style={{ fontSize: 10, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.1em', color: '#8B7355' }}>
              Status Pesanan
            </p>
          </div>
          <div className="p-4 space-y-1">
            {statusSteps.map((step, index) => {
              const isDone    = index < currentStepIndex
              const isActive  = index === currentStepIndex
              const isPending = index > currentStepIndex
              const isLast    = index === statusSteps.length - 1

              return (
                <div key={step.key}>
                  <div className="flex items-center gap-3 py-2">
                    {/* Step circle */}
                    <div className="relative flex-shrink-0">
                      <div className={`w-9 h-9 rounded-full flex items-center justify-center text-sm transition-all duration-500`}
                        style={{
                          background: isDone ? '#6B3F1F' : isActive ? step.color : '#F0EDE8',
                          boxShadow: isActive ? `0 0 0 4px ${step.color}30` : 'none',
                        }}>
                        {isDone ? (
                          <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                          </svg>
                        ) : (
                          <span style={{ fontSize: isActive ? 16 : 14, filter: isPending ? 'grayscale(1) opacity(0.5)' : 'none' }}>
                            {step.emoji}
                          </span>
                        )}
                      </div>
                      {isActive && (
                        <div className="absolute inset-0 rounded-full animate-ping opacity-30"
                          style={{ background: step.color }} />
                      )}
                    </div>

                    {/* Step label */}
                    <div className="flex-1">
                      <p style={{
                        fontSize: 14,
                        fontWeight: isActive ? 600 : 400,
                        color: isDone ? '#6B3F1F' : isActive ? step.color : '#C4A882',
                        transition: 'all 0.3s',
                      }}>
                        {isEN ? step.labelEn : step.label}
                      </p>
                      {isActive && (
                        <p style={{ fontSize: 11, color: step.color, marginTop: 2, opacity: 0.8 }}>
                          {order.order_status === 'pending'   && 'Pesanan kamu sudah masuk ke sistem'}
                          {order.order_status === 'confirmed' && 'Pesanan sudah dikonfirmasi oleh staff'}
                          {order.order_status === 'cooking'   && 'Dapur sedang menyiapkan pesananmu ☕'}
                          {order.order_status === 'ready'     && 'Pesananmu siap! Silakan diambil 🎊'}
                          {order.order_status === 'completed' && 'Terima kasih telah mampir! 🙏'}
                        </p>
                      )}
                    </div>

                    {isActive && (
                      <div className="w-2 h-2 rounded-full animate-pulse flex-shrink-0"
                        style={{ background: step.color }} />
                    )}
                  </div>

                  {/* Connector line */}
                  {!isLast && (
                    <div className="ml-[17px] w-0.5 h-3"
                      style={{ background: index < currentStepIndex ? '#6B3F1F' : '#F0EDE8' }} />
                  )}
                </div>
              )
            })}
          </div>
        </div>

        {/* Info pesanan */}
        <div className="bg-white rounded-2xl p-4 border border-[#F0EDE8] shadow-sm">
          <p style={{ fontSize: 10, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.1em', color: '#8B7355', marginBottom: 12 }}>
            {t('order_info')}
          </p>
          <div className="space-y-2.5">
            {[
              { label: t('name'),         value: order.customer_name },
              { label: t('table'),        value: order.table_number },
              { label: isEN ? 'Type' : 'Tipe', value: order.order_type === 'dine_in' ? (isEN ? 'Dine In' : 'Makan di Tempat') : (isEN ? 'Takeaway' : 'Bawa Pulang') },
            ].map(row => (
              <div key={row.label} className="flex justify-between items-center">
                <span style={{ fontSize: 13, color: '#8B7355' }}>{row.label}</span>
                <span style={{ fontSize: 13, fontWeight: 500, color: '#6B3F1F' }}>{row.value}</span>
              </div>
            ))}
            <div className="flex justify-between items-center">
              <span style={{ fontSize: 13, color: '#8B7355' }}>{isEN ? 'Payment' : 'Pembayaran'}</span>
              <span className="px-2.5 py-1 rounded-full text-xs font-semibold"
                style={{
                  background: order.payment_status === 'paid' ? '#D1FAE5' : '#FEF3C7',
                  color:      order.payment_status === 'paid' ? '#065F46' : '#92400E',
                }}>
                {order.payment_status === 'paid' ? (isEN ? 'Paid' : 'Lunas') : (isEN ? 'Unpaid' : 'Belum Dibayar')}
              </span>
            </div>
          </div>
        </div>

        {/* Item pesanan */}
        <div className="bg-white rounded-2xl p-4 border border-[#F0EDE8] shadow-sm">
          <p style={{ fontSize: 10, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.1em', color: '#8B7355', marginBottom: 12 }}>
            {t('order_items')}
          </p>
          <div className="space-y-3">
            {order.items?.map((item, i) => (
              <div key={i} className="flex justify-between items-center">
                <div className="flex-1 pr-3">
                  <p style={{ fontSize: 13, fontWeight: 500, color: '#6B3F1F' }}>{item.product_name}</p>
                  <p style={{ fontSize: 11, color: '#8B7355', marginTop: 2 }}>{formatPriceShort(item.price)} × {item.qty}</p>
                </div>
                <p style={{ fontSize: 13, fontWeight: 600, color: '#6B3F1F' }}>{formatPriceShort(item.subtotal)}</p>
              </div>
            ))}
            <div className="border-t border-dashed border-[#EDE8E0] pt-3 flex justify-between items-center">
              <span style={{ fontSize: 14, fontWeight: 700, color: '#6B3F1F' }}>{t('total')}</span>
              <span style={{ fontSize: 18, fontWeight: 700, color: '#C8872A' }}>{formatPriceShort(order.grand_total)}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom CTA */}
      <div className="fixed bottom-0 left-0 right-0 px-4 pb-6 pt-3 max-w-lg mx-auto"
        style={{ background: 'linear-gradient(to top, #FAF8F4 70%, transparent)' }}>
        {order.order_status === 'completed' ? (
          <button onClick={() => navigate('/menu')}
            className="w-full py-4 rounded-2xl text-sm font-bold text-white shadow-lg active:scale-[0.98] transition-all"
            style={{ background: 'linear-gradient(135deg, #6B3F1F, #C8872A)', boxShadow: '0 8px 24px rgba(107,63,31,0.3)' }}>
            🎉 {t('order_again')}
          </button>
        ) : (
          <button onClick={fetchOrder}
            className="w-full py-3.5 rounded-2xl text-sm font-medium active:scale-[0.98] transition-all"
            style={{ border: '1px solid #EDE8E0', color: '#6B3F1F', background: 'white' }}>
            🔄 {t('refresh')}
          </button>
        )}
      </div>
    </div>
  )
}