import { useState, useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { Star, CalendarDays } from 'lucide-react'
import { categoryApi, productApi, settingApi, bannerApi } from '@/api'
import useCartStore from '@/store/cartStore'
import useSettingStore from '@/store/settingStore'
import ProductCard from '@/components/menu/ProductCard'
import CheckoutBar from '@/components/cart/CheckoutBar'
import LanguageSwitcher from '@/components/common/LanguageSwitcher'

const fontLink = document.getElementById('alnes-fonts')
if (!fontLink) {
  const link = document.createElement('link')
  link.id   = 'alnes-fonts'
  link.rel  = 'stylesheet'
  link.href = 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;1,9..144,300&family=DM+Sans:wght@300;400;500&display=swap'
  document.head.appendChild(link)
}

function BannerSlider({ banners }) {
  const [current, setCurrent] = useState(0)
  const timerRef = useRef(null)

  useEffect(() => {
    if (banners.length <= 1) return
    timerRef.current = setInterval(() => setCurrent(p => (p + 1) % banners.length), 3500)
    return () => clearInterval(timerRef.current)
  }, [banners.length])

  if (!banners.length) return null

  return (
    <div className="px-4 pt-4 pb-2">
      <div className="relative overflow-hidden rounded-2xl h-[160px] md:h-[280px] lg:h-[360px]"
        style={{ background: '#2C1A0E' }}>
        {banners.map((banner, i) => (
          <div key={banner.id} className="absolute inset-0 transition-opacity duration-700"
            style={{ opacity: i === current ? 1 : 0 }}>
            {banner.image ? (
              <img src={banner.image} alt={banner.title}
                className="w-full h-full object-cover"
                loading="eager"
                fetchPriority={i === 0 ? 'high' : 'auto'}
                width={800} height={360} />
            ) : (
              <div className="w-full h-full flex items-end p-5"
                style={{ background: 'linear-gradient(135deg, #6B3F1F 0%, #3D2B1A 100%)' }}>
                <p style={{ fontFamily: "'Fraunces', serif", fontSize: 20, fontWeight: 300, color: '#FAF2E6', lineHeight: 1.2 }}>
                  {banner.title}
                </p>
              </div>
            )}
            {banner.image && (
              <div className="absolute inset-0 flex items-end p-5"
                style={{ background: 'linear-gradient(to top, rgba(107,63,31,0.75) 0%, rgba(107,63,31,0.1) 55%, transparent 100%)' }}>
                <p style={{ fontFamily: "'Fraunces', serif", fontSize: 18, fontWeight: 300, color: '#FAF2E6', lineHeight: 1.3 }}>
                  {banner.title}
                </p>
              </div>
            )}
          </div>
        ))}
        {banners.length > 1 && (
          <div className="absolute bottom-4 right-5 flex gap-1.5">
            {banners.map((_, i) => (
              <button key={i} onClick={() => setCurrent(i)}
                className="rounded-full transition-all duration-300"
                style={{ width: i === current ? 18 : 6, height: 6,
                  background: i === current ? '#C8872A' : 'rgba(255,255,255,0.5)' }} />
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

export default function MenuPage() {
  const navigate    = useNavigate()
  const { t }       = useTranslation()
  const tableNumber = useCartStore(s => s.tableNumber)
  const tableId     = useCartStore(s => s.tableId)
  const { setting, setSetting } = useSettingStore()

  const [categories, setCategories]         = useState([])
  const [products, setProducts]             = useState([])
  const [banners, setBanners]               = useState([])
  const [activeCategory, setActiveCategory] = useState(null)
  const [activePilihan, setActivePilihan]   = useState(null)
  const [searchQuery, setSearchQuery]       = useState('')
  const [showSearch, setShowSearch]         = useState(false)
  const [showPilihan, setShowPilihan]       = useState(false)
  const [loading, setLoading]               = useState(true)

  useEffect(() => { if (!tableId) navigate('/') }, [tableId])

  useEffect(() => {
    if (!setting) settingApi.get().then(({ data }) => setSetting(data.data)).catch(() => {})
  }, [])

  useEffect(() => {
    categoryApi.list().then(({ data }) => setCategories(data.data || [])).catch(() => setCategories([]))
    bannerApi.list().then(({ data }) => setBanners(data.data || [])).catch(() => setBanners([]))
  }, [])

  useEffect(() => {
    setLoading(true)
    const params = {}
    if (activeCategory) params.category_id = activeCategory
    if (activePilihan === 'best_seller') params.is_best_seller = true
    if (activePilihan === 'beverages') {
      const ids = categories.filter(c => c.type === 'beverages').map(c => c.id)
      if (ids.length) params.category_id = ids[0]
    }
    if (searchQuery.length >= 2) {
      productApi.search(searchQuery)
        .then(({ data }) => { setProducts(data.data || []); setLoading(false) })
        .catch(() => { setProducts([]); setLoading(false) })
      return
    }
    productApi.list(params)
      .then(({ data }) => { setProducts(data.data || []); setLoading(false) })
      .catch(() => { setProducts([]); setLoading(false) })
  }, [activeCategory, activePilihan, searchQuery, categories])

  const pilihanOptions = [
    { key: 'best_seller', label: t('filter_best'),      emoji: '⭐️' },
    { key: 'beverages',   label: t('filter_beverages'), emoji: '☕' },
  ]

  return (
    <div style={{ fontFamily: "'DM Sans', sans-serif" }} className="min-h-screen bg-[#FAF8F4]">

      {/* Header sticky */}
      <div className="sticky top-0 z-40 bg-[#FAF8F4]/95 backdrop-blur-md border-b border-[#EDE8E0]">
        <div className="max-w-3xl mx-auto">
          <div className="flex items-center justify-between px-4 py-3">
            <div className="flex items-center gap-3">
              {setting?.logo ? (
                <img src={setting.logo} alt={setting?.cafe_name || 'Logo'}
                  className="h-11 w-11 rounded-full object-cover flex-shrink-0"
                  style={{ border: '1.5px solid rgba(200,135,42,0.35)' }}
                  loading="eager" fetchPriority="high" width={44} height={44} />
              ) : (
                <div className="h-11 w-11 rounded-full flex items-center justify-center flex-shrink-0"
                  style={{ background: '#6B3F1F', border: '1.5px solid rgba(200,135,42,0.35)' }}>
                  <span style={{ fontFamily: "'Fraunces', serif", fontStyle: 'italic', fontWeight: 300, color: '#FAF2E6', fontSize: 17 }}>
                    {setting?.cafe_name?.charAt(0) || 'A'}
                  </span>
                </div>
              )}
              <div>
                <h1 style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 16, color: '#6B3F1F', lineHeight: 1.1, letterSpacing: '-0.01em' }}>
                  {setting?.cafe_name || 'Alnes Coffee'}
                </h1>
                <p style={{ color: '#8B7355', fontWeight: 300, letterSpacing: '0.04em', textTransform: 'uppercase', fontSize: 10, marginTop: 2 }}>
                  {t('table')} {tableNumber}
                </p>
              </div>
            </div>

            <div className="flex items-center gap-2">

              {/* Tombol Loyalty */}
              <button onClick={() => navigate('/loyalty')}
                className="w-9 h-9 rounded-full flex items-center justify-center transition-all active:scale-95"
                style={{ background: '#FDF3E7', border: '1px solid #EDD9B8' }}
                title="Loyalty Points">
                <Star className="w-4 h-4" style={{ color: '#C8872A' }} fill="#C8872A" />
              </button>

              {/* Tombol Reservasi */}
              <button onClick={() => navigate('/reservation')}
                className="w-9 h-9 rounded-full flex items-center justify-center transition-all active:scale-95"
                style={{ background: '#EDE8E0', border: '1px solid #D9D0C4' }}
                title="Reservasi Meja">
                <CalendarDays className="w-4 h-4" style={{ color: '#6B3F1F' }} />
              </button>

              <LanguageSwitcher />

              <button onClick={() => { setShowSearch(s => !s); setSearchQuery('') }}
                className="w-9 h-9 rounded-full flex items-center justify-center transition-all active:scale-95"
                style={{ background: '#EDE8E0', border: '1px solid #D9D0C4', color: '#6B3F1F' }}>
                {showSearch ? (
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                ) : (
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                )}
              </button>
            </div>
          </div>

          {showSearch && (
            <div className="px-4 pb-3">
              <div className="relative">
                <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style={{ color: '#C4A882' }}
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input autoFocus value={searchQuery} onChange={e => setSearchQuery(e.target.value)}
                  placeholder={t('search_placeholder')}
                  className="w-full rounded-3xl px-4 pl-9 py-2.5 text-sm outline-none border-none"
                  style={{ background: '#EDE8E0', color: '#6B3F1F', fontFamily: "'DM Sans', sans-serif" }} />
              </div>
            </div>
          )}

          {!showSearch && (
            <div className="flex items-center gap-2 px-4 pb-3 overflow-x-auto" style={{ scrollbarWidth: 'none' }}>
              <button onClick={() => setShowPilihan(true)}
                className="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs transition-all"
                style={{
                  fontFamily: "'DM Sans', sans-serif", fontWeight: 400, border: '0.5px solid',
                  borderColor: activePilihan ? '#6B3F1F' : '#EDE8E0',
                  background: activePilihan ? '#6B3F1F' : '#fff',
                  color: activePilihan ? '#FAF2E6' : '#5C4433', whiteSpace: 'nowrap',
                }}>
                {activePilihan ? pilihanOptions.find(p => p.key === activePilihan)?.label : t('filter')}
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              {categories.map(cat => (
                <button key={cat.id}
                  onClick={() => { setActiveCategory(activeCategory === cat.id ? null : cat.id); setActivePilihan(null) }}
                  className="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-full text-xs transition-all"
                  style={{
                    fontFamily: "'DM Sans', sans-serif", fontWeight: 400, border: '0.5px solid',
                    borderColor: activeCategory === cat.id ? '#6B3F1F' : '#EDE8E0',
                    background: activeCategory === cat.id ? '#6B3F1F' : '#fff',
                    color: activeCategory === cat.id ? '#FAF2E6' : '#5C4433', whiteSpace: 'nowrap',
                  }}>
                  {cat.icon && <span>{cat.icon}</span>}
                  <span>{cat.name}</span>
                </button>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Main content */}
      <div className="max-w-3xl mx-auto pb-28">

        {!showSearch && (
          <div className="px-4 pt-5 pb-2">
            <p style={{ fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.1em', color: '#C4A882', fontWeight: 300, marginBottom: 4 }}>
              {t('welcome')}
            </p>
            <h2 style={{ fontFamily: "'Fraunces', serif", fontSize: 24, fontWeight: 300, color: '#6B3F1F', lineHeight: 1.2, letterSpacing: '-0.02em' }}>
              {t('tagline').split('ingin')[0]}
              <em style={{ fontStyle: 'italic', color: '#C8872A' }}>ingin</em>
              <br />{t('tagline').split('ingin')[1]}
            </h2>
          </div>
        )}

        {!showSearch && <BannerSlider banners={banners} />}

        <div className="px-4 pt-4">
          {loading ? (
            <div className="space-y-0 mt-2">
              {[...Array(5)].map((_, i) => (
                <div key={i} className="flex gap-3 py-4 border-b border-[#EDE8E0]">
                  <div className="w-20 h-20 rounded-2xl flex-shrink-0 animate-pulse" style={{ background: '#EDE8E0' }} />
                  <div className="flex-1 space-y-2 pt-1">
                    <div className="h-4 rounded-lg animate-pulse w-3/4" style={{ background: '#EDE8E0' }} />
                    <div className="h-3 rounded-lg animate-pulse w-full" style={{ background: '#EDE8E0' }} />
                    <div className="h-4 rounded-lg animate-pulse w-1/3 mt-3" style={{ background: '#EDE8E0' }} />
                  </div>
                </div>
              ))}
            </div>
          ) : products.length === 0 ? (
            <div className="text-center py-20">
              <div className="text-4xl mb-3">🔍</div>
              <p style={{ fontSize: 14, color: '#8B7355', fontWeight: 300 }}>{t('not_found')}</p>
            </div>
          ) : (
            <div>
              {products.map((product, index) => (
                <ProductCard key={product.id} product={product} priority={index < 3} />
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Pilihan bottom sheet */}
      {showPilihan && (
        <div className="fixed inset-0 z-50" onClick={() => setShowPilihan(false)}>
          <div className="absolute inset-0" style={{ background: 'rgba(107,63,31,0.4)', backdropFilter: 'blur(4px)' }} />
          <div className="absolute bottom-0 left-0 right-0 rounded-t-3xl p-6 shadow-2xl"
            style={{ background: '#fff' }} onClick={e => e.stopPropagation()}>
            <div className="w-9 h-[3px] rounded-full mx-auto mb-5" style={{ background: '#EDE8E0' }} />
            <div className="flex items-center justify-between mb-5">
              <h3 style={{ fontFamily: "'Fraunces', serif", fontWeight: 400, fontSize: 18, color: '#6B3F1F', letterSpacing: '-0.01em' }}>
                {t('filter_menu')}
              </h3>
              <button onClick={() => setShowPilihan(false)}
                className="w-8 h-8 rounded-full flex items-center justify-center"
                style={{ background: '#EDE8E0', color: '#6B3F1F' }}>✕</button>
            </div>
            <div className="space-y-2">
              {pilihanOptions.map(opt => (
                <button key={opt.key}
                  onClick={() => { setActivePilihan(activePilihan === opt.key ? null : opt.key); setActiveCategory(null); setShowPilihan(false) }}
                  className="w-full flex items-center justify-between px-4 py-3.5 rounded-2xl transition-all"
                  style={{
                    border: '0.5px solid',
                    borderColor: activePilihan === opt.key ? '#6B3F1F' : '#EDE8E0',
                    background: activePilihan === opt.key ? '#6B3F1F' : '#FAF8F4',
                    fontFamily: "'DM Sans', sans-serif",
                  }}>
                  <div className="flex items-center gap-3">
                    <span className="text-xl">{opt.emoji}</span>
                    <span style={{ fontSize: 14, fontWeight: 400, color: activePilihan === opt.key ? '#FAF2E6' : '#6B3F1F' }}>
                      {opt.label}
                    </span>
                  </div>
                  {activePilihan === opt.key && (
                    <div className="w-5 h-5 rounded-full flex items-center justify-center" style={{ background: '#C8872A' }}>
                      <svg className="w-3 h-3" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                  )}
                </button>
              ))}
            </div>
          </div>
        </div>
      )}

      <CheckoutBar />
    </div>
  )
}