    import { useEffect, useState } from 'react'
    import { useNavigate } from 'react-router-dom'
    import { useTranslation } from 'react-i18next'
    import { loyaltyApi } from '@/api'
    import useLoyaltyStore from '@/store/loyaltyStore'
    import useCartStore from '@/store/cartStore'
    import useSettingStore from '@/store/settingStore'
    import { formatPriceShort } from '@/utils/format'

    const TIER_CONFIG = {
    bronze:   { label: 'Bronze',   emoji: '🥉', color: '#CD7F32', bg: '#FDF3E7' },
    silver:   { label: 'Silver',   emoji: '🥈', color: '#9E9E9E', bg: '#F5F5F5' },
    gold:     { label: 'Gold',     emoji: '🥇', color: '#FFD700', bg: '#FFFDE7' },
    platinum: { label: 'Platinum', emoji: '💎', color: '#6C5CE7', bg: '#F3F0FF' },
    }

    const TIER_ORDER  = ['bronze', 'silver', 'gold', 'platinum']
    const TIER_POINTS = { bronze: 0, silver: 1000, gold: 5000, platinum: 15000 }

    export default function LoyaltyPage() {
    const navigate    = useNavigate()
    const { t }       = useTranslation()
    const tableNumber = useCartStore(s => s.tableNumber)
    const { setting } = useSettingStore()
    const { customer, nextTier, setCustomer, setHistory, history } = useLoyaltyStore()

    const [tab, setTab]                       = useState('points')
    const [phone, setPhone]                   = useState('')
    const [loading, setLoading]               = useState(false)
    const [rewards, setRewards]               = useState([])
    const [error, setError]                   = useState('')
    const [redeemLoading, setRedeemLoading]   = useState(null)
    const [redeemSuccess, setRedeemSuccess]   = useState('')

    useEffect(() => {
        if (customer?.phone) {
        setPhone(customer.phone)
        loadRewards(customer.phone)
        loadHistory(customer.phone)
        }
    }, [])

    const handleCheck = async () => {
        if (!phone) return
        setLoading(true)
        setError('')
        try {
        const { data } = await loyaltyApi.check(phone)
        setCustomer(data.data.customer, data.data.next_tier)
        await loadRewards(phone)
        await loadHistory(phone)
        } catch (e) {
        setError(e.response?.data?.message || 'Customer tidak ditemukan')
        } finally {
        setLoading(false)
        }
    }

    const loadRewards = async (p) => {
        try { const { data } = await loyaltyApi.rewards(p); setRewards(data.data.rewards) } catch {}
    }

    const loadHistory = async (p) => {
        try { const { data } = await loyaltyApi.history(p); setHistory(data.data.history) } catch {}
    }

    const handleRedeem = async (rewardId) => {
        setRedeemLoading(rewardId)
        setRedeemSuccess('')
        try {
        const { data } = await loyaltyApi.redeem({ phone: customer.phone, reward_id: rewardId })
        setRedeemSuccess(`✓ ${data.data.reward_name} berhasil ditukar!`)
        await handleCheck()
        } catch (e) {
        setError(e.response?.data?.message || 'Gagal redeem reward')
        } finally {
        setRedeemLoading(null)
        }
    }

    const tier        = customer ? TIER_CONFIG[customer.tier] : null
    const tierIndex   = customer ? TIER_ORDER.indexOf(customer.tier) : 0
    const nextTierKey = TIER_ORDER[tierIndex + 1]
    const progress    = customer && nextTierKey
        ? Math.min(100, ((customer.total_points_earned - TIER_POINTS[customer.tier]) /
            (TIER_POINTS[nextTierKey] - TIER_POINTS[customer.tier])) * 100)
        : 100

    return (
        <div style={{ fontFamily: "'DM Sans', sans-serif" }} className="min-h-screen bg-[#FAF8F4]">

        {/* ── Header — sama persis dengan MenuPage ── */}
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

                {/* Logo + title — mengikuti struktur header MenuPage */}
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
                    Loyalty Program
                    </h1>
                    <p style={{ color: '#8B7355', fontWeight: 300, letterSpacing: '0.04em', textTransform: 'uppercase', fontSize: 10, marginTop: 2 }}>
                    Kumpulkan poin, dapatkan reward
                    </p>
                </div>
                </div>
            </div>
            </div>
        </div>

        {/* ── Content — dibungkus max-w-3xl mx-auto seperti MenuPage ── */}
        <div className="max-w-3xl mx-auto px-4 py-5 space-y-4 pb-28">

            {/* Input phone */}
            {!customer && (
            <div className="bg-white rounded-2xl p-5 border border-[#EDE8E0]">
                <div className="text-center mb-5">
                <div className="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center"
                    style={{ background: 'linear-gradient(135deg, #6B3F1F, #C8872A)' }}>
                    <span style={{ fontSize: 28 }}>⭐</span>
                </div>
                <p style={{ fontFamily: "'Fraunces', serif", fontSize: 20, color: '#1C1008', fontWeight: 400 }}>
                    Cek Poin Kamu
                </p>
                <p style={{ fontSize: 13, color: '#8B7355', marginTop: 4 }}>
                    Masukkan nomor HP yang digunakan saat order
                </p>
                </div>

                <div className="flex gap-2">
                <input
                    value={phone}
                    onChange={e => setPhone(e.target.value)}
                    placeholder="08xx-xxxx-xxxx"
                    type="tel"
                    className="flex-1 outline-none"
                    style={{
                    background: '#FAF8F4', border: '1px solid #EDE8E0',
                    borderRadius: 12, padding: '11px 16px', fontSize: 14, color: '#1C1008',
                    }}
                    onFocus={e => e.target.style.borderColor = '#C8872A'}
                    onBlur={e => e.target.style.borderColor = '#EDE8E0'}
                    onKeyDown={e => e.key === 'Enter' && handleCheck()}
                />
                <button onClick={handleCheck} disabled={loading}
                    className="px-5 rounded-xl active:scale-95 transition-transform"
                    style={{ background: '#1C1008', color: '#FAF2E6', fontSize: 13, fontWeight: 600 }}>
                    {loading ? '...' : 'Cek'}
                </button>
                </div>

                {error && (
                <p style={{ fontSize: 12, color: '#ef4444', marginTop: 8, textAlign: 'center' }}>
                    ✕ {error}
                </p>
                )}
            </div>
            )}

            {/* Customer info */}
            {customer && tier && (
            <>
                {/* Membership card */}
                <div className="rounded-2xl overflow-hidden"
                style={{ background: 'linear-gradient(135deg, #1C1008 0%, #6B3F1F 60%, #C8872A 100%)' }}>

                <div className="px-5 pt-5 pb-4">
                    <div className="flex items-start justify-between mb-4">
                    <div>
                        <p style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase', letterSpacing: '0.1em' }}>
                        Member
                        </p>
                        <p style={{ fontSize: 18, fontWeight: 600, color: 'white', marginTop: 2 }}>
                        {customer.name}
                        </p>
                        <p style={{ fontSize: 12, color: 'rgba(255,255,255,0.5)', marginTop: 1 }}>
                        {customer.phone}
                        </p>
                    </div>
                    <div className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full"
                        style={{ background: 'rgba(255,255,255,0.15)' }}>
                        <span style={{ fontSize: 16 }}>{tier.emoji}</span>
                        <span style={{ fontSize: 12, fontWeight: 600, color: 'white' }}>{tier.label}</span>
                    </div>
                    </div>

                    <div className="rounded-xl px-4 py-3" style={{ background: 'rgba(255,255,255,0.1)' }}>
                    <p style={{ fontSize: 10, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase', letterSpacing: '0.1em' }}>
                        Saldo Poin
                    </p>
                    <p style={{ fontFamily: "'Fraunces', serif", fontSize: 36, fontWeight: 400, color: '#FAF2E6', letterSpacing: '-0.02em', lineHeight: 1.1 }}>
                        {customer.points_balance.toLocaleString()}
                    </p>
                    <p style={{ fontSize: 11, color: 'rgba(255,255,255,0.4)', marginTop: 2 }}>
                        Total diperoleh: {customer.total_points_earned.toLocaleString()} poin
                    </p>
                    </div>
                </div>

                {nextTierKey ? (
                    <div className="px-5 pb-5">
                    <div className="flex justify-between items-center mb-1.5">
                        <p style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>
                        Menuju {TIER_CONFIG[nextTierKey]?.emoji} {TIER_CONFIG[nextTierKey]?.label}
                        </p>
                        <p style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>
                        {nextTier?.points_needed?.toLocaleString()} poin lagi
                        </p>
                    </div>
                    <div style={{ height: 4, background: 'rgba(255,255,255,0.15)', borderRadius: 2, overflow: 'hidden' }}>
                        <div style={{
                        height: '100%', borderRadius: 2,
                        background: 'linear-gradient(90deg, #FAF2E6, #C8872A)',
                        width: `${progress}%`, transition: 'width 0.8s ease',
                        }} />
                    </div>
                    </div>
                ) : (
                    <div className="px-5 pb-5">
                    <p style={{ fontSize: 12, color: 'rgba(255,255,255,0.5)', textAlign: 'center' }}>
                        💎 Kamu sudah di tier tertinggi!
                    </p>
                    </div>
                )}
                </div>

                {/* Tier benefits */}
                <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0]">
                <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 12 }}>
                    Keuntungan Tier
                </p>
                <div className="grid grid-cols-4 gap-2">
                    {[
                    { tier: 'bronze',   emoji: '🥉', multiplier: '1x',    min: '0' },
                    { tier: 'silver',   emoji: '🥈', multiplier: '1.25x', min: '1.000' },
                    { tier: 'gold',     emoji: '🥇', multiplier: '1.5x',  min: '5.000' },
                    { tier: 'platinum', emoji: '💎', multiplier: '2x',    min: '15.000' },
                    ].map(t => (
                    <div key={t.tier} className="rounded-xl p-3"
                        style={{
                        background: customer.tier === t.tier ? TIER_CONFIG[t.tier].bg : '#FAF8F4',
                        border: `1px solid ${customer.tier === t.tier ? TIER_CONFIG[t.tier].color + '40' : '#EDE8E0'}`,
                        }}>
                        <p style={{ fontSize: 16, marginBottom: 4 }}>{t.emoji}</p>
                        <p style={{ fontSize: 12, fontWeight: 600, color: '#1C1008' }}>{t.multiplier} Poin</p>
                        <p style={{ fontSize: 10, color: '#8B7355', marginTop: 1 }}>Min. {t.min} poin</p>
                    </div>
                    ))}
                </div>
                </div>

                {/* Tab navigation */}
                <div className="flex gap-1 p-1 rounded-xl" style={{ background: '#EDE8E0' }}>
                {[
                    { key: 'points',  label: '⭐ Poin' },
                    { key: 'rewards', label: '🎁 Reward' },
                    { key: 'history', label: '📋 Riwayat' },
                ].map(t => (
                    <button key={t.key} onClick={() => setTab(t.key)}
                    className="flex-1 py-2 rounded-lg text-xs font-medium transition-all"
                    style={{
                        background: tab === t.key ? '#1C1008' : 'transparent',
                        color:      tab === t.key ? '#FAF2E6' : '#8B7355',
                    }}>
                    {t.label}
                    </button>
                ))}
                </div>

                {/* Tab: Poin */}
                {tab === 'points' && (
                <div className="bg-white rounded-2xl p-4 border border-[#EDE8E0] space-y-3">
                    <p style={{ fontSize: 10, fontWeight: 600, color: '#A0896E', textTransform: 'uppercase', letterSpacing: '0.1em' }}>
                    Cara Mendapatkan Poin
                    </p>
                    {[
                    { emoji: '🛍️', title: 'Setiap Transaksi', desc: '1 poin per Rp 1.000 yang dibelanjakan' },
                    { emoji: '⬆️', title: 'Bonus Tier',        desc: 'Multiplier poin berdasarkan tier kamu' },
                    { emoji: '📅', title: 'Masa Berlaku',       desc: 'Poin berlaku selama 1 tahun sejak diperoleh' },
                    ].map(item => (
                    <div key={item.title} className="flex items-start gap-3 py-2" style={{ borderBottom: '1px dashed #EDE8E0' }}>
                        <span style={{ fontSize: 20 }}>{item.emoji}</span>
                        <div>
                        <p style={{ fontSize: 13, fontWeight: 600, color: '#1C1008' }}>{item.title}</p>
                        <p style={{ fontSize: 11, color: '#8B7355', marginTop: 2 }}>{item.desc}</p>
                        </div>
                    </div>
                    ))}
                    <button onClick={() => { useLoyaltyStore.getState().clearLoyalty(); setPhone('') }}
                    className="w-full py-2.5 rounded-xl text-sm active:scale-95 transition-all mt-2"
                    style={{ border: '1px solid #EDE8E0', color: '#8B7355' }}>
                    Ganti Akun
                    </button>
                </div>
                )}

                {/* Tab: Rewards */}
                {tab === 'rewards' && (
                <div className="space-y-3">
                    {redeemSuccess && (
                    <div className="rounded-xl px-4 py-3" style={{ background: '#f0fdf4', border: '1px solid #bbf7d0' }}>
                        <p style={{ fontSize: 13, color: '#15803d', fontWeight: 500 }}>{redeemSuccess}</p>
                    </div>
                    )}
                    {rewards.length === 0 ? (
                    <div className="bg-white rounded-2xl p-8 border border-[#EDE8E0] text-center">
                        <p style={{ fontSize: 32, marginBottom: 8 }}>🎁</p>
                        <p style={{ fontSize: 14, color: '#8B7355' }}>Belum ada reward tersedia</p>
                    </div>
                    ) : (
                    // Grid 2 kolom di desktop, 1 kolom di mobile
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {rewards.map(reward => (
                        <div key={reward.id} className="bg-white rounded-2xl p-4 border border-[#EDE8E0] flex flex-col">
                            <div className="flex items-start justify-between gap-3 flex-1">
                            <div className="flex-1">
                                <div className="flex items-center gap-2 mb-1 flex-wrap">
                                <p style={{ fontSize: 14, fontWeight: 600, color: '#1C1008' }}>{reward.name}</p>
                                {reward.min_tier && (
                                    <span className="px-2 py-0.5 rounded-full text-xs"
                                    style={{ background: '#FDF3E7', color: '#C8872A', border: '1px solid #EDD9B8' }}>
                                    {TIER_CONFIG[reward.min_tier]?.emoji} {reward.min_tier}+
                                    </span>
                                )}
                                </div>
                                {reward.description && (
                                <p style={{ fontSize: 12, color: '#8B7355', marginBottom: 8 }}>{reward.description}</p>
                                )}
                                <div className="flex items-center gap-3 flex-wrap">
                                <span style={{ fontSize: 13, fontWeight: 700, color: '#C8872A' }}>
                                    ⭐ {reward.points_required.toLocaleString()} poin
                                </span>
                                <span style={{ fontSize: 11, color: '#8B7355' }}>
                                    Nilai: {formatPriceShort(reward.value)}
                                </span>
                                </div>
                                {reward.stock !== null && (
                                <p style={{ fontSize: 11, color: '#8B7355', marginTop: 4 }}>
                                    Sisa stok: {reward.stock}
                                </p>
                                )}
                            </div>
                            </div>
                            <button
                            onClick={() => reward.can_redeem && handleRedeem(reward.id)}
                            disabled={!reward.can_redeem || redeemLoading === reward.id}
                            className="w-full mt-3 py-2.5 rounded-xl text-sm font-semibold transition-all active:scale-[0.98]"
                            style={{
                                background: reward.can_redeem ? '#1C1008' : '#EDE8E0',
                                color:      reward.can_redeem ? '#FAF2E6' : '#8B7355',
                                cursor:     reward.can_redeem ? 'pointer' : 'not-allowed',
                            }}>
                            {redeemLoading === reward.id ? '...' :
                                reward.can_redeem ? 'Tukar Sekarang' : 'Poin Tidak Cukup'}
                            </button>
                        </div>
                        ))}
                    </div>
                    )}
                </div>
                )}

                {/* Tab: History */}
                {tab === 'history' && (
                <div className="bg-white rounded-2xl border border-[#EDE8E0] overflow-hidden">
                    {history.length === 0 ? (
                    <div className="p-8 text-center">
                        <p style={{ fontSize: 32, marginBottom: 8 }}>📋</p>
                        <p style={{ fontSize: 14, color: '#8B7355' }}>Belum ada riwayat poin</p>
                    </div>
                    ) : (
                    history.map((item, idx) => (
                        <div key={item.id}
                        className="flex items-center justify-between px-4 py-3"
                        style={{ borderBottom: idx < history.length - 1 ? '1px solid #F5F0EA' : 'none' }}>
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                            style={{
                                background: item.type === 'earn'   ? '#f0fdf4' :
                                            item.type === 'redeem' ? '#fef2f2' :
                                            item.type === 'expire' ? '#fefce8' : '#eff6ff',
                            }}>
                            <span style={{ fontSize: 16 }}>
                                {item.type === 'earn'   ? '⬆️' :
                                item.type === 'redeem' ? '🎁' :
                                item.type === 'expire' ? '⏰' : '✏️'}
                            </span>
                            </div>
                            <div>
                            <p style={{ fontSize: 13, fontWeight: 500, color: '#1C1008' }}>{item.description}</p>
                            <p style={{ fontSize: 11, color: '#8B7355', marginTop: 1 }}>
                                {new Date(item.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                            </p>
                            </div>
                        </div>
                        <div className="text-right">
                            <p style={{ fontSize: 14, fontWeight: 700, color: item.points > 0 ? '#16a34a' : '#dc2626' }}>
                            {item.points > 0 ? '+' : ''}{item.points.toLocaleString()}
                            </p>
                            <p style={{ fontSize: 11, color: '#8B7355' }}>
                            Saldo: {item.balance_after.toLocaleString()}
                            </p>
                        </div>
                        </div>
                    ))
                    )}
                </div>
                )}
            </>
            )}
        </div>

        {/* Bottom CTA */}
        {customer && (
            <div className="fixed bottom-0 left-0 right-0 px-4 pb-6 pt-3 border-t border-[#EDE8E0]"
            style={{ background: 'rgba(250,248,244,0.95)', backdropFilter: 'blur(12px)' }}>
            <div className="max-w-3xl mx-auto">
                <button onClick={() => navigate('/menu')}
                className="w-full py-3.5 rounded-2xl text-sm font-semibold active:scale-[0.98] transition-all"
                style={{ background: '#1C1008', color: '#FAF2E6' }}>
                ☕ Pesan Sekarang
                </button>
            </div>
            </div>
        )}
        </div>
    )
    }