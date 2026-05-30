import { useEffect, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { paymentApi } from '@/api'

export default function PaymentPage() {
  const location = useLocation()
  const navigate = useNavigate()
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

  // ── Loading ────────────────────────────────────────────────────────────────
  if (loading) return (
    <div className="min-h-screen bg-[#FAF8F4] flex items-center justify-center">
      <div className="text-center space-y-5">
        {/* Animated coffee cup */}
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

  // ── Main ───────────────────────────────────────────────────────────────────
  return (
    <div style={{ fontFamily: "'DM Sans', sans-serif" }}
      className="min-h-screen bg-[#FAF8F4]">

      {/* ── Hero header — warm gradient ─────────────────────────────────── */}
      <div className="relative overflow-hidden"
        style={{ background: 'linear-gradient(160deg, #F5ECD7 0%, #EDE0C8 50%, #E8D5B0 100%)' }}>

        {/* Decorative circles */}
        <div className="absolute -top-10 -right-10 w-40 h-40 rounded-full opacity-30"
          style={{ background: 'radial-gradient(circle, #C8872A, transparent)' }} />
        <div className="absolute -bottom-6 -left-6 w-28 h-28 rounded-full opacity-20"
          style={{ background: 'radial-gradient(circle, #8B5E3C, transparent)' }} />

        {/* Back button */}
        <button
          onClick={() => navigate(-1)}
          className="absolute top-12 left-4 w-9 h-9 rounded-full flex items-center justify-center"
          style={{ background: 'rgba(28,16,8,0.08)' }}
        >
          <svg className="w-4 h-4" fill="none" stroke="#1C1008" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <div className="text-center px-4 pt-16 pb-10 relative z-10">
          {/* Icon */}
          <div className="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-sm"
            style={{ background: 'rgba(255,255,255,0.7)', backdropFilter: 'blur(8px)' }}>
            <span className="text-3xl">🧾</span>
          </div>

          <p style={{ fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.12em', color: '#8B6840', fontWeight: 400, marginBottom: 6 }}>
            Konfirmasi Pembayaran
          </p>
          <h1 style={{ fontFamily: "'Fraunces', serif", fontSize: 26, fontWeight: 300, color: '#1C1008', lineHeight: 1.2, letterSpacing: '-0.02em' }}>
            Selesaikan<br /><em style={{ fontStyle: 'italic', color: '#C8872A' }}>pesanan</em> Anda
          </h1>

          {/* Invoice badge */}
          <div className="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-full"
            style={{ background: 'rgba(28,16,8,0.07)' }}>
            <div className="w-1.5 h-1.5 rounded-full" style={{ background: '#C8872A' }} />
            <span style={{ fontSize: 12, color: '#5C3D1E', fontWeight: 500, letterSpacing: '0.02em' }}>
              {invoice}
            </span>
          </div>
        </div>
      </div>

      {/* ── Content ────────────────────────────────────────────────────────── */}
      <div className="px-4 py-5 space-y-3 max-w-lg mx-auto">

        {/* Total card — hero element */}
        <div className="rounded-3xl p-6 text-center shadow-sm"
          style={{ background: '#fff', border: '1px solid #F0EDE8' }}>
          <p style={{ fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.1em', color: '#C4A882', fontWeight: 400, marginBottom: 8 }}>
            Total Pembayaran
          </p>
          <p style={{ fontFamily: "'Fraunces', serif", fontSize: 38, fontWeight: 300, color: '#1C1008', letterSpacing: '-0.02em', lineHeight: 1 }}>
            {formatPrice(grand_total)}
          </p>
          <div className="flex items-center justify-center gap-1.5 mt-3">
            <div className="w-1.5 h-1.5 rounded-full" style={{ background: '#4CAF50' }} />
            <p style={{ fontSize: 12, color: '#6B8F6B', fontWeight: 400 }}>
              Siap diproses
            </p>
          </div>
        </div>

        {/* Detail card */}
        <div className="rounded-3xl overflow-hidden shadow-sm"
          style={{ background: '#fff', border: '1px solid #F0EDE8' }}>
          <div className="px-5 py-4 border-b border-[#F0EDE8]">
            <p style={{ fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.08em', color: '#C4A882', fontWeight: 400 }}>
              Detail Transaksi
            </p>
          </div>
          <div className="px-5 py-4 space-y-3.5">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-xl flex items-center justify-center"
                  style={{ background: '#F5ECD7' }}>
                  <span className="text-sm">🧾</span>
                </div>
                <span style={{ fontSize: 13, color: '#8B7355', fontWeight: 400 }}>Invoice</span>
              </div>
              <span style={{ fontSize: 13, color: '#1C1008', fontWeight: 500 }}>{invoice}</span>
            </div>

            <div className="h-px" style={{ background: '#F5F0EB' }} />

            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-xl flex items-center justify-center"
                  style={{ background: '#F5ECD7' }}>
                  <span className="text-sm">
                    {payment_method === 'qris' ? '📱' :
                     payment_method === 'cash' ? '💵' :
                     payment_method === 'transfer' ? '🏦' : '💳'}
                  </span>
                </div>
                <span style={{ fontSize: 13, color: '#8B7355', fontWeight: 400 }}>Metode</span>
              </div>
              <span style={{ fontSize: 13, color: '#1C1008', fontWeight: 500 }}>
                {methodLabel[payment_method] || payment_method}
              </span>
            </div>
          </div>
        </div>

        {/* Error */}
        {error && (
          <div className="rounded-2xl p-4 flex items-start gap-3"
            style={{ background: '#FFF5F5', border: '1px solid #FFE0E0' }}>
            <span className="text-lg mt-0.5">⚠️</span>
            <p style={{ fontSize: 13, color: '#C0392B', lineHeight: 1.5 }}>{error}</p>
          </div>
        )}

        {/* Info box */}
        <div className="rounded-2xl p-4 flex items-start gap-3"
          style={{ background: '#F5ECD7', border: '1px solid #E8D5B0' }}>
          <span className="text-base mt-0.5">💡</span>
          <p style={{ fontSize: 12, color: '#8B6840', lineHeight: 1.6 }}>
            Pastikan total pembayaran sudah sesuai sebelum melanjutkan.
            Pesanan akan diproses setelah pembayaran dikonfirmasi.
          </p>
        </div>

        {/* ── CTA Buttons ──────────────────────────────────────────────── */}
        <div className="space-y-2.5 pt-1">
          {snapToken && (
            <button
              onClick={handlePay}
              className="w-full py-4 rounded-2xl text-sm font-bold transition-all active:scale-[0.98]"
              style={{
                background: 'linear-gradient(135deg, #1C1008 0%, #2D1F0E 100%)',
                color: '#FAF2E6',
                boxShadow: '0 4px 20px rgba(28,16,8,0.25)',
                fontFamily: "'DM Sans', sans-serif",
                letterSpacing: '0.01em',
              }}
            >
              <span className="flex items-center justify-center gap-2">
                <span>Bayar Sekarang</span>
                <span className="opacity-70">·</span>
                <span>{formatPrice(grand_total)}</span>
              </span>
            </button>
          )}

          <button
            onClick={() => navigate(`/order/${invoice}`)}
            className="w-full py-4 rounded-2xl text-sm transition-all active:scale-[0.98]"
            style={{
              border: '1px solid #E5DDD3',
              background: '#fff',
              color: '#8B7355',
              fontFamily: "'DM Sans', sans-serif",
            }}
          >
            <span className="flex items-center justify-center gap-2">
              <span>💵</span>
              <span>Bayar Tunai di Kasir</span>
            </span>
          </button>
        </div>

        {/* Security note */}
        <div className="flex items-center justify-center gap-2 pt-1 pb-4">
          <svg className="w-3.5 h-3.5" style={{ color: '#C4A882' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
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