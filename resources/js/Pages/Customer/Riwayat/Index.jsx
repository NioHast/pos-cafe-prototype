import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { ClipboardList, X } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import RiwayatCard from '@/Components/Customer/RiwayatCard';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const TABS = [
    { key: 'all',      label: 'Semua'    },
    { key: 'pending',  label: 'Pending'  },
    { key: 'diproses', label: 'Diproses' },
    { key: 'selesai',  label: 'Selesai'  },
];

const METHOD_LABEL = { cash: 'Tunai', qris: 'QRIS' };

export default function CustomerRiwayat({ orders = [] }) {
    const [activeTab,    setActiveTab]    = useState('all');
    const [receiptOrder, setReceiptOrder] = useState(null);  // struk modal

    /* Nama pelanggan dari sessionStorage sebagai fallback */
    const sessionName = (() => {
        try {
            const s = sessionStorage.getItem('w9_customer');
            return s ? JSON.parse(s)?.name ?? null : null;
        } catch (_) { return null; }
    })();

    /* Jika phone ada di sessionStorage tapi belum ada di URL → reload dengan phone */
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (!params.get('phone')) {
            try {
                const saved = sessionStorage.getItem('w9_customer');
                if (saved) {
                    const data = JSON.parse(saved);
                    if (data?.phone) {
                        router.visit(`/customer/riwayat?phone=${encodeURIComponent(data.phone)}`, {
                            preserveState: true,
                            replace: true,
                        });
                    }
                }
            } catch (_) {}
        }
    }, []);

    /* Auto-refresh tiap 8 detik selama ada order aktif */
    useEffect(() => {
        const hasActive = orders.some(o => o.status !== 'selesai');
        if (!hasActive) return;
        const id = setInterval(() => {
            router.reload({ only: ['orders'], preserveState: true });
        }, 8000);
        return () => clearInterval(id);
    }, [orders]);

    function handleDetail(order) {
        setReceiptOrder(order);
    }

    const filteredOrders = orders.filter(o => {
        if (activeTab === 'all') return true;
        return o.status === activeTab;
    });

    return (
        <CustomerLayout activeTab="riwayat">
            <div style={{
                display: 'flex', flexDirection: 'column',
                minHeight: 'calc(100vh - 92px)',
                gap: 20, padding: '0 24px 24px',
            }}>

                {/* Header */}
                <div style={{
                    height: 56, display: 'flex',
                    alignItems: 'center', justifyContent: 'center',
                }}>
                    <span style={{
                        fontSize: 20, fontWeight: 700, color: '#2D2016',
                        fontFamily: '"DM Sans", system-ui, sans-serif',
                    }}>
                        Riwayat Pesanan
                    </span>
                </div>

                {/* Segment control */}
                <div style={{
                    background: '#F5F0EB', borderRadius: 18,
                    border: '1px solid #EDE8E2',
                    padding: 4, display: 'flex', height: 46,
                }}>
                    {TABS.map(tab => (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            style={{
                                flex: 1, height: '100%',
                                borderRadius: 14, border: 'none', cursor: 'pointer',
                                fontSize: 14,
                                fontFamily: 'Outfit, system-ui, sans-serif',
                                background:  activeTab === tab.key ? '#FFFFFF' : 'transparent',
                                color:       activeTab === tab.key ? '#2D2016' : '#B5A898',
                                fontWeight:  activeTab === tab.key ? 700 : 500,
                                boxShadow:   activeTab === tab.key
                                    ? '0 2px 6px rgba(45,32,22,0.10)' : 'none',
                                transition: 'background 0.15s, box-shadow 0.15s',
                            }}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* Order list */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14, flex: 1 }}>
                    {filteredOrders.length === 0 ? (
                        <div style={{
                            display: 'flex', flexDirection: 'column',
                            alignItems: 'center', justifyContent: 'center',
                            padding: '48px 0', gap: 12,
                        }}>
                            <div style={{
                                width: 64, height: 64, borderRadius: '50%',
                                background: '#F5F0EB',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                <ClipboardList size={28} color="#C4B5A5" />
                            </div>
                            <span style={{
                                fontSize: 14, color: '#B5A898',
                                fontFamily: 'Outfit, system-ui, sans-serif',
                            }}>
                                Belum ada pesanan
                            </span>
                        </div>
                    ) : (
                        filteredOrders.map(order => (
                            <RiwayatCard
                                key={order.id}
                                order={order}
                                onDetail={handleDetail}
                            />
                        ))
                    )}
                </div>

            </div>

            {/* ── Receipt Modal (Struk) ── */}
            {receiptOrder && (
                <div
                    onClick={() => setReceiptOrder(null)}
                    style={{
                        position: 'fixed', inset: 0,
                        background: 'rgba(0,0,0,0.55)',
                        zIndex: 300,
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        padding: '24px 20px',
                    }}
                >
                    <div
                        onClick={e => e.stopPropagation()}
                        style={{
                            background: '#FFFFFF',
                            borderRadius: 24,
                            width: '100%', maxWidth: 340,
                            maxHeight: 'calc(100vh - 48px)',
                            display: 'flex', flexDirection: 'column',
                            boxShadow: '0 16px 48px rgba(45,32,22,0.22)',
                            overflow: 'hidden',
                            fontFamily: 'Outfit, system-ui, sans-serif',
                        }}
                    >
                        {/* Close button */}
                        <div style={{ display: 'flex', justifyContent: 'flex-end', padding: '14px 16px 0' }}>
                            <button
                                onClick={() => setReceiptOrder(null)}
                                style={{
                                    width: 32, height: 32, borderRadius: '50%',
                                    background: '#F5F0EB', border: 'none', cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                }}
                            >
                                <X size={16} color="#8C7B6B" />
                            </button>
                        </div>

                        {/* Scrollable content */}
                        <div style={{ overflowY: 'auto', flex: 1, padding: '0 24px 8px' }}>

                            {/* Logo + cafe name */}
                            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 10, paddingBottom: 20 }}>
                                <div style={{
                                    width: 56, height: 56, borderRadius: 14,
                                    background: 'radial-gradient(ellipse 140% 140% at 50% 30%, #2A4F5F 0%, #1B3A4B 100%)',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    overflow: 'hidden',
                                    boxShadow: '0 4px 14px rgba(0,0,0,0.18)',
                                }}>
                                    <img
                                        src="/images/logo.jpg"
                                        alt="W9"
                                        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                        onError={e => {
                                            e.target.style.display = 'none';
                                            e.target.parentElement.innerHTML = '<span style="color:white;font-size:18px;font-style:italic;font-weight:700">w9</span>';
                                        }}
                                    />
                                </div>
                                <span style={{
                                    fontSize: 18, fontWeight: 700, color: '#2D2016',
                                    fontFamily: '"DM Sans", system-ui',
                                }}>
                                    W9 Cafe
                                </span>
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: '#EDE8E2', marginBottom: 16 }} />

                            {/* Info rows */}
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 16 }}>
                                {[
                                    { label: 'No. Pesanan', value: receiptOrder.order_code },
                                    { label: 'Tanggal',     value: `${formatDate(receiptOrder.created_at)}, ${formatTime(receiptOrder.created_at)}` },
                                    { label: 'Pelanggan',   value: receiptOrder.customer_name ?? sessionName ?? '—' },
                                    { label: 'Pembayaran',  value: METHOD_LABEL[receiptOrder.payment_method] ?? '—' },
                                ].map(row => (
                                    <div key={row.label} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 12 }}>
                                        <span style={{ fontSize: 13, color: '#B5A898', flexShrink: 0 }}>
                                            {row.label}
                                        </span>
                                        <span style={{ fontSize: 13, fontWeight: 600, color: '#2D2016', textAlign: 'right' }}>
                                            {row.value}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: '#EDE8E2', marginBottom: 14 }} />

                            {/* Items table header */}
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#B5A898', flex: 1 }}>Item</span>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#B5A898', width: 32, textAlign: 'center' }}>Qty</span>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#B5A898', width: 80, textAlign: 'right' }}>Harga</span>
                            </div>

                            {/* Items */}
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 14 }}>
                                {(receiptOrder.items ?? []).map((item, i) => (
                                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 13, color: '#2D2016', flex: 1 }}>{item.name}</span>
                                        <span style={{ fontSize: 13, color: '#8C7B6B', width: 32, textAlign: 'center' }}>{item.quantity}x</span>
                                        <span style={{ fontSize: 13, color: '#2D2016', width: 80, textAlign: 'right' }}>{formatRupiah(item.subtotal)}</span>
                                    </div>
                                ))}
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: '#EDE8E2', marginBottom: 12 }} />

                            {/* Subtotal */}
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                                <span style={{ fontSize: 13, color: '#8C7B6B' }}>Subtotal</span>
                                <span style={{ fontSize: 13, color: '#2D2016' }}>{formatRupiah(receiptOrder.total_amount)}</span>
                            </div>

                            {/* Total */}
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                                <span style={{ fontSize: 16, fontWeight: 700, color: '#2D2016', fontFamily: '"DM Sans", system-ui' }}>Total</span>
                                <span style={{ fontSize: 16, fontWeight: 700, color: '#E8763A', fontFamily: '"DM Sans", system-ui' }}>
                                    {formatRupiah(receiptOrder.total_amount)}
                                </span>
                            </div>

                            {/* Thank you */}
                            <div style={{ textAlign: 'center', marginBottom: 20 }}>
                                <span style={{ fontSize: 14, fontWeight: 600, color: '#2D2016', fontFamily: '"DM Sans", system-ui' }}>
                                    Terima kasih!
                                </span>
                            </div>
                        </div>

                        {/* Footer: Tutup button */}
                        <div style={{ padding: '12px 24px 20px', flexShrink: 0 }}>
                            <button
                                onClick={() => setReceiptOrder(null)}
                                style={{
                                    width: '100%', height: 50,
                                    background: '#E8763A', color: '#FFFFFF',
                                    border: 'none', borderRadius: 16,
                                    fontSize: 15, fontWeight: 700, cursor: 'pointer',
                                    fontFamily: '"DM Sans", system-ui',
                                    boxShadow: '0 4px 14px rgba(232,118,58,0.30)',
                                }}
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </CustomerLayout>
    );
}
