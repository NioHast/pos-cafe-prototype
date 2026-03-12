import { useState, useRef } from 'react';
import { router, Link } from '@inertiajs/react';
import { Search, Calendar, CreditCard, ChevronDown } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const METHOD_LABELS = {
    cash:     'Tunai',
    qris:     'QRIS',
    ewallet:  'E-Wallet',
    transfer: 'Transfer Bank',
};

const TODAY = new Date().toISOString().split('T')[0];

export default function RiwayatPesanan({ orders, filters }) {
    const [search, setSearch]   = useState(filters.search  ?? '');
    const [date,   setDate]     = useState(filters.date    ?? TODAY);
    const [method, setMethod]   = useState(filters.method  ?? '');
    const searchTimer = useRef(null);

    function apply(overrides = {}) {
        const params = { search, date, method, ...overrides };
        // strip empty strings so Laravel skips the when() clauses
        Object.keys(params).forEach(k => { if (params[k] === '') delete params[k]; });
        router.get(route('cashier.riwayat'), params, { preserveState: true, replace: true });
    }

    function handleSearch(e) {
        const val = e.target.value;
        setSearch(val);
        clearTimeout(searchTimer.current);
        searchTimer.current = setTimeout(() => apply({ search: val }), 400);
    }

    function handleDate(e) {
        const val = e.target.value;
        setDate(val);
        apply({ date: val });
    }

    function handleMethod(e) {
        const val = e.target.value;
        setMethod(val);
        apply({ method: val });
    }

    return (
        <CashierLayout title="Riwayat Pesanan">

            {/* ── Header ── */}
            <div style={{ marginBottom: 28 }}>
                <h1 style={{
                    fontSize: 26, fontWeight: 700, color: '#0F172A',
                    margin: '0 0 4px', letterSpacing: '-0.5px',
                }}>
                    Riwayat Pesanan
                </h1>
                <p style={{ fontSize: 14, color: '#64748B', margin: 0 }}>
                    Lihat semua transaksi yang telah selesai
                </p>
            </div>

            {/* ── Filter Toolbar ── */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                {/* Search */}
                <div style={{ position: 'relative', flex: 1 }}>
                    <Search size={18} style={{
                        position: 'absolute', left: 14, top: '50%',
                        transform: 'translateY(-50%)', color: '#94A3B8', pointerEvents: 'none',
                    }} />
                    <input
                        type="text"
                        value={search}
                        onChange={handleSearch}
                        placeholder="Cari transaksi..."
                        style={{
                            width: '100%', height: 44,
                            border: '1px solid #E2E8F0', borderRadius: 8,
                            padding: '0 16px 0 44px', fontSize: 14, color: '#0F172A',
                            background: '#FFFFFF', outline: 'none', boxSizing: 'border-box',
                            boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
                        }}
                    />
                </div>

                {/* Date filter */}
                <div style={{ position: 'relative' }}>
                    <Calendar size={16} style={{
                        position: 'absolute', left: 14, top: '50%',
                        transform: 'translateY(-50%)', color: '#64748B', pointerEvents: 'none',
                    }} />
                    <input
                        type="date"
                        value={date}
                        onChange={handleDate}
                        style={{
                            height: 44, width: 180,
                            border: '1px solid #E2E8F0', borderRadius: 8,
                            padding: '0 14px 0 40px', fontSize: 13, color: '#0F172A',
                            background: '#FFFFFF', outline: 'none', boxSizing: 'border-box',
                            boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
                        }}
                    />
                </div>

                {/* Payment method filter */}
                <div style={{ position: 'relative' }}>
                    <CreditCard size={16} style={{
                        position: 'absolute', left: 14, top: '50%',
                        transform: 'translateY(-50%)', color: '#64748B', pointerEvents: 'none',
                    }} />
                    <select
                        value={method}
                        onChange={handleMethod}
                        style={{
                            height: 44, width: 180,
                            border: '1px solid #E2E8F0', borderRadius: 8,
                            padding: '0 36px 0 40px', fontSize: 13, color: '#0F172A',
                            background: '#FFFFFF', outline: 'none', appearance: 'none',
                            boxSizing: 'border-box', cursor: 'pointer',
                            boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
                        }}
                    >
                        <option value="">Semua Metode</option>
                        {Object.entries(METHOD_LABELS).map(([v, l]) => (
                            <option key={v} value={v}>{l}</option>
                        ))}
                    </select>
                    <ChevronDown size={14} style={{
                        position: 'absolute', right: 12, top: '50%',
                        transform: 'translateY(-50%)', color: '#64748B', pointerEvents: 'none',
                    }} />
                </div>
            </div>

            {/* ── Table Card ── */}
            <div style={{
                background: '#FFFFFF', borderRadius: 16,
                border: '1px solid #E2E8F0',
                boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                overflow: 'hidden',
            }}>
                {/* Table head */}
                <div style={{
                    display: 'flex', alignItems: 'center',
                    background: '#F1F5F9', padding: '12px 16px',
                    borderBottom: '1px solid #E2E8F0',
                }}>
                    {COLS.map(col => (
                        <div key={col.key} style={{ width: col.width, flex: col.flex, flexShrink: 0 }}>
                            <span style={{ fontSize: 12, fontWeight: 600, color: '#64748B' }}>
                                {col.label}
                            </span>
                        </div>
                    ))}
                </div>

                {/* Rows */}
                {orders.length === 0 ? (
                    <div style={{
                        textAlign: 'center', color: '#94A3B8',
                        padding: '48px 16px', fontSize: 14,
                    }}>
                        Tidak ada data riwayat pesanan
                    </div>
                ) : (
                    orders.map(order => (
                        <OrderRow key={order.id} order={order} />
                    ))
                )}
            </div>

        </CashierLayout>
    );
}

/* ── Column config ── */
const COLS = [
    { key: 'id',       label: 'ID Pesanan',  width: 110, flex: undefined },
    { key: 'date',     label: 'Tanggal',     width: 120, flex: undefined },
    { key: 'time',     label: 'Waktu',       width: 70,  flex: undefined },
    { key: 'total',    label: 'Total',       width: 130, flex: undefined },
    { key: 'payment',  label: 'Pembayaran',  width: 110, flex: undefined },
    { key: 'cashier',  label: 'Kasir',       width: undefined, flex: 1   },
    { key: 'status',   label: 'Status',      width: 110, flex: undefined },
    { key: 'action',   label: 'Aksi',        width: 70,  flex: undefined },
];

/* ── OrderRow ── */
function OrderRow({ order }) {
    const [hovered, setHovered] = useState(false);

    const methodLabel = {
        cash: 'Tunai', qris: 'QRIS', ewallet: 'E-Wallet', transfer: 'Transfer',
    }[order.payment_method] ?? order.payment_method ?? '—';

    return (
        <div
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            style={{
                display: 'flex', alignItems: 'center',
                padding: '14px 16px',
                borderBottom: '1px solid #E2E8F0',
                background: hovered ? '#F8FAFC' : '#FFFFFF',
                transition: 'background 0.1s',
            }}
        >
            <div style={{ width: 110, flexShrink: 0 }}>
                <span style={{ fontSize: 13, fontWeight: 600, color: '#0F172A' }}>
                    {order.order_code}
                </span>
            </div>
            <div style={{ width: 120, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: '#64748B' }}>
                    {formatDate(order.created_at)}
                </span>
            </div>
            <div style={{ width: 70, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: '#64748B' }}>
                    {formatTime(order.created_at)}
                </span>
            </div>
            <div style={{ width: 130, flexShrink: 0 }}>
                <span style={{ fontSize: 13, fontWeight: 600, color: '#0F172A' }}>
                    {formatRupiah(order.total_amount)}
                </span>
            </div>
            <div style={{ width: 110, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: '#64748B' }}>{methodLabel}</span>
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <span style={{
                    fontSize: 13, color: '#64748B',
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
                    href={route('cashier.order.show', order.id)}
                    style={{
                        fontSize: 13, fontWeight: 500,
                        color: '#3B6FD4', textDecoration: 'none',
                    }}
                >
                    Detail
                </Link>
            </div>
        </div>
    );
}
