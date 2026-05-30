import { useEffect, useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { tableApi } from '@/api'
import useCartStore from '@/store/cartStore'

const fontLink = document.getElementById('alnes-fonts')
if (!fontLink) {
  const link = document.createElement('link')
  link.id   = 'alnes-fonts'
  link.rel  = 'stylesheet'
  link.href = 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;1,9..144,300&family=DM+Sans:wght@300;400;500&display=swap'
  document.head.appendChild(link)
}

export default function TablePage() {
  const { slug }  = useParams()
  const navigate  = useNavigate()
  const { t }     = useTranslation()
  const setTable  = useCartStore(s => s.setTable)
  const [status, setStatus] = useState('loading')

  useEffect(() => {
    tableApi.show(slug)
      .then(({ data }) => {
        const table = data.data
        setTable(slug, table.id, table.table_number)
        setTimeout(() => navigate('/menu'), 1400)
      })
      .catch(() => setStatus('error'))
  }, [slug])

  return (
    <div style={{
      fontFamily: "'DM Sans', sans-serif", minHeight: '100svh',
      background: '#FAF8F4', display: 'flex', flexDirection: 'column',
      alignItems: 'center', justifyContent: 'center',
      padding: '48px 32px', position: 'relative', overflow: 'hidden',
    }}>

      {/* Decorative circles */}
      <div style={{ position: 'absolute', width: 280, height: 280, borderRadius: '50%',
        border: '0.5px solid rgba(196,168,130,0.15)', top: -80, right: -80, pointerEvents: 'none' }} />
      <div style={{ position: 'absolute', width: 200, height: 200, borderRadius: '50%',
        border: '0.5px solid rgba(196,168,130,0.12)', bottom: -50, left: -50, pointerEvents: 'none' }} />
      <div style={{ position: 'absolute', width: 120, height: 120, borderRadius: '50%',
        border: '0.5px solid rgba(196,168,130,0.08)', top: '40%', left: -40, pointerEvents: 'none' }} />

      {/* Logo */}
      <div style={{ position: 'relative', marginBottom: 28 }}>
        <div style={{ position: 'absolute', inset: -6, borderRadius: 28,
          border: '0.5px solid rgba(196,168,130,0.25)', pointerEvents: 'none' }} />
        <div style={{
          width: 80, height: 80, borderRadius: 24, background: '#6B3F1F',
          border: '1.5px solid rgba(200,135,42,0.35)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          boxShadow: '0 8px 24px rgba(107,63,31,0.25)',
        }}>
          <span style={{ fontFamily: "'Fraunces', serif", fontSize: 32,
            fontStyle: 'italic', fontWeight: 300, color: '#FAF2E6' }}>A</span>
        </div>
      </div>

      {/* Loading state */}
      {status === 'loading' && (
        <>
          <h1 style={{
            fontFamily: "'Fraunces', serif", fontSize: 28, fontWeight: 300,
            color: '#6B3F1F', letterSpacing: '-0.03em', marginBottom: 6, textAlign: 'center',
          }}>
            Alnes <em style={{ fontStyle: 'italic', color: '#C8872A' }}>Coffee</em>
          </h1>

          <p style={{
            fontSize: 13, color: '#8B7355', fontWeight: 300, textAlign: 'center',
            lineHeight: 1.6, letterSpacing: '0.01em', marginBottom: 36,
          }}>
            {t('preparing_menu')}
          </p>

          {/* Bouncing dots */}
          <div style={{ display: 'flex', gap: 8, alignItems: 'center', marginBottom: 32 }}>
            {[0, 1, 2].map(i => (
              <div key={i} style={{
                width: 7, height: 7, borderRadius: '50%', background: '#C8872A',
                animation: `alnesBounce 1.2s ease-in-out ${i * 0.2}s infinite`,
              }} />
            ))}
          </div>

          {/* Progress bar */}
          <div style={{
            width: 180, height: 1.5, background: '#EDE8E0',
            borderRadius: 1, overflow: 'hidden', marginBottom: 48,
          }}>
            <div style={{
              height: '100%', background: 'linear-gradient(90deg, #6B3F1F, #C8872A)',
              borderRadius: 1, animation: 'alnesFill 1.3s ease-out forwards',
            }} />
          </div>

          {/* QR detected chip */}
          <div style={{
            position: 'absolute', bottom: 36, left: '50%', transform: 'translateX(-50%)',
            background: '#fff', border: '0.5px solid #EDE8E0', borderRadius: 20,
            padding: '8px 18px', display: 'flex', alignItems: 'center', gap: 8,
            whiteSpace: 'nowrap', boxShadow: '0 2px 12px rgba(107,63,31,0.08)',
          }}>
            <svg width="15" height="15" fill="none" stroke="#C8872A" strokeWidth="1.5" viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="3" width="7" height="7" rx="1"/>
              <rect x="3" y="14" width="7" height="7" rx="1"/>
              <path d="M14 14h2v2h-2zm4 0h3v3h-3zm0 4v3h-3v-2"/>
            </svg>
            <span style={{ fontSize: 12, color: '#8B7355', fontWeight: 300 }}>
              {t('qr_detected')}
            </span>
          </div>
        </>
      )}

      {/* Error state */}
      {status === 'error' && (
        <div style={{ textAlign: 'center' }}>
          <div style={{
            width: 72, height: 72, borderRadius: '50%',
            background: '#F5EDE4', border: '1px solid rgba(107,63,31,0.12)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            margin: '0 auto 20px', boxShadow: '0 4px 16px rgba(107,63,31,0.08)',
          }}>
            <svg width="28" height="28" fill="none" stroke="#C8872A" strokeWidth="1.5" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 8v4m0 4h.01"/>
            </svg>
          </div>
          <h2 style={{
            fontFamily: "'Fraunces', serif", fontSize: 20, fontWeight: 400,
            color: '#6B3F1F', marginBottom: 8, letterSpacing: '-0.02em',
          }}>
            {t('table_not_found')}
          </h2>
          <p style={{ fontSize: 13, color: '#8B7355', fontWeight: 300, lineHeight: 1.6 }}>
            {t('scan_again')}
          </p>

          <div style={{
            display: 'inline-flex', alignItems: 'center', gap: 6, marginTop: 24,
            background: '#fff', border: '0.5px solid #EDE8E0', borderRadius: 20,
            padding: '8px 16px', boxShadow: '0 2px 8px rgba(107,63,31,0.06)',
          }}>
            <svg width="13" height="13" fill="none" stroke="#C4A882" strokeWidth="1.5" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span style={{ fontSize: 11, color: '#8B7355', fontWeight: 300 }}>
              {t('scan_again')}
            </span>
          </div>
        </div>
      )}

      <style>{`
        @keyframes alnesBounce {
          0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
          40% { transform: translateY(-8px); opacity: 1; }
        }
        @keyframes alnesFill {
          from { width: 0%; }
          to   { width: 100%; }
        }
      `}</style>
    </div>
  )
}