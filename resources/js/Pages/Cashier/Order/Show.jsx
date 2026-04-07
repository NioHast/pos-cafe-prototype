import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import axios from 'axios';
import { ArrowLeft, X, CircleCheck } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

// Cashier blue theme (consistent with Dashboard, PesananAktif, etc.)
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

const paymentLabel = { cash: 'Tunai (Cash)', qris: 'QRIS' };

export default function OrderShow({ order }) {
    const [processing,     setProcessing]     = useState(false);
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [rejectNote,      setRejectNote]      = useState('');

    const isCashPending = order.status === 'pending' && order.payment_method === 'cash';
    const isQrisPending = order.status === 'pending' && order.payment_method === 'qris' && !!order.payment_proof;
    const canAdvance    = order.status === 'diproses';

    async function handleAction(url, body = {}) {
        if (processing) return;
        setProcessing(true);
        try {
            await axios.patch(url, body);
            router.reload();
        } finally {
            setProcessing(false);
        }
    }

    function handleAdvance()      { handleAction(`/cashier/order/${order.id}/status`, { status: 'selesai' }); }
    function handleConfirmCash()  { handleAction(`/cashier/order/${order.id}/confirm-cash`); }
    function handleConfirmQris()  { handleAction(`/cashier/order/${order.id}/confirm-qris`); setShowRejectModal(false); }
    function handleRejectQris()   { handleAction(`/cashier/order/${order.id}/reject-qris`, { note: rejectNote }); setShowRejectModal(false); setRejectNote(''); }

    return (
        <CashierLayout title={`Detail Pesanan ${order.order_code}`} fullscreen>
            <div style={{ flex: 1, overflowY: 'auto', padding: 32, background: '#F8FAFC' }}>
            <div style={{ background: '#FFFFFF', borderRadius: 12, padding: 24, border: '1px solid #E2E8F0', boxShadow: '0 2px 8px rgba(15,23,42,0.03)' }}>

            {/* ── Header ── */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 28 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                    <Link href="/cashier/pesanan-aktif" style={{
                        width: 36, height: 36, borderRadius: 8, flexShrink: 0,
                        background: T.surface, border: `1px solid ${T.border}`,
                        boxShadow: T.shadowSm, textDecoration: 'none',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        color: T.textPri,
                    }}>
                        <ArrowLeft size={18} />
                    </Link>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                        <h1 style={{
                            fontSize: 24, fontWeight: 700, color: T.textPri,
                            margin: 0, letterSpacing: '-0.5px',
                            fontFamily: '"DM Sans", system-ui',
                        }}>
                            Detail Pesanan {order.order_code}
                        </h1>
                        <p style={{ fontSize: 14, color: T.textSec, margin: 0, fontFamily: 'Outfit, system-ui' }}>
                            {formatDate(order.created_at)}, {formatTime(order.created_at)} WIB
                        </p>
                    </div>
                </div>

                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    {canAdvance && (
                        <button
                            onClick={handleAdvance}
                            disabled={processing}
                            style={{
                                height: 36, padding: '0 18px',
                                background: processing ? '#8EC4A0' : T.accent,
                                color: '#FFFFFF', border: 'none', borderRadius: 8,
                                fontSize: 13, fontWeight: 600,
                                fontFamily: 'Outfit, system-ui',
                                cursor: processing ? 'not-allowed' : 'pointer',
                            }}
                        >
                            {processing ? 'Memproses...' : 'Tandai Selesai'}
                        </button>
                    )}
                    <StatusBadge status={order.status} />
                </div>
            </div>

            {/* ── Cash Confirmation Banner ── */}
            {isCashPending && (
                <div style={{
                    background: '#FFF8E1', border: '1px solid #FFE082', borderRadius: 12,
                    padding: '16px 20px', marginBottom: 20,
                    display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                }}>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                        <span style={{ fontSize: 14, fontWeight: 700, color: '#B8860B', fontFamily: '"DM Sans", system-ui' }}>
                            Pelanggan Akan Bayar Tunai
                        </span>
                        <span style={{ fontSize: 13, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>
                            Total: <strong style={{ color: T.textPri }}>{formatRupiah(order.total_amount)}</strong> — Konfirmasi setelah uang diterima
                        </span>
                    </div>
                    <button
                        onClick={handleConfirmCash}
                        disabled={processing}
                        style={{
                            height: 40, padding: '0 20px', flexShrink: 0,
                            background: processing ? '#93AEDF' : T.accent, color: '#FFFFFF',
                            border: 'none', borderRadius: 8,
                            fontSize: 13, fontWeight: 700,
                            fontFamily: 'Outfit, system-ui',
                            cursor: processing ? 'not-allowed' : 'pointer',
                        }}
                    >
                        ✓ Konfirmasi Pembayaran
                    </button>
                </div>
            )}

            {/* ── QRIS Proof Banner ── */}
            {isQrisPending && (
                <div style={{
                    background: '#E3F2FD', border: '1px solid #90CAF9', borderRadius: 12,
                    padding: '16px 20px', marginBottom: 20,
                    display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                }}>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                        <span style={{ fontSize: 14, fontWeight: 700, color: '#1565C0', fontFamily: '"DM Sans", system-ui' }}>
                            Bukti Pembayaran QRIS Diterima
                        </span>
                        <span style={{ fontSize: 13, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>
                            Verifikasi bukti transfer pelanggan sebelum memproses pesanan
                        </span>
                    </div>
                    <div style={{ display: 'flex', gap: 8, flexShrink: 0 }}>
                        <button
                            onClick={() => setShowRejectModal(true)}
                            style={{
                                height: 36, padding: '0 14px',
                                background: '#FFFFFF', color: '#C95D4A',
                                border: '1.5px solid #C95D4A', borderRadius: 8,
                                fontSize: 13, fontWeight: 600,
                                fontFamily: 'Outfit, system-ui', cursor: 'pointer',
                            }}
                        >
                            Tolak
                        </button>
                        <button
                            onClick={handleConfirmQris}
                            disabled={processing}
                            style={{
                                height: 36, padding: '0 14px',
                                background: T.accent, color: '#FFFFFF',
                                border: 'none', borderRadius: 8,
                                fontSize: 13, fontWeight: 700,
                                fontFamily: 'Outfit, system-ui', cursor: processing ? 'not-allowed' : 'pointer',
                            }}
                        >
                            ✓ Konfirmasi
                        </button>
                    </div>
                </div>
            )}

            {/* ── 2-column content ── */}
            <div style={{ display: 'flex', gap: 24, alignItems: 'flex-start' }}>

                {/* LEFT — Items Card */}
                <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{
                        background: T.surface, borderRadius: 16,
                        border: `1px solid ${T.border}`,
                        boxShadow: T.shadow, overflow: 'hidden',
                    }}>
                        {/* Card title */}
                        <div style={{
                            padding: '16px 20px',
                            background: T.elevated, borderBottom: `1px solid ${T.border}`,
                        }}>
                            <span style={{
                                fontSize: 16, fontWeight: 600, color: T.textPri,
                                fontFamily: 'Outfit, system-ui',
                            }}>
                                Daftar Item Pesanan
                            </span>
                        </div>
                        {/* Column headers */}
                        <div style={{
                            display: 'flex', padding: '12px 20px',
                            borderBottom: `1px solid ${T.border}`,
                        }}>
                            <div style={{ flex: 1 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>Nama Item</span>
                            </div>
                            <div style={{ width: 100, flexShrink: 0 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>Harga</span>
                            </div>
                            <div style={{ width: 80, flexShrink: 0 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>Jumlah</span>
                            </div>
                            <div style={{ width: 120, flexShrink: 0 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>Subtotal</span>
                            </div>
                        </div>
                        {/* Rows */}
                        {order.items.map(item => (
                            <div key={item.id} style={{
                                display: 'flex', padding: '14px 20px',
                                borderBottom: `1px solid ${T.border}`,
                            }}>
                                <div style={{ flex: 1 }}>
                                    <span style={{ fontSize: 14, fontWeight: 500, color: T.textPri, fontFamily: 'Outfit, system-ui' }}>
                                        {item.name}
                                    </span>
                                </div>
                                <div style={{ width: 100, flexShrink: 0 }}>
                                    <span style={{ fontSize: 13, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>
                                        {formatRupiah(item.unit_price)}
                                    </span>
                                </div>
                                <div style={{ width: 80, flexShrink: 0 }}>
                                    <span style={{ fontSize: 13, fontWeight: 600, color: T.textPri, fontFamily: 'Outfit, system-ui' }}>
                                        {item.quantity}
                                    </span>
                                </div>
                                <div style={{ width: 120, flexShrink: 0 }}>
                                    <span style={{ fontSize: 13, fontWeight: 600, color: T.textPri, fontFamily: 'Outfit, system-ui' }}>
                                        {formatRupiah(item.subtotal)}
                                    </span>
                                </div>
                            </div>
                        ))}
                        {/* Total row */}
                        <div style={{
                            display: 'flex', justifyContent: 'space-between',
                            padding: '16px 20px', background: T.elevated,
                        }}>
                            <span style={{
                                fontSize: 16, fontWeight: 700, color: T.textPri,
                                fontFamily: 'Outfit, system-ui',
                            }}>
                                Total Pembayaran
                            </span>
                            <span style={{
                                fontSize: 20, fontWeight: 700, color: T.accent,
                                fontFamily: 'Outfit, system-ui',
                            }}>
                                {formatRupiah(order.total_amount)}
                            </span>
                        </div>
                    </div>

                </div>

                {/* RIGHT — Info Card */}
                <div style={{ width: 360, flexShrink: 0 }}>
                    <div style={{
                        background: T.surface, borderRadius: 16,
                        border: `1px solid ${T.border}`, boxShadow: T.shadow,
                        overflow: 'hidden',
                    }}>
                        <div style={{
                            padding: '16px 20px',
                            background: T.elevated, borderBottom: `1px solid ${T.border}`,
                        }}>
                            <span style={{ fontSize: 16, fontWeight: 600, color: T.textPri, fontFamily: 'Outfit, system-ui' }}>
                                Informasi Pesanan
                            </span>
                        </div>
                        <div style={{ padding: 20, display: 'flex', flexDirection: 'column', gap: 16 }}>
                            <InfoRow label="ID Pesanan"         value={order.order_code}   bold />
                            <InfoRow label="Nama Pelanggan"     value={order.customer_name} bold />
                            <InfoRow label="No. Telepon"        value={order.customer_phone} />
                            <InfoRow label="Meja"               value={order.table_number ? `No. ${order.table_number}` : '—'} />
                            <InfoRow label="Tanggal"            value={formatDate(order.created_at)} />
                            <InfoRow label="Waktu"              value={`${formatTime(order.created_at)} WIB`} />
                            <InfoRow label="Metode Pembayaran"  value={paymentLabel[order.payment_method] ?? '—'} bold />
                            <InfoRow label="Kasir"              value={order.cashier_name ?? '—'} />
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                <span style={{ fontSize: 13, fontWeight: 500, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>
                                    Status
                                </span>
                                <StatusBadge status={order.status} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* ── Reject Modal ── */}
            {showRejectModal && (
                <div style={{
                    position: 'fixed', inset: 0,
                    background: 'rgba(0,0,0,0.40)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    zIndex: 200,
                }}>
                    <div style={{
                        background: T.surface, borderRadius: 16, padding: 28,
                        width: 420,
                        boxShadow: '0 20px 60px rgba(15,23,42,0.20)',
                    }}>
                        <h3 style={{
                            fontSize: 18, fontWeight: 700, color: T.textPri,
                            marginBottom: 8, fontFamily: '"DM Sans", system-ui',
                        }}>
                            Tolak Bukti QRIS
                        </h3>
                        <p style={{ fontSize: 14, color: T.textSec, marginBottom: 16, fontFamily: 'Outfit, system-ui' }}>
                            Isi alasan penolakan (opsional). Pelanggan dapat upload ulang.
                        </p>
                        <textarea
                            value={rejectNote}
                            onChange={e => setRejectNote(e.target.value)}
                            placeholder="Contoh: Nominal tidak sesuai / Bukti tidak jelas"
                            rows={3}
                            style={{
                                width: '100%', border: `1px solid ${T.border}`, borderRadius: 10,
                                padding: 12, fontSize: 14, resize: 'vertical',
                                boxSizing: 'border-box', outline: 'none',
                                fontFamily: 'Outfit, system-ui', color: T.textPri,
                                background: T.elevated,
                            }}
                        />
                        <div style={{ display: 'flex', gap: 12, marginTop: 16 }}>
                            <button
                                onClick={() => setShowRejectModal(false)}
                                style={{
                                    flex: 1, height: 42,
                                    background: T.surface, color: T.textSec,
                                    border: `1px solid ${T.border}`, borderRadius: 8,
                                    fontSize: 14, cursor: 'pointer',
                                    fontFamily: 'Outfit, system-ui',
                                }}
                            >
                                Batal
                            </button>
                            <button
                                onClick={handleRejectQris}
                                style={{
                                    flex: 1, height: 42,
                                    background: '#C95D4A', color: '#FFFFFF',
                                    border: 'none', borderRadius: 8,
                                    fontSize: 14, fontWeight: 700, cursor: 'pointer',
                                    fontFamily: 'Outfit, system-ui',
                                }}
                            >
                                Konfirmasi Tolak
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

function InfoRow({ label, value, bold }) {
    return (
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <span style={{ fontSize: 13, fontWeight: 500, color: T.textSec, fontFamily: 'Outfit, system-ui' }}>
                {label}
            </span>
            <span style={{ fontSize: 13, fontWeight: bold ? 600 : 400, color: T.textPri, fontFamily: 'Outfit, system-ui' }}>
                {value}
            </span>
        </div>
    );
}
