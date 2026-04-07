import { useState, useRef } from 'react';
import { router, Link } from '@inertiajs/react';
import { Search, Calendar, CreditCard, ChevronDown } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const METHOD_LABELS = { cash: 'Tunai', qris: 'QRIS', bayar_nanti: 'Bayar Nanti' };
const TODAY = new Date().toISOString().split('T')[0];

// Cashier blue theme
const T = {
    surface:  '#FFFFFF',
    elevated: '#F1F5F9',
    textPri:  '#0F172A',
    textSec:  '#64748B',
    textTer:  '#94A3B8',
    border:   '#E2E8F0',
    accent:   '#3B6FD4',
    shadow:   '0 4px 14px rgba(15,23,42,0.06)',
    shadowSm: '0 2px 8px rgba(15,23,42,0.04)',
};

const COLS = [
    { key: 'id',      label: 'ID Pesanan',  width: 150 },
    { key: 'date',    label: 'Tanggal',     width: 120 },
    { key: 'time',    label: 'Waktu',       width: 80  },
    { key: 'total',   label: 'Total',       width: 140 },
    { key: 'payment', label: 'Pembayaran',  width: 110 },
    { key: 'cashier', label: 'Kasir',       flex: 1    },
    { key: 'status',  label: 'Status',      width: 110 },
    { key: 'action',  label: 'Aksi',        width: 70  },
];

export default function RiwayatPesanan({ orders, filters }) {
    const [search, setSearch] = useState(filters.search  ?? '');
    const [date,   setDate]   = useState(filters.date    ?? TODAY);
    const [method, setMethod] = useState(filters.method  ?? '');
    const searchTimer = useRef(null);

    function apply(overrides = {}) {
        const params = { search, date, method, ...overrides };
        Object.keys(params).forEach(k => { if (params[k] === '') delete params[k]; });
        router.get('/cashier/riwayat', params, { preserveState: true, replace: true });
    }

    function handleSearch(e) {
        const val = e.target.value;
        setSearch(val);
        clearTimeout(searchTimer.current);
        searchTimer.current = setTimeout(() => apply({ search: val }), 400);
    }

    function handleDate(e) { const val = e.target.value; setDate(val); apply({ date: val }); }
    function handleMethod(e) { const val = e.target.value; setMethod(val); apply({ method: val }); }

    return (
        <CashierLayout title="Riwayat Pesanan" fullscreen>
            <div style={{ flex: 1, overflowY: 'auto', padding: 32, background: '#F8FAFC' }}>
            <div style={{ background: '#FFFFFF', borderRadius: 12, padding: 24, border: '1px solid #E2E8F0', boxShadow: '0 2px 8px rgba(15,23,42,0.03)' }}>

            {/* ── Header ── */}
            <div style={{ marginBottom: 28, display: 'flex', flexDirection: 'column', gap: 4 }}>
                <h1 style={{
                    fontSize: 26, fontWeight: 700, color: T.textPri,
                    margin: 0, letterSpacing: '-0.5px',
                    fontFamily: '"DM Sans", system-ui',
                }}>
                    Riwayat Pesanan
                </h1>
                <p style={{ fontSize: 14, color: T.textSec, margin: 0, fontFamily: 'Outfit, system-ui' }}>
                    Lihat semua transaksi yang telah selesai
                </p>
            </div>

            {/* ── Toolbar ── */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                {/* Search */}
                <div style={{ position: 'relative', flex: 1 }}>
                    <Search size={18} style={{
                        position: 'absolute', left: 14, top: '50%',
                        transform: 'translateY(-50%)', color: T.textTer, pointerEvents: 'none',
                    }} />
                    <input
                        type="text"
                        value={search}
                        onChange={handleSearch}
                        placeholder="Cari transaksi..."
                        style={{
                            width: '100%', height: 44,
                            border: `1px solid ${T.border}`, borderRadius: 12,
                            padding: '0 16px 0 44px', fontSize: 14,
                            color: T.textPri, background: T.surface,
                            outline: 'none', boxSizing: 'border-box',
                            boxShadow: T.shadowSm,
                            fontFamily: 'Outfit, system-ui',
                        }}
                    />
                </div>

                {/* Date */}
                <div style={{ position: 'relative' }}>
                    <Calendar size={16} style={{
                        position: 'absolute', left: 14, top: '50%',
                        transform: 'translateY(-50%)', color: T.textSec, pointerEvents: 'none',
                    }} />
                    <input
                        type="date"
                        value={date}
                        onChange={handleDate}
                        style={{
                            height: 44, width: 180,
                            border: `1px solid ${T.border}`, borderRadius: 12,
                            padding: '0 14px 0 40px', fontSize: 13,
                            color: T.textPri, background: T.surface,
                            outline: 'none', boxSizing: 'border-box',
                            boxShadow: T.shadowSm,
                            fontFamily: 'Outfit, system-ui',
                        }}
                    />
                </div>

                {/* Payment method */}
                <div style={{ position: 'relative' }}>
                    <CreditCard size={16} style={{
                        position: 'absolute', left: 14, top: '50%',
                        transform: 'translateY(-50%)', color: T.textSec, pointerEvents: 'none',
                    }} />
                    <select
                        value={method}
                        onChange={handleMethod}
                        style={{
                            height: 44, width: 180,
                            border: `1px solid ${T.border}`, borderRadius: 12,
                            padding: '0 36px 0 40px', fontSize: 13,
                            color: T.textPri, background: T.surface,
                            outline: 'none', appearance: 'none',
                            boxSizing: 'border-box', cursor: 'pointer',
                            boxShadow: T.shadowSm,
                            fontFamily: 'Outfit, system-ui',
                        }}
                    >
                        <option value="">Semua Metode</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                    <ChevronDown size={14} style={{
                        position: 'absolute', right: 12, top: '50%',
                        transform: 'translateY(-50%)', color: T.textTer, pointerEvents: 'none',
                    }} />
                </div>
            </div>

            {/* ── Table Card ── */}
            <div style={{
                background: T.surface, borderRadius: 16,
                border: `1px solid ${T.border}`,
                boxShadow: T.shadow, overflow: 'hidden',
            }}>
                {/* Head */}
                <div style={{
                    display: 'flex', alignItems: 'center',
                    background: T.elevated, padding: '12px 16px',
                    borderBottom: `1px solid ${T.border}`,
                }}>
                    {COLS.map(col => (
                        <div key={col.key} style={{ width: col.width, flex: col.flex, flexShrink: col.flex ? undefined : 0 }}>
                            <span style={{
                                fontSize: 12, fontWeight: 600, color: T.textSec,
                                fontFamily: 'Outfit, system-ui',
                            }}>
                                {col.label}
                            </span>
                        </div>
                    ))}
                </div>

                {/* Rows */}
                {orders.length === 0 ? (
                    <div style={{
                        textAlign: 'center', color: T.textTer,
                        padding: '48px 16px', fontSize: 14,
                        fontFamily: 'Outfit, system-ui',
                    }}>
                        Tidak ada data riwayat pesanan
                    </div>
                ) : (
                    orders.map(order => (
                        <OrderRow key={order.id} order={order} />
                    ))
                )}
            </div>
            </div>
            </div>
        </CashierLayout>
    );
}

function OrderRow({ order }) {
    const [hovered, setHovered] = useState(false);
    const methodLabel = METHOD_LABELS[order.payment_method] ?? order.payment_method ?? '—';

    return (
        <div
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            style={{
                display: 'flex', alignItems: 'center',
                padding: '14px 16px',
                borderBottom: `1px solid ${T.border}`,
                background: hovered ? T.elevated : T.surface,
                transition: 'background 0.1s',
            }}
        >
            <div style={{ width: 150, flexShrink: 0 }}>
                <span style={{ fontSize: 13, fontWeight: 600, color: T.textPri, fontFamily: 'Outfit, system-ui' }}>
                    {order.order_code}
                </span>
            </div>
            <div style={{ width: 120, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>
                    {formatDate(order.created_at)}
                </span>
            </div>
            <div style={{ width: 80, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>
                    {formatTime(order.created_at)}
                </span>
            </div>
            <div style={{ width: 140, flexShrink: 0 }}>
                <span style={{ fontSize: 13, fontWeight: 600, color: T.textPri, fontFamily: 'Outfit, system-ui' }}>
                    {formatRupiah(order.total_amount)}
                </span>
            </div>
            <div style={{ width: 110, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>{methodLabel}</span>
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <span style={{
                    fontSize: 13, color: T.textSec, fontFamily: 'Outfit, system-ui',
                    overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', display: 'block',
                }}>
                    {order.cashier_name ?? '—'}
                </span>
            </div>
            <div style={{ width: 110, flexShrink: 0 }}>
                <StatusBadge status={order.status} />
            </div>
            <div style={{ width: 70, flexShrink: 0 }}>
                <Link
                    href={`/cashier/order/${order.id}`}
                    style={{
                        fontSize: 13, fontWeight: 500,
                        color: T.accent, textDecoration: 'none',
                        fontFamily: 'Outfit, system-ui',
                    }}
                >
                    Detail
                </Link>
            </div>
        </div>
    );
}
