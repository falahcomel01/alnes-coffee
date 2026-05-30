    import { useState, useEffect, useCallback } from 'react'
    import { kitchenApi } from '@/api'
    import echo from '@/lib/echo'

    const STATUS_CONFIG = {
    pending: {
        label: 'Menunggu',
        bg: 'bg-amber-50',
        border: 'border-amber-300',
        badge: 'bg-amber-100 text-amber-700',
        dot: 'bg-amber-400',
        nextStatus: 'confirmed',
        nextLabel: 'Konfirmasi',
        nextColor: 'bg-blue-600 hover:bg-blue-700',
    },
    confirmed: {
        label: 'Dikonfirmasi',
        bg: 'bg-blue-50',
        border: 'border-blue-300',
        badge: 'bg-blue-100 text-blue-700',
        dot: 'bg-blue-400',
        nextStatus: 'cooking',
        nextLabel: 'Mulai Masak',
        nextColor: 'bg-orange-500 hover:bg-orange-600',
    },
    cooking: {
        label: 'Dimasak',
        bg: 'bg-orange-50',
        border: 'border-orange-300',
        badge: 'bg-orange-100 text-orange-700',
        dot: 'bg-orange-400',
        nextStatus: 'ready',
        nextLabel: 'Siap Disajikan',
        nextColor: 'bg-green-600 hover:bg-green-700',
    },
    ready: {
        label: 'Siap',
        bg: 'bg-green-50',
        border: 'border-green-300',
        badge: 'bg-green-100 text-green-700',
        dot: 'bg-green-400',
        nextStatus: 'completed',
        nextLabel: 'Selesai',
        nextColor: 'bg-gray-700 hover:bg-gray-800',
    },
    }

    function OrderCard({ order, onUpdateStatus }) {
    const config = STATUS_CONFIG[order.order_status] || STATUS_CONFIG.pending
    const [loading, setLoading] = useState(false)

    const handleUpdate = async () => {
        if (!config.nextStatus) return
        setLoading(true)
        try {
        await onUpdateStatus(order.id, config.nextStatus)
        } finally {
        setLoading(false)
        }
    }

    return (
        <div className={`rounded-2xl border-2 ${config.border} ${config.bg} p-4 flex flex-col gap-3 transition-all`}>
        {/* Header */}
        <div className="flex items-start justify-between">
            <div>
            <div className="flex items-center gap-2 mb-1">
                <span className={`w-2 h-2 rounded-full ${config.dot} animate-pulse`} />
                <span className={`text-xs font-semibold px-2 py-0.5 rounded-full ${config.badge}`}>
                {config.label}
                </span>
                {order.order_type === 'takeaway' && (
                <span className="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-semibold">
                    Takeaway
                </span>
                )}
            </div>
            <h3 className="text-lg font-bold text-gray-900">{order.table_number}</h3>
            <p className="text-sm text-gray-500">{order.customer_name}</p>
            </div>
            <div className="text-right">
            <p className="text-xl font-bold text-gray-800">{order.ordered_at}</p>
            <p className="text-xs text-gray-400">{order.invoice_number}</p>
            </div>
        </div>

        {/* Items */}
        <div className="bg-white/70 rounded-xl p-3 space-y-2">
            {order.items.map((item, i) => (
            <div key={i} className="flex items-start gap-3">
                <span className="w-7 h-7 rounded-full bg-gray-900 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">
                {item.qty}
                </span>
                <div>
                <p className="text-sm font-semibold text-gray-900">{item.name}</p>
                {item.notes && (
                    <p className="text-xs text-gray-500 italic">📝 {item.notes}</p>
                )}
                </div>
            </div>
            ))}
        </div>

        {/* Notes */}
        {order.notes && (
            <div className="bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2">
            <p className="text-xs text-yellow-800">📋 {order.notes}</p>
            </div>
        )}

        {/* Action */}
        {config.nextStatus && (
            <button
            onClick={handleUpdate}
            disabled={loading}
            className={`w-full py-3 rounded-xl text-white text-sm font-bold transition-all
                ${config.nextColor} disabled:opacity-50 active:scale-[0.98]`}
            >
            {loading ? 'Memproses...' : `${config.nextLabel} →`}
            </button>
        )}
        </div>
    )
    }

    export default function KitchenPage() {
    const [orders, setOrders] = useState([])
    const [loading, setLoading] = useState(true)
    const [lastUpdate, setLastUpdate] = useState(null)
    const [connected, setConnected] = useState(false)
    const [newOrderAlert, setNewOrderAlert] = useState(null)

    const fetchOrders = useCallback(async () => {
        try {
        const { data } = await kitchenApi.index()
        setOrders(data.data)
        setLastUpdate(new Date().toLocaleTimeString('id-ID'))
        } catch (e) {
        console.error(e)
        } finally {
        setLoading(false)
        }
    }, [])

    useEffect(() => {
        fetchOrders()

        // Realtime via Laravel Reverb
        const channel = echo.channel('kitchen')

        channel.subscribed(() => setConnected(true))

        channel.listen('.order.created', (data) => {
        setNewOrderAlert(data)
        setTimeout(() => setNewOrderAlert(null), 5000)
        fetchOrders()
        })

        channel.listen('.order.status.updated', () => {
        fetchOrders()
        })

        // Fallback polling setiap 15 detik
        const interval = setInterval(fetchOrders, 15000)

        return () => {
        echo.leaveChannel('kitchen')
        clearInterval(interval)
        }
    }, [fetchOrders])

    const handleUpdateStatus = async (id, status) => {
        await kitchenApi.updateStatus(id, status)
        await fetchOrders()
    }

    const groupedOrders = {
        pending:   orders.filter(o => o.order_status === 'pending'),
        confirmed: orders.filter(o => o.order_status === 'confirmed'),
        cooking:   orders.filter(o => o.order_status === 'cooking'),
        ready:     orders.filter(o => o.order_status === 'ready'),
    }

    const totalActive = orders.length

    return (
        <div className="min-h-screen bg-gray-950 text-white">
        {/* New order alert */}
        {newOrderAlert && (
            <div className="fixed top-4 right-4 z-50 bg-green-500 text-white px-5 py-3 rounded-2xl shadow-2xl animate-bounce">
            🔔 Order baru masuk! — {newOrderAlert.table_number} ({newOrderAlert.customer_name})
            </div>
        )}

        {/* Header */}
        <div className="bg-gray-900 border-b border-gray-800 px-6 py-4">
            <div className="flex items-center justify-between max-w-screen-2xl mx-auto">
            <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-xl">
                👨‍🍳
                </div>
                <div>
                <h1 className="text-xl font-bold">Kitchen Display</h1>
                <p className="text-gray-400 text-sm">Alnes Coffee and Venue Batu</p>
                </div>
            </div>
            <div className="flex items-center gap-6">
                {/* Realtime indicator */}
                <div className="flex items-center gap-2">
                <span className={`w-2.5 h-2.5 rounded-full ${connected ? 'bg-green-400 animate-pulse' : 'bg-red-400'}`} />
                <span className="text-xs text-gray-400">{connected ? 'Realtime' : 'Offline'}</span>
                </div>
                <div className="text-center">
                <p className="text-2xl font-bold text-orange-400">{totalActive}</p>
                <p className="text-xs text-gray-400">Order Aktif</p>
                </div>
                <div className="text-right">
                <p className="text-xs text-gray-400">Update terakhir</p>
                <p className="text-sm font-mono text-green-400">{lastUpdate || '-'}</p>
                </div>
                <button
                onClick={fetchOrders}
                className="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-xl text-sm font-medium transition-colors"
                >
                🔄 Refresh
                </button>
            </div>
            </div>
        </div>

        {/* Content */}
        {loading ? (
            <div className="flex items-center justify-center min-h-[60vh]">
            <div className="flex gap-2">
                {[0,1,2].map(i => (
                <div key={i} className="w-3 h-3 rounded-full bg-orange-400 animate-bounce"
                    style={{ animationDelay: `${i * 0.15}s` }} />
                ))}
            </div>
            </div>
        ) : totalActive === 0 ? (
            <div className="flex flex-col items-center justify-center min-h-[60vh] gap-4">
            <div className="text-6xl">✅</div>
            <p className="text-xl font-semibold text-gray-300">Semua order sudah selesai!</p>
            <p className="text-gray-500 text-sm">Tidak ada order aktif saat ini</p>
            </div>
        ) : (
            <div className="p-6 max-w-screen-2xl mx-auto">
            <div className="grid grid-cols-4 gap-6">
                {Object.entries(STATUS_CONFIG).map(([status, config]) => (
                <div key={status}>
                    {/* Column header */}
                    <div className="flex items-center gap-2 mb-3">
                    <span className={`w-2.5 h-2.5 rounded-full ${config.dot}`} />
                    <h2 className="text-sm font-semibold text-gray-300 uppercase tracking-wide">
                        {config.label}
                    </h2>
                    <span className="ml-auto text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full">
                        {groupedOrders[status].length}
                    </span>
                    </div>

                    {/* Cards */}
                    <div className="space-y-3">
                    {groupedOrders[status].length === 0 ? (
                        <div className="border-2 border-dashed border-gray-800 rounded-2xl p-6 text-center">
                        <p className="text-gray-600 text-sm">Kosong</p>
                        </div>
                    ) : (
                        groupedOrders[status].map(order => (
                        <OrderCard
                            key={order.id}
                            order={order}
                            onUpdateStatus={handleUpdateStatus}
                        />
                        ))
                    )}
                    </div>
                </div>
                ))}
            </div>
            </div>
        )}
        </div>
    )
    }