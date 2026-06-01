import { useEffect, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { paymentApi } from '@/api'
import useSettingStore from '@/store/settingStore'

export default function PaymentPage() {
  const location    = useLocation()
  const navigate    = useNavigate()
  const { setting } = useSettingStore()
  const { invoice, grand_total, payment_method } = location.state || {}

  const [loading, setLoading]     = useState(true)
  const [error, setError]         = useState('')
  const [snapToken, setSnapToken] = useState(null)

  useEffect(() => {
    if (!invoice) { navigate('/menu'); return }
    const script = document.createElement('script')
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js'
    script.setAttribute('data-client-key', import.meta.env.VITE_MIDTRANS_CLIENT_KEY)
    script.onload = () => fetchToken()
    document.head.appendChild(script)
    return () => {
      const s = document.querySelector('script[src*="midtrans"]')
      if (s) s.remove()
    }
  }, [invoice])

  const fetchToken = async () => {
    try {
      const { data } = await paymentApi.createToken({ invoice_number: invoice })
      setSnapToken(data.data.snap_token)
      setLoading(false)
    } catch (e) {
      setError(e.response?.data?.message || 'Gagal memuat pembayaran')
      setLoading(false)
    }
  }

  const handlePay = () => {
    if (!snapToken) return
    window.snap.pay(snapToken, {
      onSuccess: () => navigate(`/order/${invoice}`),
      onPending: () => navigate(`/order/${invoice}`),
      onError:   () => setError('Pembayaran gagal. Silakan coba lagi.'),
      onClose:   () => {},
    })
  }

  const formatPrice = (price) =>
    `Rp${new Intl.NumberFormat('id-ID').format(price)}`

  const methodLabel = {
    qris:     'QRIS',
    cash:     'Tunai di Kasir',
    transfer: 'Transfer Bank',
    ewallet:  'E-Wallet',
  }

  const methodEmoji = {
    qris:     '📱',
    cash:     '💵',
    transfer: '🏦',
    ewallet:  '💳',
  }

  // ── Loading ──
  if (loading) return (
    <div className="min-h-screen bg-[#FAF8F4] flex items-center justify-center">
      <div className="text-center space-y-5">
        <div className="relative w-16 h-16 mx-auto">
          <div className="w-16 h-16 rounded-full flex items-center justify-center"
            style={{ background: 'linear-gradient(135deg, #F5ECD7 0%, #EDE8E0 100%)' }}>
            <span className="text-3xl">☕</span>
          </div>
          <div className="absolute inset-0 rounded-full animate-ping opacity-20"
            style={{ background: '#C8872A' }} />
        </div>
        <div>
          <p className="text-sm font-medium text-[#1C1008]">Menyiapkan pembayaran</p>
          <p className="text-xs text-[#8B7355] mt-1">Harap tunggu sebentar...</p>
        </div>
        <div className="flex gap-1.5 justify-center">
          {[0,1,2].map(i => (
            <div key={i} className="w-1.5 h-1.5 rounded-full bg-[#C8872A] animate-bounce"
              style={{ animationDelay: `${i * 0.15}s` }} />
          ))}
        </div>
      </div>
    </div>
  )

  // ── Main ──
  return (
    <div style={{ fontFamily: "'DM Sans', sans-serif" }} className="min-h-screen bg-[#FAF8F4]">

      {/* ── Sticky top header — selaras MenuPage ── */}
      <div className="sticky top-0 z-40 bg-[#FAF8F4]/95 backdrop-blur-md border-b border-[#EDE8E0]">
        <div className="max-w-3xl mx-auto">
          <div className="flex items-center gap-3 px-4 py-3.5">
            <button onClick={() => navigate(-1)}
              className="w-9 h-9 rounded-full flex items-center justify-center active:scale-95 transition-transform flex-shrink-0"
              style={{ background: '#EDE8E0', border: '1px solid #D9D0C4', color: '#6B3F1F' }}>
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <div className="flex items-center gap-3 flex-1">
              {setting?.logo ? (
                <img src={setting.logo} alt={setting?.cafe_name || 'Logo'}
                  className="h-9 w-9 rounded-full object-cover flex-shrink-0"
                  style={{ border: '1.5px solid rgba(200,135,42,0.35)' }} />
              ) : (
                <div className="h-9 w-9 rounded-full flex items-center justify-center flex-shrink-0"
                  style={{ background: '#6B3F1F', border: '1.5px solid rgba(200,135,42,0.35)' }}>
                  <span style={{ fontFamily: "'Fraunces', serif", fontStyle: 'italic', fontWeight: 300, color: '#FAF2E6', fontSize: 15 }}>
                    {setting?.cafe_name?.charAt(0) || 'A'}
                  </span>
                </div>
              )}
              <div>
                <h1 style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 16, color: '#6B3F1F', lineHeight: 1.1, letterSpacing: '-0.01em' }}>
                  Konfirmasi Pembayaran
                </h1>
                <p style={{ color: '#8B7355', fontWeight: 300, letterSpacing: '0.04em', textTransform: 'uppercase', fontSize: 10, marginTop: 2 }}>
                  {invoice}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ── Hero gradient ── */}
      <div style={{ background: 'linear-gradient(160deg, #F5ECD7 0%, #EDE0C8 50%, #E8D5B0 100%)' }}>
        <div className="max-w-3xl mx-auto px-4 py-6 text-center">
          <div className="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center shadow-sm"
            style={{ background: 'rgba(255,255,255,0.7)', backdropFilter: 'blur(8px)' }}>
            <span className="text-2xl">🧾</span>
          </div>
          <p style={{ fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.12em', color: '#8B6840', fontWeight: 400, marginBottom: 6 }}>
            Konfirmasi Pembayaran
          </p>
          <h2 style={{ fontFamily: "'Fraunces', serif", fontSize: 20, fontWeight: 300, color: '#1C1008', lineHeight: 1.2, letterSpacing: '-0.02em' }}>
            Selesaikan<br /><em style={{ fontStyle: 'italic', color: '#C8872A' }}>pesanan</em> Anda
          </h2>
          <div className="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-full"
            style={{ background: 'rgba(28,16,8,0.07)' }}>
            <div className="w-1.5 h-1.5 rounded-full" style={{ background: '#C8872A' }} />
            <span style={{ fontSize: 12, color: '#5C3D1E', fontWeight: 500, letterSpacing: '0.02em' }}>
              {invoice}
            </span>
          </div>
        </div>
      </div>

      {/* ── Content — 1 kolom, max-w-3xl ── */}
      <div className="max-w-3xl mx-auto px-4 py-5 pb-32 space-y-3">

        {/* Total card */}
        <div className="rounded-2xl p-6 text-center border border-[#EDE8E0]"
          style={{ background: '#fff' }}>
          <p style={{ fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.1em', color: '#C4A882', fontWeight: 400, marginBottom: 8 }}>
            Total Pembayaran
          </p>
          <p style={{ fontFamily: "'Fraunces', serif", fontSize: 42, fontWeight: 300, color: '#1C1008', letterSpacing: '-0.02em', lineHeight: 1 }}>
            {formatPrice(grand_total)}
          </p>
          <div className="flex items-center justify-center gap-1.5 mt-3">
            <div className="w-1.5 h-1.5 rounded-full" style={{ background: '#4CAF50' }} />
            <p style={{ fontSize: 12, color: '#6B8F6B' }}>Siap diproses</p>
          </div>
        </div>

        {/* Detail transaksi */}
        <div className="rounded-2xl overflow-hidden border border-[#EDE8E0]" style={{ background: '#fff' }}>
          <div className="px-5 py-3.5 border-b border-[#F0EDE8]">
            <p style={{ fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.08em', color: '#C4A882', fontWeight: 600 }}>
              Detail Transaksi
            </p>
          </div>
          <div className="px-5 py-4 space-y-3.5">
            {[
              { emoji: '🧾', label: 'Invoice', value: invoice },
              { emoji: methodEmoji[payment_method] || '💳', label: 'Metode Pembayaran', value: methodLabel[payment_method] || payment_method },
            ].map((row, i, arr) => (
              <div key={row.label}>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2.5">
                    <div className="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                      style={{ background: '#F5ECD7' }}>
                      <span className="text-sm">{row.emoji}</span>
                    </div>
                    <span style={{ fontSize: 13, color: '#8B7355' }}>{row.label}</span>
                  </div>
                  <span style={{ fontSize: 13, color: '#1C1008', fontWeight: 500 }}>{row.value}</span>
                </div>
                {i < arr.length - 1 && <div className="h-px mt-3.5" style={{ background: '#F5F0EB' }} />}
              </div>
            ))}
          </div>
        </div>

        {/* Info box */}
        <div className="rounded-2xl p-4 flex items-start gap-3"
          style={{ background: '#FDF8F3', border: '1px solid #EDD9B8' }}>
          <span className="text-base mt-0.5 flex-shrink-0">💡</span>
          <p style={{ fontSize: 12, color: '#8B6840', lineHeight: 1.6 }}>
            Pastikan total pembayaran sudah sesuai sebelum melanjutkan.
            Pesanan akan diproses setelah pembayaran dikonfirmasi.
          </p>
        </div>

        {/* Error */}
        {error && (
          <div className="rounded-2xl p-4 flex items-start gap-3"
            style={{ background: '#FFF5F5', border: '1px solid #FFE0E0' }}>
            <span className="text-lg mt-0.5 flex-shrink-0">⚠️</span>
            <p style={{ fontSize: 13, color: '#C0392B', lineHeight: 1.5 }}>{error}</p>
          </div>
        )}

        {/* CTA buttons */}
        <div className="space-y-2.5 pt-1">
          {snapToken && (
            <button onClick={handlePay}
              className="w-full py-4 rounded-2xl text-sm font-bold transition-all active:scale-[0.98]"
              style={{
                background: 'linear-gradient(135deg, #1C1008 0%, #2D1F0E 100%)',
                color: '#FAF2E6',
                boxShadow: '0 4px 20px rgba(28,16,8,0.25)',
                letterSpacing: '0.01em',
              }}>
              <span className="flex items-center justify-center gap-2">
                <span>Bayar Sekarang</span>
                <span className="opacity-50">·</span>
                <span>{formatPrice(grand_total)}</span>
              </span>
            </button>
          )}

          <button onClick={() => navigate(`/order/${invoice}`)}
            className="w-full py-4 rounded-2xl text-sm transition-all active:scale-[0.98]"
            style={{ border: '1px solid #EDE8E0', background: '#fff', color: '#8B7355' }}>
            <span className="flex items-center justify-center gap-2">
              <span>💵</span>
              <span>Bayar Tunai di Kasir</span>
            </span>
          </button>
        </div>

        {/* Security note */}
        <div className="flex items-center justify-center gap-2 pt-1">
          <svg className="w-3.5 h-3.5 flex-shrink-0" style={{ color: '#C4A882' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <p style={{ fontSize: 11, color: '#C4A882', fontWeight: 300 }}>
            Pembayaran aman & terenkripsi
          </p>
        </div>
      </div>
    </div>
  )
}