import { useState } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import useCart from '@/Hooks/useCart';
import { ChevronLeft, Banknote, QrCode, MapPin, Wallet } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah } from '@/helpers';

export default function PaymentChoose({ order, items, table_number }) {
    const [selected,   setSelected]   = useState(null);
    const [loading,    setLoading]    = useState(false);
    const [error,      setError]      = useState('');
    const [showCashModal, setShowCashModal] = useState(false);
    const { clearCart } = useCart();

    async function handleLanjut() {
        if (!selected || loading) return;
        setLoading(true);
        setError('');
        try {
            if (selected === 'cash') {
                await axios.post(`/api/order/${order.id}/pay/cash`);
                clearCart();
                setShowCashModal(true);          // tampilkan modal C5b
            } else {
                await axios.post(`/api/order/${order.id}/pay/qris`);
                router.visit(`/customer/payment/${order.order_code}/qris`);
            }
        } catch (err) {
            setError(err.response?.data?.message ?? 'Terjadi kesalahan. Coba lagi.');
        } finally {
            setLoading(false);
        }
    }

    function handleMengerti() {
        // Kembali ke menu, bawa tableId dari sessionStorage
        let tableId = null;
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (saved) tableId = JSON.parse(saved)?.tableId;
        } catch (_) {}
        router.visit(tableId ? `/customer/menu?table=${tableId}` : '/customer/menu');
    }

    const METHODS = [
        {
            key:   'cash',
            title: 'Bayar ke Kasir (Cash)',
            desc:  'Bayar langsung di kasir setelah pesanan dikonfirmasi',
            Icon:  Banknote,
            iconBg: '#FEF3EC',
            iconColor: '#E8763A',
        },
        {
            key:   'qris',
            title: 'QRIS',
            desc:  'Scan QR code & upload bukti pembayaran',
            Icon:  QrCode,
            iconBg: '#F5F0EB',
            iconColor: '#8C7B6B',
        },
    ];

    return (
        <CustomerLayout activeTab="cart">
            {/* ── Header ── */}
            <div style={{
                background: '#FFFFFF',
                borderBottom: '1px solid #F0EBE5',
                padding: '22px 24px 16px',
                display: 'flex', flexDirection: 'column', gap: 4,
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                    <button
                        onClick={() => router.visit('/customer/cart')}
                        style={{
                            width: 36, height: 36, borderRadius: 12,
                            background: '#F0EBE5', border: 'none', cursor: 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            flexShrink: 0,
                        }}
                    >
                        <ChevronLeft size={20} color="#2D2016" />
                    </button>
                    <div>
                        <div style={{ fontSize: 20, fontWeight: 700, color: '#2D2016', fontFamily: '"DM Sans", system-ui' }}>
                            Pilih Cara Bayar
                        </div>
                        <div style={{ fontSize: 12, color: '#8C7B6B', fontFamily: 'Outfit, system-ui' }}>
                            Pesanan #{order.order_code}
                        </div>
                    </div>
                </div>
                {table_number && (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, paddingLeft: 50 }}>
                        <MapPin size={12} color="#B5A898" />
                        <span style={{ fontSize: 12, fontWeight: 500, color: '#B5A898', fontFamily: 'Outfit, system-ui' }}>
                            Meja {table_number}
                        </span>
                    </div>
                )}
            </div>

            {/* ── Content ── */}
            <div style={{
                padding: '0 24px 24px',
                display: 'flex', flexDirection: 'column', gap: 18,
            }}>

                {/* Ringkasan label */}
                <div style={{ fontSize: 13, fontWeight: 600, color: '#8C7B6B', letterSpacing: 0.5, fontFamily: 'Outfit, system-ui', paddingTop: 18 }}>
                    RINGKASAN PESANAN
                </div>

                {/* Order summary card */}
                <div style={{
                    background: '#FFFFFF', borderRadius: 20,
                    border: '1px solid #EDE8E2',
                    boxShadow: '0 3px 12px rgba(45,32,22,0.05)',
                    overflow: 'hidden',
                }}>
                    {items.map((item, idx) => (
                        <div
                            key={idx}
                            style={{
                                display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                                padding: '14px 18px',
                                borderBottom: idx < items.length - 1 ? '1px solid #F5F0EB' : 'none',
                            }}
                        >
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                <div style={{
                                    width: 26, height: 26, borderRadius: 8,
                                    background: '#FEF3EC',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    flexShrink: 0,
                                }}>
                                    <span style={{ fontSize: 11, fontWeight: 700, color: '#E8763A', fontFamily: 'Outfit, system-ui' }}>
                                        {item.qty}x
                                    </span>
                                </div>
                                <span style={{ fontSize: 14, fontWeight: 500, color: '#2D2016', fontFamily: 'Outfit, system-ui' }}>
                                    {item.name}
                                </span>
                            </div>
                            <span style={{ fontSize: 14, fontWeight: 600, color: '#2D2016', fontFamily: 'Outfit, system-ui' }}>
                                {formatRupiah(item.subtotal)}
                            </span>
                        </div>
                    ))}
                    {/* Divider */}
                    <div style={{ height: 1, background: '#EDE8E2' }} />
                    {/* Total row */}
                    <div style={{
                        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                        padding: '10px 18px 16px',
                    }}>
                        <span style={{ fontSize: 16, fontWeight: 700, color: '#2D2016', fontFamily: '"DM Sans", system-ui' }}>
                            Total
                        </span>
                        <span style={{ fontSize: 18, fontWeight: 700, color: '#E8763A', fontFamily: '"DM Sans", system-ui' }}>
                            {formatRupiah(order.total_amount)}
                        </span>
                    </div>
                </div>

                {/* Metode label */}
                <div style={{ fontSize: 13, fontWeight: 600, color: '#8C7B6B', letterSpacing: 0.5, fontFamily: 'Outfit, system-ui' }}>
                    METODE PEMBAYARAN
                </div>

                {/* Payment methods */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                    {METHODS.map(({ key, title, desc, Icon, iconBg, iconColor }) => {
                        const active = selected === key;
                        return (
                            <div
                                key={key}
                                onClick={() => setSelected(key)}
                                style={{
                                    background: active ? '#FFFBF8' : '#FFFFFF',
                                    borderRadius: 16,
                                    border: `${active ? 2 : 1}px solid ${active ? '#E8763A' : '#EDE8E2'}`,
                                    boxShadow: active ? '0 2px 10px rgba(232,118,58,0.12)' : 'none',
                                    padding: '16px 18px 16px 16px',
                                    display: 'flex', alignItems: 'center', gap: 14,
                                    cursor: 'pointer', transition: 'all 0.15s',
                                }}
                            >
                                <div style={{
                                    width: 42, height: 42, borderRadius: 12,
                                    background: active ? '#FEF3EC' : iconBg,
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    flexShrink: 0,
                                }}>
                                    {key === 'qris'
                                        ? <img src="/images/logo-qris.png" alt="QRIS" style={{ width: 28, height: 28, objectFit: 'contain' }} />
                                        : <Icon size={22} color={active ? '#E8763A' : iconColor} />
                                    }
                                </div>
                                <div style={{ flex: 1 }}>
                                    <div style={{ fontSize: 15, fontWeight: 600, color: '#2D2016', fontFamily: 'Outfit, system-ui' }}>
                                        {title}
                                    </div>
                                    <div style={{ fontSize: 12, color: '#8C7B6B', marginTop: 2, fontFamily: 'Outfit, system-ui' }}>
                                        {desc}
                                    </div>
                                </div>
                                {/* Radio */}
                                <div style={{
                                    width: 22, height: 22, borderRadius: '50%', flexShrink: 0,
                                    border: `${active ? 2 : 1.5}px solid ${active ? '#E8763A' : '#D6CFC6'}`,
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                }}>
                                    {active && (
                                        <div style={{ width: 12, height: 12, borderRadius: '50%', background: '#E8763A' }} />
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Spacer */}
                <div style={{ flex: 1 }} />

                {error && (
                    <div style={{
                        background: '#FEF2F2', border: '1px solid #FECACA',
                        borderRadius: 10, padding: '10px 14px',
                        fontSize: 13, color: '#DC2626', fontFamily: 'Outfit, system-ui',
                    }}>
                        {error}
                    </div>
                )}

                {/* CTA */}
                <button
                    onClick={handleLanjut}
                    disabled={!selected || loading}
                    style={{
                        width: '100%', height: 54,
                        background: !selected ? '#EDE8E2' : '#E8763A',
                        color: !selected ? '#9AA3AF' : '#FFFFFF',
                        border: 'none', borderRadius: 18,
                        fontSize: 16, fontWeight: 700,
                        cursor: !selected ? 'default' : 'pointer',
                        boxShadow: !selected ? 'none' : '0 4px 16px rgba(232,118,58,0.30)',
                        display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                        fontFamily: '"DM Sans", system-ui',
                        transition: 'all 0.15s',
                    }}
                >
                    {loading ? 'Memproses...' : 'Konfirmasi Pembayaran'}
                </button>
            </div>

            {/* ── C5b Modal: Notif Tunai ── */}
            {showCashModal && (
                <div style={{
                    position: 'fixed', inset: 0,
                    background: 'rgba(0,0,0,0.50)',
                    zIndex: 200,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    padding: '0 24px',
                }}>
                    <div style={{
                        background: '#FFFFFF',
                        borderRadius: 24,
                        width: '100%', maxWidth: 300,
                        padding: '28px 24px 24px',
                        display: 'flex', flexDirection: 'column',
                        alignItems: 'center', gap: 16,
                        boxShadow: '0 8px 30px rgba(45,32,22,0.20)',
                    }}>
                        {/* Icon circle */}
                        <div style={{
                            width: 72, height: 72, borderRadius: '50%',
                            background: '#FEF3EC',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                        }}>
                            <Wallet size={36} color="#E8763A" />
                        </div>

                        {/* Title */}
                        <span style={{
                            fontSize: 18, fontWeight: 700, color: '#2D2016',
                            fontFamily: '"DM Sans", system-ui', textAlign: 'center',
                        }}>
                            Bayar di Kasir
                        </span>

                        {/* Desc */}
                        <span style={{
                            fontSize: 13, color: '#8C7B6B', lineHeight: 1.5,
                            fontFamily: 'Outfit, system-ui', textAlign: 'center',
                        }}>
                            Silakan tunjukkan pesanan ini ke kasir dan lakukan pembayaran tunai.
                        </span>

                        {/* Amount box */}
                        <div style={{
                            width: '100%', background: '#FEF8F4',
                            borderRadius: 14, padding: '12px 16px',
                            display: 'flex', flexDirection: 'column',
                            alignItems: 'center', gap: 2,
                        }}>
                            <span style={{ fontSize: 11, fontWeight: 500, color: '#B5A898', fontFamily: 'Outfit, system-ui' }}>
                                Total Pembayaran
                            </span>
                            <span style={{
                                fontSize: 22, fontWeight: 700, color: '#E8763A',
                                fontFamily: '"DM Sans", system-ui', letterSpacing: -0.5,
                            }}>
                                {formatRupiah(order.total_amount)}
                            </span>
                        </div>

                        {/* Mengerti button */}
                        <button
                            onClick={handleMengerti}
                            style={{
                                width: '100%', height: 46,
                                background: '#E8763A', color: '#FFFFFF',
                                border: 'none', borderRadius: 14,
                                fontSize: 15, fontWeight: 700, cursor: 'pointer',
                                fontFamily: '"DM Sans", system-ui',
                                boxShadow: '0 3px 10px rgba(232,118,58,0.25)',
                            }}
                        >
                            Mengerti
                        </button>
                    </div>
                </div>
            )}
        </CustomerLayout>
    );
}
