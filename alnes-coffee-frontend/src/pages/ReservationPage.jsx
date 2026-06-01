    import { useState } from 'react'
    import { useNavigate } from 'react-router-dom'
    import { reservationApi } from '@/api'
    import useSettingStore from '@/store/settingStore'

    const fontLink = document.getElementById('alnes-fonts')
    if (!fontLink) {
    const link = document.createElement('link')
    link.id   = 'alnes-fonts'
    link.rel  = 'stylesheet'
    link.href = 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;1,9..144,300&family=DM+Sans:wght@300;400;500&display=swap'
    document.head.appendChild(link)
    }

    const STATUS_CONFIG = {
    pending:   { label: '⏳ Pending',    color: '#F59E0B' },
    confirmed: { label: '✅ Confirmed',  color: '#10B981' },
    cancelled: { label: '❌ Cancelled',  color: '#EF4444' },
    completed: { label: '🏁 Completed', color: '#6B7280' },
    }

    export default function ReservationPage() {
    const navigate    = useNavigate()
    const { setting } = useSettingStore()

    const [tab, setTab]         = useState('book')
    const [step, setStep]       = useState(1)
    const [loading, setLoading] = useState(false)
    const [error, setError]     = useState('')

    const [form, setForm] = useState({
        customer_name:    '',
        customer_phone:   '',
        customer_email:   '',
        reservation_date: '',
        reservation_time: '',
        guest_count:      2,
        notes:            '',
        table_id:         null,
    })

    const [availableTables, setAvailableTables] = useState([])
    const [successData, setSuccessData]         = useState(null)

    const [phone, setPhone]                   = useState('')
    const [myReservations, setMyReservations] = useState([])
    const [checkLoading, setCheckLoading]     = useState(false)
    const [cancelLoading, setCancelLoading]   = useState(null)

    const timeSlots = [
        '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00',
        '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00',
    ]

    const handleCheckAvailability = async () => {
        if (!form.reservation_date || !form.reservation_time || !form.guest_count) {
        setError('Lengkapi tanggal, jam, dan jumlah tamu.')
        return
        }
        setLoading(true)
        setError('')
        try {
        const { data } = await reservationApi.checkAvailability({
            date: form.reservation_date, time: form.reservation_time, guest_count: form.guest_count,
        })
        setAvailableTables(data.data.available_tables)
        setStep(2)
        } catch (e) {
        setError(e.response?.data?.message || 'Gagal cek ketersediaan.')
        } finally {
        setLoading(false)
        }
    }

    const handleSubmit = async () => {
        if (!form.customer_name || !form.customer_phone) {
        setError('Nama dan nomor HP wajib diisi.')
        return
        }
        setLoading(true)
        setError('')
        try {
        const { data } = await reservationApi.store(form)
        setSuccessData(data.data)
        setStep(3)
        } catch (e) {
        setError(e.response?.data?.message || 'Gagal membuat reservasi.')
        } finally {
        setLoading(false)
        }
    }

    const handleCheckByPhone = async () => {
        if (!phone) return
        setCheckLoading(true)
        try {
        const { data } = await reservationApi.checkByPhone({ phone })
        setMyReservations(data.data.reservations)
        } catch {
        setError('Nomor HP tidak ditemukan.')
        } finally {
        setCheckLoading(false)
        }
    }

    const handleCancel = async (id) => {
        setCancelLoading(id)
        try {
        await reservationApi.cancel(id, { phone })
        await handleCheckByPhone()
        } catch (e) {
        setError(e.response?.data?.message || 'Gagal membatalkan reservasi.')
        } finally {
        setCancelLoading(null)
        }
    }

    const today = new Date().toISOString().split('T')[0]

    return (
        <div style={{ fontFamily: "'DM Sans', sans-serif" }} className="min-h-screen bg-[#FAF8F4]">

        {/* ── Header — selaras dengan MenuPage & LoyaltyPage ── */}
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
                    Reservasi Meja
                    </h1>
                    <p style={{ color: '#8B7355', fontWeight: 300, letterSpacing: '0.04em', textTransform: 'uppercase', fontSize: 10, marginTop: 2 }}>
                    Pesan meja sebelum datang
                    </p>
                </div>
                </div>
            </div>
            </div>
        </div>

        {/* ── Content — max-w-3xl mx-auto selaras MenuPage ── */}
        <div className="max-w-3xl mx-auto px-4 py-5 pb-24 space-y-4">

            {/* Tab */}
            <div className="flex gap-1 p-1 rounded-xl" style={{ background: '#EDE8E0' }}>
            {[
                { key: 'book',  label: '📅 Buat Reservasi' },
                { key: 'check', label: '🔍 Cek Reservasi' },
            ].map(t => (
                <button key={t.key} onClick={() => { setTab(t.key); setStep(1); setError('') }}
                className="flex-1 py-2 rounded-lg text-xs font-medium transition-all"
                style={{
                    background: tab === t.key ? '#1C1008' : 'transparent',
                    color:      tab === t.key ? '#FAF2E6' : '#8B7355',
                }}>
                {t.label}
                </button>
            ))}
            </div>

            {error && (
            <div className="rounded-xl px-4 py-3" style={{ background: '#fef2f2', border: '1px solid #fecaca' }}>
                <p style={{ fontSize: 13, color: '#dc2626' }}>✕ {error}</p>
            </div>
            )}

            {/* ── TAB: BOOK ── */}
            {tab === 'book' && (
            <>
                {/* Step 1 — Pilih waktu */}
                {step === 1 && (
                <div className="bg-white rounded-2xl p-5 border border-[#EDE8E0] space-y-5">
                    <p style={{ fontFamily: "'Fraunces', serif", fontSize: 16, color: '#1C1008', fontWeight: 400 }}>
                    Pilih Waktu
                    </p>

                    {/* Tanggal + Jumlah Tamu — 2 kolom di desktop */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label style={{ fontSize: 12, color: '#8B7355', display: 'block', marginBottom: 6 }}>Tanggal</label>
                        <input type="date" min={today}
                        value={form.reservation_date}
                        onChange={e => setForm(f => ({ ...f, reservation_date: e.target.value }))}
                        className="w-full rounded-xl border border-[#EDE8E0] px-4 py-2.5 text-sm outline-none"
                        style={{ color: '#1C1008', background: '#FAF8F4' }} />
                    </div>

                    <div>
                        <label style={{ fontSize: 12, color: '#8B7355', display: 'block', marginBottom: 6 }}>Jumlah Tamu</label>
                        <div className="flex items-center gap-3">
                        <button onClick={() => setForm(f => ({ ...f, guest_count: Math.max(1, f.guest_count - 1) }))}
                            className="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0"
                            style={{ background: '#EDE8E0', color: '#1C1008' }}>−</button>
                        <span style={{ fontSize: 20, fontWeight: 600, color: '#1C1008', minWidth: 32, textAlign: 'center' }}>
                            {form.guest_count}
                        </span>
                        <button onClick={() => setForm(f => ({ ...f, guest_count: Math.min(20, f.guest_count + 1) }))}
                            className="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold flex-shrink-0"
                            style={{ background: '#1C1008', color: '#FAF2E6' }}>+</button>
                        <span style={{ fontSize: 12, color: '#8B7355' }}>orang</span>
                        </div>
                    </div>
                    </div>

                    {/* Time slots — lebih banyak kolom di desktop */}
                    <div>
                    <label style={{ fontSize: 12, color: '#8B7355', display: 'block', marginBottom: 6 }}>Jam</label>
                    <div className="grid grid-cols-5 sm:grid-cols-7 gap-2">
                        {timeSlots.map(time => (
                        <button key={time}
                            onClick={() => setForm(f => ({ ...f, reservation_time: time }))}
                            className="py-2 rounded-xl text-xs font-medium transition-all"
                            style={{
                            background: form.reservation_time === time ? '#1C1008' : '#FAF8F4',
                            color:      form.reservation_time === time ? '#FAF2E6' : '#5C4433',
                            border:     `1px solid ${form.reservation_time === time ? '#1C1008' : '#EDE8E0'}`,
                            }}>
                            {time}
                        </button>
                        ))}
                    </div>
                    </div>

                    <button onClick={handleCheckAvailability} disabled={loading}
                    className="w-full py-3.5 rounded-2xl text-sm font-semibold active:scale-[0.98] transition-all"
                    style={{ background: '#1C1008', color: '#FAF2E6' }}>
                    {loading ? '⏳ Mengecek...' : 'Cek Ketersediaan →'}
                    </button>
                </div>
                )}

                {/* Step 2 — Pilih meja + isi data */}
                {step === 2 && (
                <div className="space-y-4">

                    {/* Pilih meja */}
                    <div className="bg-white rounded-2xl p-5 border border-[#EDE8E0]">
                    <p style={{ fontFamily: "'Fraunces', serif", fontSize: 16, color: '#1C1008', fontWeight: 400, marginBottom: 12 }}>
                        Pilih Meja (Opsional)
                    </p>
                    {availableTables.length === 0 ? (
                        <p style={{ fontSize: 13, color: '#EF4444', textAlign: 'center' }}>
                        😔 Tidak ada meja tersedia pada waktu ini
                        </p>
                    ) : (
                        <div className="grid grid-cols-4 sm:grid-cols-6 gap-2">
                        <button
                            onClick={() => setForm(f => ({ ...f, table_id: null }))}
                            className="py-3 rounded-xl text-xs font-medium transition-all"
                            style={{
                            background: !form.table_id ? '#1C1008' : '#FAF8F4',
                            color:      !form.table_id ? '#FAF2E6' : '#5C4433',
                            border:     `1px solid ${!form.table_id ? '#1C1008' : '#EDE8E0'}`,
                            }}>
                            Bebas
                        </button>
                        {availableTables.map(t => (
                            <button key={t.id}
                            onClick={() => setForm(f => ({ ...f, table_id: t.id }))}
                            className="py-3 rounded-xl text-xs font-medium transition-all"
                            style={{
                                background: form.table_id === t.id ? '#1C1008' : '#FAF8F4',
                                color:      form.table_id === t.id ? '#FAF2E6' : '#5C4433',
                                border:     `1px solid ${form.table_id === t.id ? '#1C1008' : '#EDE8E0'}`,
                            }}>
                            {t.table_number}
                            </button>
                        ))}
                        </div>
                    )}
                    </div>

                    {/* Data diri + Summary — 2 kolom di desktop */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {/* Kiri: form data diri */}
                    <div className="bg-white rounded-2xl p-5 border border-[#EDE8E0] space-y-4">
                        <p style={{ fontFamily: "'Fraunces', serif", fontSize: 16, color: '#1C1008', fontWeight: 400 }}>
                        Data Diri
                        </p>
                        {[
                        { key: 'customer_name',  label: 'Nama Lengkap *', type: 'text',  placeholder: 'Masukkan nama kamu' },
                        { key: 'customer_phone', label: 'Nomor HP *',      type: 'tel',   placeholder: '08xx-xxxx-xxxx' },
                        { key: 'customer_email', label: 'Email',           type: 'email', placeholder: 'email@example.com (opsional)' },
                        ].map(field => (
                        <div key={field.key}>
                            <label style={{ fontSize: 12, color: '#8B7355', display: 'block', marginBottom: 6 }}>{field.label}</label>
                            <input type={field.type} placeholder={field.placeholder}
                            value={form[field.key]}
                            onChange={e => setForm(f => ({ ...f, [field.key]: e.target.value }))}
                            className="w-full rounded-xl border border-[#EDE8E0] px-4 py-2.5 text-sm outline-none"
                            style={{ color: '#1C1008', background: '#FAF8F4' }} />
                        </div>
                        ))}
                        <div>
                        <label style={{ fontSize: 12, color: '#8B7355', display: 'block', marginBottom: 6 }}>Catatan</label>
                        <textarea rows={3} placeholder="Ada permintaan khusus? (opsional)"
                            value={form.notes}
                            onChange={e => setForm(f => ({ ...f, notes: e.target.value }))}
                            className="w-full rounded-xl border border-[#EDE8E0] px-4 py-2.5 text-sm outline-none resize-none"
                            style={{ color: '#1C1008', background: '#FAF8F4' }} />
                        </div>
                    </div>

                    {/* Kanan: summary */}
                    <div className="flex flex-col gap-4">
                        <div className="rounded-2xl p-5 flex-1"
                        style={{ background: 'linear-gradient(135deg, #1C1008, #6B3F1F)' }}>
                        <p style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 12 }}>
                            Ringkasan Reservasi
                        </p>
                        {[
                            ['📅 Tanggal', new Date(form.reservation_date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })],
                            ['⏰ Jam',     form.reservation_time],
                            ['👥 Tamu',   `${form.guest_count} orang`],
                            ['🪑 Meja',   availableTables.find(t => t.id === form.table_id)?.table_number || 'Bebas'],
                        ].map(([label, value]) => (
                            <div key={label} className="flex justify-between items-center py-2"
                            style={{ borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
                            <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.55)' }}>{label}</span>
                            <span style={{ fontSize: 12, fontWeight: 600, color: '#FAF2E6' }}>{value}</span>
                            </div>
                        ))}
                        </div>

                        <div className="flex gap-3">
                        <button onClick={() => { setStep(1); setError('') }}
                            className="flex-1 py-3 rounded-2xl text-sm font-medium"
                            style={{ background: '#EDE8E0', color: '#1C1008' }}>
                            ← Kembali
                        </button>
                        <button onClick={handleSubmit} disabled={loading}
                            className="flex-1 py-3 rounded-2xl text-sm font-semibold active:scale-[0.98] transition-all"
                            style={{ background: '#C8872A', color: '#FAF2E6' }}>
                            {loading ? '⏳...' : '✓ Konfirmasi'}
                        </button>
                        </div>
                    </div>
                    </div>
                </div>
                )}

                {/* Step 3 — Sukses */}
                {step === 3 && successData && (
                <div className="bg-white rounded-2xl p-6 border border-[#EDE8E0] text-center space-y-4 max-w-md mx-auto">
                    <div className="w-16 h-16 rounded-full mx-auto flex items-center justify-center"
                    style={{ background: '#f0fdf4' }}>
                    <span style={{ fontSize: 32 }}>✅</span>
                    </div>
                    <div>
                    <p style={{ fontFamily: "'Fraunces', serif", fontSize: 20, color: '#1C1008', fontWeight: 400 }}>
                        Reservasi Berhasil!
                    </p>
                    <p style={{ fontSize: 13, color: '#8B7355', marginTop: 4 }}>
                        Kami akan konfirmasi segera
                    </p>
                    </div>
                    <div className="rounded-xl p-4 text-left space-y-2" style={{ background: '#FAF8F4' }}>
                    {[
                        ['Nama',    successData.customer_name],
                        ['Tanggal', successData.reservation_date],
                        ['Jam',     successData.reservation_time],
                        ['Tamu',    `${successData.guest_count} orang`],
                        ['Meja',    successData.table || 'Akan ditentukan'],
                        ['Status',  '⏳ Menunggu konfirmasi'],
                    ].map(([label, value]) => (
                        <div key={label} className="flex justify-between text-sm">
                        <span style={{ color: '#8B7355' }}>{label}</span>
                        <span style={{ color: '#1C1008', fontWeight: 500 }}>{value}</span>
                        </div>
                    ))}
                    </div>
                    <div className="flex gap-3">
                    <button onClick={() => { setStep(1); setForm({ customer_name: '', customer_phone: '', customer_email: '', reservation_date: '', reservation_time: '', guest_count: 2, notes: '', table_id: null }) }}
                        className="flex-1 py-3 rounded-2xl text-sm font-medium"
                        style={{ background: '#EDE8E0', color: '#1C1008' }}>
                        Buat Lagi
                    </button>
                    <button onClick={() => navigate('/menu')}
                        className="flex-1 py-3 rounded-2xl text-sm font-semibold"
                        style={{ background: '#1C1008', color: '#FAF2E6' }}>
                        ☕ Pesan Menu
                    </button>
                    </div>
                </div>
                )}
            </>
            )}

            {/* ── TAB: CHECK ── */}
            {tab === 'check' && (
            <div className="space-y-4">
                <div className="bg-white rounded-2xl p-5 border border-[#EDE8E0]">
                <p style={{ fontFamily: "'Fraunces', serif", fontSize: 16, color: '#1C1008', fontWeight: 400, marginBottom: 12 }}>
                    Cek Reservasi Saya
                </p>
                <div className="flex gap-2">
                    <input type="tel" placeholder="Masukkan nomor HP"
                    value={phone}
                    onChange={e => setPhone(e.target.value)}
                    onKeyDown={e => e.key === 'Enter' && handleCheckByPhone()}
                    className="flex-1 rounded-xl border border-[#EDE8E0] px-4 py-2.5 text-sm outline-none"
                    style={{ color: '#1C1008', background: '#FAF8F4' }} />
                    <button onClick={handleCheckByPhone} disabled={checkLoading}
                    className="px-5 rounded-xl text-sm font-semibold active:scale-95 transition-all"
                    style={{ background: '#1C1008', color: '#FAF2E6' }}>
                    {checkLoading ? '...' : 'Cek'}
                    </button>
                </div>
                </div>

                {myReservations.length > 0 && (
                // Grid 2 kolom di desktop
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {myReservations.map(r => (
                    <div key={r.id} className="bg-white rounded-2xl p-4 border border-[#EDE8E0]">
                        <div className="flex items-start justify-between mb-3">
                        <div>
                            <p style={{ fontSize: 14, fontWeight: 600, color: '#1C1008' }}>
                            {r.reservation_date} · {r.reservation_time}
                            </p>
                            <p style={{ fontSize: 12, color: '#8B7355', marginTop: 2 }}>
                            {r.guest_count} orang · {r.table}
                            </p>
                        </div>
                        <span className="px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0"
                            style={{
                            background: STATUS_CONFIG[r.status]?.color + '20',
                            color:      STATUS_CONFIG[r.status]?.color,
                            }}>
                            {STATUS_CONFIG[r.status]?.label}
                        </span>
                        </div>
                        {r.notes && (
                        <p style={{ fontSize: 12, color: '#8B7355', marginBottom: 8 }}>📝 {r.notes}</p>
                        )}
                        {['pending', 'confirmed'].includes(r.status) && (
                        <button onClick={() => handleCancel(r.id)}
                            disabled={cancelLoading === r.id}
                            className="w-full py-2 rounded-xl text-xs font-medium active:scale-95 transition-all"
                            style={{ background: '#fef2f2', color: '#dc2626', border: '1px solid #fecaca' }}>
                            {cancelLoading === r.id ? '...' : '✕ Batalkan Reservasi'}
                        </button>
                        )}
                    </div>
                    ))}
                </div>
                )}

                {myReservations.length === 0 && phone && !checkLoading && (
                <div className="bg-white rounded-2xl p-8 border border-[#EDE8E0] text-center">
                    <p style={{ fontSize: 32, marginBottom: 8 }}>📭</p>
                    <p style={{ fontSize: 14, color: '#8B7355' }}>Tidak ada reservasi ditemukan</p>
                </div>
                )}
            </div>
            )}
        </div>
        </div>
    )
    }