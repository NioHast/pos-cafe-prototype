import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { X, CircleCheck, QrCode } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import OrderCard from '@/Components/Cashier/OrderCard';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

export default function PesananAktif({ orders, counts }) {
    const [activeTab,    setActiveTab]    = useState('all');
    const [qrisOrder,    setQrisOrder]    = useState(null);   // order for modal 4c
    const [rejectNote,   setRejectNote]   = useState('');
    const [processing,   setProcessing]   = useState(false);

    /* ── Auto-refresh tiap 10 detik ── */
    useEffect(() => {
        const id = setInterval(() => {
            router.reload({ only: ['orders', 'counts'] });
        }, 10000);
        return () => clearInterval(id);
    }, []);

    /* ── Tabs ── */
    const tabs = [
        { key: 'all',         label: `Semua (${counts.all})`,                  color: { text: '#475569', bg: '#F1F5F9', border: '#CBD5E1' } },
        { key: 'pending',     label: `Pending (${counts.pending})`,             color: { text: '#D97706', bg: '#FFFBEB', border: '#FCD34D' } },
        { key: 'diproses',    label: `Diproses (${counts.diproses})`,           color: { text: '#3B6FD4', bg: '#EFF6FF', border: '#93C5FD' } },
        { key: 'belum_bayar', label: `Belum Bayar (${counts.belum_bayar ?? 0})`, color: { text: '#EF4444', bg: '#FEF2F2', border: '#FCA5A5' } },
    ];

    const filteredOrders = (() => {
        switch (activeTab) {
            case 'pending':     return orders.filter(o => o.status === 'pending');
            case 'diproses':    return orders.filter(o => o.status === 'diproses');
            case 'belum_bayar': return orders.filter(o => o.is_paid === false);
            default:            return orders;
        }
    })();

    /* ── Actions ── */
    async function handleConfirmQris() {
        if (processing || !qrisOrder) return;
        setProcessing(true);
        try {
            await axios.patch(`/cashier/order/${qrisOrder.id}/confirm-qris`);
            setQrisOrder(null);
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    async function handleRejectQris() {
        if (processing || !qrisOrder) return;
        setProcessing(true);
        try {
            await axios.patch(`/cashier/order/${qrisOrder.id}/reject-qris`, { note: rejectNote });
            setQrisOrder(null);
            setRejectNote('');
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    async function handleMarkDone(orderId, targetStatus) {
        if (processing) return;
        setProcessing(true);
        try {
            await axios.patch(`/cashier/order/${orderId}/status`, { status: targetStatus });
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    async function handleConfirmPayment(orderId, paymentMethod) {
        if (processing) return;
        setProcessing(true);
        try {
            await axios.patch(`/cashier/order/${orderId}/confirm-payment`, { payment_method: paymentMethod });
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    return (
        <CashierLayout title="Pesanan Aktif" fullscreen>
            <div style={{ flex: 1, overflowY: 'auto', padding: 32, background: '#F8FAFC' }}>
            <div style={{ background: '#FFFFFF', borderRadius: 12, padding: 24, border: '1px solid #E2E8F0', boxShadow: '0 2px 8px rgba(15,23,42,0.03)' }}>

            {/* ── Header ── */}
            <div style={{ marginBottom: 20 }}>
                <h1 style={{
                    fontSize: 26, fontWeight: 700, color: '#0F172A',
                    margin: '0 0 4px', letterSpacing: '-0.5px',
                }}>
                    Pesanan Aktif
                </h1>
                <p style={{ fontSize: 14, color: '#64748B', margin: 0 }}>
                    Kelola semua pesanan yang sedang diproses
                </p>
            </div>

            {/* ── Filter Tabs ── */}
            <div style={{ display: 'flex', gap: 8, marginBottom: 24, flexWrap: 'wrap' }}>
                {tabs.map(tab => {
                    const active = activeTab === tab.key;
                    const color  = tab.color;
                    return (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            style={{
                                height: 36, padding: '0 16px', borderRadius: 100,
                                fontSize: 13, fontWeight: active ? 700 : 500,
                                cursor: 'pointer', transition: 'all 0.15s',
                                background: color.bg,
                                color:      color.text,
                                border:     `1.5px solid ${active ? color.border : 'transparent'}`,
                                opacity:    active ? 1 : 0.65,
                            }}
                        >
                            {tab.label}
                        </button>
                    );
                })}
            </div>

            {/* ── Order Grid ── */}
            {filteredOrders.length === 0 ? (
                <div style={{ textAlign: 'center', color: '#94A3B8', paddingTop: 64, fontSize: 14 }}>
                    Tidak ada pesanan aktif
                </div>
            ) : (
                <div style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))',
                    gap: 18,
                    alignItems: 'start',
                }}>
                    {filteredOrders.map(order => (
                        <OrderCard
                            key={order.id}
                            order={order}
                            onDetail={id => router.visit(`/cashier/order/${id}`)}
                            onOpenQrisModal={o => { setQrisOrder(o); setRejectNote(''); }}
                            onMarkDone={handleMarkDone}
                            onConfirmPayment={handleConfirmPayment}
                        />
                    ))}
                </div>
            )}

            {/* ── 4c: QRIS Konfirmasi Modal ── */}
            {qrisOrder && (
                <div style={{
                    position: 'fixed', inset: 0,
                    background: 'rgba(0,0,0,0.40)',
                    zIndex: 200,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    padding: '16px',
                }}>
                    <div style={{
                        background: '#FFFFFF',
                        borderRadius: 20,
                        width: '100%', maxWidth: 600,
                        maxHeight: 'calc(100vh - 32px)',
                        display: 'flex', flexDirection: 'column',
                        boxShadow: '0 12px 40px rgba(15,23,42,0.125), 0 2px 6px rgba(15,23,42,0.031)',
                        overflow: 'hidden',
                    }}>
                        {/* ── Header ── */}
                        <div style={{
                            display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                            padding: '14px 20px',
                            borderBottom: '1px solid #E2E8F0',
                            flexShrink: 0,
                        }}>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                                <span style={{
                                    fontSize: 16, fontWeight: 700, color: '#0F172A',
                                    fontFamily: '"DM Sans", system-ui',
                                }}>
                                    Konfirmasi Pembayaran QRIS
                                </span>
                                <span style={{
                                    fontSize: 12, color: '#64748B',
                                    fontFamily: 'Outfit, system-ui',
                                }}>
                                    #{qrisOrder.order_code}{qrisOrder.table_number ? ` · Meja ${qrisOrder.table_number}` : ''}
                                </span>
                            </div>
                            <button
                                onClick={() => setQrisOrder(null)}
                                style={{
                                    width: 32, height: 32, borderRadius: 8, flexShrink: 0,
                                    background: '#F1F5F9', border: 'none', cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    color: '#64748B',
                                }}
                            >
                                <X size={16} />
                            </button>
                        </div>

                        {/* ── Body (scrollable) ── */}
                        <div style={{ padding: '14px 20px', display: 'flex', gap: 14, overflowY: 'auto', flex: 1 }}>

                            {/* LEFT: Bukti gambar */}
                            <div style={{ flex: '0 0 200px', display: 'flex', flexDirection: 'column', gap: 8 }}>
                                <span style={{
                                    fontSize: 11, fontWeight: 600, color: '#64748B',
                                    fontFamily: 'Outfit, system-ui', letterSpacing: '0.3px',
                                }}>
                                    BUKTI TRANSFER
                                </span>
                                <div style={{
                                    background: '#F1F5F9', borderRadius: 10,
                                    border: '1px solid #E2E8F0', padding: 6,
                                    display: 'flex', flexDirection: 'column', gap: 5,
                                }}>
                                    <img
                                        src={`/storage/${qrisOrder.payment_proof}`}
                                        alt="Bukti QRIS"
                                        style={{
                                            width: '100%', height: 150,
                                            objectFit: 'contain', borderRadius: 6,
                                            cursor: 'pointer', display: 'block',
                                        }}
                                        onClick={() => window.open(`/storage/${qrisOrder.payment_proof}`, '_blank')}
                                    />
                                    <span style={{ fontSize: 10, color: '#94A3B8', fontFamily: 'Outfit, system-ui', textAlign: 'center' }}>
                                        Klik untuk perbesar
                                    </span>
                                </div>
                            </div>

                            {/* RIGHT: Info + items + textarea */}
                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 10, minWidth: 0 }}>

                                {/* Payment info */}
                                <div style={{
                                    background: '#F8FAFC', borderRadius: 10,
                                    border: '1px solid #E2E8F0', padding: '10px 14px',
                                    display: 'flex', flexDirection: 'column', gap: 7,
                                }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 12, color: '#64748B', fontFamily: 'Outfit, system-ui' }}>Metode</span>
                                        <span style={{
                                            display: 'flex', alignItems: 'center', gap: 4,
                                            fontSize: 12, fontWeight: 600, color: '#0F172A',
                                            fontFamily: 'Outfit, system-ui',
                                        }}>
                                            <QrCode size={12} color="#3B6FD4" />
                                            QRIS
                                        </span>
                                    </div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 12, color: '#64748B', fontFamily: 'Outfit, system-ui' }}>Waktu Bayar</span>
                                        <span style={{ fontSize: 12, fontWeight: 500, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>
                                            {formatTime(qrisOrder.created_at)} · {formatDate(qrisOrder.created_at)}
                                        </span>
                                    </div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 12, color: '#64748B', fontFamily: 'Outfit, system-ui' }}>Status</span>
                                        <StatusBadge status={qrisOrder.status} />
                                    </div>
                                    <div style={{ height: 1, background: '#E2E8F0' }} />
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 13, fontWeight: 700, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>Total</span>
                                        <span style={{
                                            fontSize: 16, fontWeight: 700, color: '#3B6FD4',
                                            fontFamily: '"DM Sans", system-ui',
                                        }}>
                                            {formatRupiah(qrisOrder.total_amount)}
                                        </span>
                                    </div>
                                </div>

                                {/* Detail pesanan */}
                                <div style={{
                                    background: '#F8FAFC', borderRadius: 10,
                                    border: '1px solid #E2E8F0', padding: '10px 14px',
                                    display: 'flex', flexDirection: 'column', gap: 6,
                                }}>
                                    <span style={{
                                        fontSize: 11, fontWeight: 600, color: '#64748B',
                                        fontFamily: 'Outfit, system-ui', letterSpacing: '0.3px',
                                    }}>
                                        DETAIL PESANAN
                                    </span>
                                    {qrisOrder.items?.map((item, i) => (
                                        <div key={i} style={{
                                            display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                                        }}>
                                            <span style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                                                <span style={{ fontSize: 12, fontWeight: 600, color: '#3B6FD4', fontFamily: 'Outfit, system-ui' }}>
                                                    {item.quantity}x
                                                </span>
                                                <span style={{ fontSize: 12, fontWeight: 500, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>
                                                    {item.name}
                                                </span>
                                            </span>
                                            <span style={{ fontSize: 12, color: '#64748B', fontFamily: 'Outfit, system-ui' }}>
                                                {formatRupiah(item.subtotal)}
                                            </span>
                                        </div>
                                    ))}
                                </div>

                                {/* Reject note textarea */}
                                <textarea
                                    value={rejectNote}
                                    onChange={e => setRejectNote(e.target.value)}
                                    placeholder="Alasan penolakan (opsional, jika Tolak)"
                                    rows={2}
                                    style={{
                                        width: '100%', border: '1px solid #E2E8F0',
                                        borderRadius: 8, padding: '8px 10px',
                                        fontSize: 12, fontFamily: 'Outfit, system-ui',
                                        resize: 'none', boxSizing: 'border-box',
                                        outline: 'none', color: '#0F172A',
                                        background: '#F8FAFC',
                                    }}
                                />
                            </div>
                        </div>

                        {/* ── Footer ── */}
                        <div style={{
                            padding: '12px 20px 14px',
                            borderTop: '1px solid #E2E8F0',
                            display: 'flex', gap: 10,
                            flexShrink: 0,
                        }}>
                            {/* Tolak */}
                            <button
                                onClick={handleRejectQris}
                                disabled={processing}
                                style={{
                                    flex: 1, height: 40,
                                    background: processing ? '#E8A898' : '#C95D4A',
                                    color: '#FFFFFF', border: 'none', borderRadius: 10,
                                    fontSize: 13, fontWeight: 600,
                                    fontFamily: 'Outfit, system-ui',
                                    cursor: processing ? 'not-allowed' : 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
                                }}
                            >
                                <X size={16} />
                                Tolak
                            </button>
                            {/* Konfirmasi Pembayaran */}
                            <button
                                onClick={handleConfirmQris}
                                disabled={processing}
                                style={{
                                    flex: 2, height: 40,
                                    background: processing ? '#8EC4A0' : '#5A9A6E',
                                    color: '#FFFFFF', border: 'none', borderRadius: 10,
                                    fontSize: 13, fontWeight: 700,
                                    fontFamily: '"DM Sans", system-ui',
                                    cursor: processing ? 'not-allowed' : 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
                                    boxShadow: processing ? 'none' : '0 3px 10px rgba(22,163,74,0.145)',
                                }}
                            >
                                <CircleCheck size={16} />
                                Konfirmasi Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            )}
            </div>
            </div>
        </CashierLayout>
    );
}
