import { useState } from 'react';
import { router, useForm, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const nextStatus = {
    pending:   'confirmed',
    confirmed: 'preparing',
    preparing: 'ready',
    ready:     'completed',
};

const nextLabel = {
    pending:   'Konfirmasi',
    confirmed: 'Mulai Proses',
    preparing: 'Tandai Siap',
    ready:     'Selesai',
};

const paymentLabel = {
    cash:     'Tunai',
    qris:     'QRIS',
    ewallet:  'E-Wallet',
    transfer: 'Transfer Bank',
};

const gatewayLabel = {
    midtrans: 'Midtrans',
    manual:   'Manual',
};

export default function OrderShow({ order }) {
    const [btnHover, setBtnHover] = useState(false);
    const { patch, processing } = useForm();

    const canAdvance = nextStatus[order.status] !== undefined;
    const target     = nextStatus[order.status];
    const label      = nextLabel[order.status];

    function handleAdvance() {
        patch(route('cashier.order.status', order.id), {
            data: { status: target },
            preserveScroll: true,
        });
    }

    /* ── Format method string ── */
    const methodStr = (() => {
        const m = paymentLabel[order.payment_method] ?? order.payment_method ?? '—';
        const g = order.payment_gateway ? ` (${gatewayLabel[order.payment_gateway] ?? order.payment_gateway})` : '';
        return m + g;
    })();

    return (
        <CashierLayout title={`Detail Pesanan ${order.order_code}`}>

            {/* ── Header ── */}
            <div style={{
                display: 'flex', alignItems: 'center',
                justifyContent: 'space-between', marginBottom: 28,
            }}>
                {/* Left: back + title */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <Link
                        href={route('cashier.riwayat')}
                        style={{
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            width: 36, height: 36, borderRadius: 8,
                            background: '#FFFFFF', border: '1px solid #E2E8F0',
                            boxShadow: '0 2px 8px rgba(15,23,42,0.05)',
                            color: '#0F172A', textDecoration: 'none', flexShrink: 0,
                        }}
                    >
                        <ArrowLeft size={18} />
                    </Link>
                    <div>
                        <h1 style={{
                            fontSize: 24, fontWeight: 700, color: '#0F172A',
                            margin: '0 0 2px', letterSpacing: '-0.5px',
                        }}>
                            Detail Pesanan {order.order_code}
                        </h1>
                        <p style={{ fontSize: 14, color: '#64748B', margin: 0 }}>
                            {formatDate(order.created_at)}, {formatTime(order.created_at)} WIB
                        </p>
                    </div>
                </div>

                {/* Right: badge + advance button */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    {canAdvance && (
                        <button
                            onClick={handleAdvance}
                            disabled={processing}
                            onMouseEnter={() => setBtnHover(true)}
                            onMouseLeave={() => setBtnHover(false)}
                            style={{
                                height: 36, padding: '0 18px',
                                background: processing ? '#93AEDF' : btnHover ? '#2E5DB8' : '#3B6FD4',
                                color: '#FFFFFF', border: 'none', borderRadius: 8,
                                fontSize: 13, fontWeight: 600,
                                cursor: processing ? 'not-allowed' : 'pointer',
                                transition: 'background 0.15s',
                                boxShadow: '0 2px 8px rgba(59,111,212,0.25)',
                            }}
                        >
                            {processing ? 'Memproses...' : label}
                        </button>
                    )}
                    <StatusBadge status={order.status} />
                </div>
            </div>

            {/* ── 2-column content ── */}
            <div style={{ display: 'flex', gap: 24, alignItems: 'flex-start' }}>

                {/* ── LEFT: Daftar Item Pesanan ── */}
                <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{
                        background: '#FFFFFF', borderRadius: 16,
                        border: '1px solid #E2E8F0',
                        boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                        overflow: 'hidden',
                    }}>
                        {/* Card title */}
                        <div style={{
                            padding: '16px 20px',
                            background: '#F1F5F9',
                            borderBottom: '1px solid #E2E8F0',
                        }}>
                            <span style={{ fontSize: 16, fontWeight: 600, color: '#0F172A' }}>
                                Daftar Item Pesanan
                            </span>
                        </div>

                        {/* Table head */}
                        <div style={{
                            display: 'flex', alignItems: 'center',
                            padding: '12px 20px', borderBottom: '1px solid #E2E8F0',
                        }}>
                            <div style={{ flex: 1 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#64748B' }}>Nama Item</span>
                            </div>
                            <div style={{ width: 100, flexShrink: 0 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#64748B' }}>Harga</span>
                            </div>
                            <div style={{ width: 80, flexShrink: 0 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#64748B' }}>Jumlah</span>
                            </div>
                            <div style={{ width: 120, flexShrink: 0 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#64748B' }}>Subtotal</span>
                            </div>
                        </div>

                        {/* Item rows */}
                        {order.items.map(item => (
                            <div
                                key={item.id}
                                style={{
                                    display: 'flex', alignItems: 'center',
                                    padding: '14px 20px', borderBottom: '1px solid #E2E8F0',
                                }}
                            >
                                <div style={{ flex: 1 }}>
                                    <span style={{ fontSize: 14, fontWeight: 500, color: '#0F172A' }}>
                                        {item.name}
                                    </span>
                                </div>
                                <div style={{ width: 100, flexShrink: 0 }}>
                                    <span style={{ fontSize: 13, color: '#64748B' }}>
                                        {formatRupiah(item.unit_price)}
                                    </span>
                                </div>
                                <div style={{ width: 80, flexShrink: 0 }}>
                                    <span style={{ fontSize: 13, fontWeight: 600, color: '#0F172A' }}>
                                        {item.quantity}
                                    </span>
                                </div>
                                <div style={{ width: 120, flexShrink: 0 }}>
                                    <span style={{ fontSize: 13, fontWeight: 600, color: '#0F172A' }}>
                                        {formatRupiah(item.subtotal)}
                                    </span>
                                </div>
                            </div>
                        ))}

                        {/* Total row */}
                        <div style={{
                            display: 'flex', alignItems: 'center',
                            justifyContent: 'space-between',
                            padding: '16px 20px',
                            background: '#F1F5F9',
                        }}>
                            <span style={{ fontSize: 16, fontWeight: 700, color: '#0F172A' }}>
                                Total Pembayaran
                            </span>
                            <span style={{ fontSize: 20, fontWeight: 700, color: '#3B6FD4' }}>
                                {formatRupiah(order.total_amount)}
                            </span>
                        </div>
                    </div>
                </div>

                {/* ── RIGHT: Informasi Pesanan ── */}
                <div style={{ width: 360, flexShrink: 0 }}>
                    <div style={{
                        background: '#FFFFFF', borderRadius: 16,
                        border: '1px solid #E2E8F0',
                        boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                        overflow: 'hidden',
                    }}>
                        {/* Card title */}
                        <div style={{
                            padding: '16px 20px',
                            background: '#F1F5F9',
                            borderBottom: '1px solid #E2E8F0',
                        }}>
                            <span style={{ fontSize: 16, fontWeight: 600, color: '#0F172A' }}>
                                Informasi Pesanan
                            </span>
                        </div>

                        {/* Info rows */}
                        <div style={{ padding: 20, display: 'flex', flexDirection: 'column', gap: 16 }}>
                            <InfoRow label="ID Pesanan"         value={order.order_code} bold />
                            <InfoRow label="Tanggal"            value={formatDate(order.created_at)} />
                            <InfoRow label="Waktu"              value={`${formatTime(order.created_at)} WIB`} />
                            <InfoRow label="Metode Pembayaran"  value={methodStr} bold />
                            <InfoRow label="Kasir"              value={order.cashier_name ?? '—'} />
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                <span style={{ fontSize: 13, fontWeight: 500, color: '#64748B' }}>Status</span>
                                <StatusBadge status={order.status} />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </CashierLayout>
    );
}

/* ── Reusable info row ── */
function InfoRow({ label, value, bold }) {
    return (
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <span style={{ fontSize: 13, fontWeight: 500, color: '#64748B' }}>{label}</span>
            <span style={{ fontSize: 13, fontWeight: bold ? 600 : 400, color: '#0F172A' }}>{value}</span>
        </div>
    );
}
