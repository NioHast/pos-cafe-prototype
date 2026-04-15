import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { ShoppingBag } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import useCart from '@/Hooks/useCart';
import { formatRupiah } from '@/helpers';

export default function CustomerCart() {
    const { items, tableId, updateQty, total, count } = useCart();
    const [loading,      setLoading]      = useState(false);
    const [errorMsg,     setErrorMsg]     = useState('');
    const [isMahasiswa,  setIsMahasiswa]  = useState(false);

    const isEmpty = items.length === 0;

    useEffect(() => {
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (saved) {
                const data = JSON.parse(saved);
                setIsMahasiswa(data.isMahasiswa === true);
            }
        } catch (_) {}
    }, []);

    const totalCashback = isMahasiswa
        ? items.reduce((s, i) => s + (i.cashback ?? 0) * i.quantity, 0)
        : 0;

    const grandTotal = total - totalCashback;

    function handleIncrement(menuId) {
        const item = items.find(i => i.menuId === menuId);
        if (item) updateQty(menuId, item.quantity + 1);
    }

    function handleDecrement(menuId) {
        const item = items.find(i => i.menuId === menuId);
        if (item) updateQty(menuId, item.quantity - 1);
    }

    async function handleCheckout() {
        if (isEmpty) return;
        setErrorMsg('');

        let customer = null;
        try {
            customer = JSON.parse(sessionStorage.getItem('w9_customer') || 'null');
        } catch (_) {}

        if (!customer?.name || !customer?.phone) {
            router.visit(`/order?table=${tableId ?? ''}`);
            return;
        }

        setLoading(true);
        try {
            const res = await axios.post('/api/order', {
                customer_name:  customer.name,
                customer_phone: customer.phone,
                table_id:       customer.tableId,
                is_mahasiswa:   isMahasiswa,
                items: items.map(i => ({ menu_id: i.menuId, quantity: i.quantity })),
            });

            const { order_code } = res.data;
            router.visit(`/customer/payment/${order_code}/choose`);
        } catch (err) {
            const msg = err.response?.data?.message
                ?? err.response?.data?.errors
                ?? 'Terjadi kesalahan. Coba lagi.';
            setErrorMsg(typeof msg === 'object' ? Object.values(msg).flat().join(' ') : msg);
        } finally {
            setLoading(false);
        }
    }

    return (
        <CustomerLayout activeTab="cart">
            {/* Header */}
            <div style={{
                background: '#FFFFFF',
                padding: '0 24px',
                borderBottom: '1px solid #F0EBE5',
            }}>
                <div style={{ height: 56, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <span style={{
                        fontSize: 20, fontWeight: 700, color: '#2D2016',
                        fontFamily: '"DM Sans", system-ui, sans-serif',
                    }}>
                        Keranjang
                    </span>
                </div>
                {!isEmpty && (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, paddingBottom: 12, flexWrap: 'wrap' }}>
                        <span style={{ fontSize: 14, fontWeight: 600, color: '#8C7B6B', fontFamily: 'Outfit, system-ui' }}>
                            {count} item
                        </span>
                        <div style={{ background: '#FEF3EC', borderRadius: 14, padding: '4px 10px' }}>
                            <span style={{ fontSize: 12, fontWeight: 600, color: '#E8763A', fontFamily: 'Outfit, system-ui' }}>
                                {formatRupiah(grandTotal)}
                            </span>
                        </div>
                        {isMahasiswa && totalCashback > 0 && (
                            <div style={{
                                display: 'flex', alignItems: 'center', gap: 4,
                                background: '#ECFDF5', borderRadius: 14, padding: '4px 10px',
                            }}>
                                <span style={{ fontSize: 12, fontWeight: 600, color: '#16A34A', fontFamily: 'Outfit, system-ui' }}>
                                    Cashback {formatRupiah(totalCashback)}
                                </span>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {isEmpty ? (
                <div style={{
                    display: 'flex', flexDirection: 'column', alignItems: 'center',
                    justifyContent: 'center', padding: '60px 24px', gap: 16,
                }}>
                    <div style={{
                        width: 80, height: 80, borderRadius: '50%',
                        background: '#F5F0EB',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}>
                        <ShoppingBag size={36} color="#C4B5A5" />
                    </div>
                    <p style={{ fontSize: 16, fontWeight: 600, color: '#2D2016', margin: 0, fontFamily: '"DM Sans", system-ui' }}>
                        Keranjang Kosong
                    </p>
                    <p style={{ fontSize: 13, color: '#8C7B6B', margin: 0, textAlign: 'center', fontFamily: 'Outfit, system-ui' }}>
                        Tambahkan menu favoritmu dari halaman menu
                    </p>
                    <button
                        onClick={() => router.visit(`/customer/menu?table=${tableId ?? ''}`)}
                        style={{
                            marginTop: 8, height: 46, padding: '0 28px',
                            background: '#E8763A', color: '#FFFFFF',
                            border: 'none', borderRadius: 50,
                            fontSize: 14, fontWeight: 600, cursor: 'pointer',
                            fontFamily: 'Outfit, system-ui',
                        }}
                    >
                        Kembali ke Menu
                    </button>
                </div>
            ) : (
                <div style={{ padding: '0 24px 24px', display: 'flex', flexDirection: 'column', gap: 20 }}>


                    {/* Item list */}
                    <div style={{
                        background: '#FFFFFF',
                        borderRadius: 20,
                        border: '1px solid #EDE8E2',
                        boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
                        marginTop: 20,
                        overflow: 'hidden',
                    }}>
                        {items.map((item, idx) => {
                            const itemCashback = isMahasiswa ? (item.cashback ?? 0) : 0;
                            const effectivePrice = item.price - itemCashback;
                            return (
                                <div
                                    key={item.menuId}
                                    style={{
                                        display: 'flex', alignItems: 'center', gap: 14,
                                        padding: '16px 18px',
                                        borderBottom: idx < items.length - 1 ? '1px solid #F5F0EB' : 'none',
                                    }}
                                >
                                    {/* Info */}
                                    <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                                        <span style={{ fontSize: 15, fontWeight: 600, color: '#2D2016', fontFamily: 'Outfit, system-ui' }}>
                                            {item.name}
                                        </span>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                            {itemCashback > 0 ? (
                                                <>
                                                    <span style={{ fontSize: 12, color: '#C4B5A5', textDecoration: 'line-through', fontFamily: 'Outfit, system-ui' }}>
                                                        {formatRupiah(item.price)}
                                                    </span>
                                                    <span style={{ fontSize: 13, fontWeight: 600, color: '#16A34A', fontFamily: 'Outfit, system-ui' }}>
                                                        {formatRupiah(effectivePrice)}
                                                    </span>
                                                </>
                                            ) : (
                                                <span style={{ fontSize: 13, color: '#8C7B6B', fontFamily: 'Outfit, system-ui' }}>
                                                    {formatRupiah(item.price)}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    {/* Qty controls */}
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                                        <button
                                            onClick={() => handleDecrement(item.menuId)}
                                            style={{
                                                width: 32, height: 32, borderRadius: 12,
                                                background: '#F5F0EB', border: 'none', cursor: 'pointer',
                                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                fontSize: 16, fontWeight: 600, color: '#8C7B6B',
                                            }}
                                        >
                                            −
                                        </button>
                                        <span style={{ fontSize: 16, fontWeight: 700, color: '#2D2016', minWidth: 20, textAlign: 'center', fontFamily: 'Outfit, system-ui' }}>
                                            {item.quantity}
                                        </span>
                                        <button
                                            onClick={() => handleIncrement(item.menuId)}
                                            style={{
                                                width: 32, height: 32, borderRadius: 12,
                                                background: '#E8763A', border: 'none', cursor: 'pointer',
                                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                fontSize: 16, fontWeight: 600, color: '#FFFFFF',
                                                boxShadow: '0 2px 6px rgba(232,118,58,0.25)',
                                            }}
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Summary card */}
                    <div style={{
                        background: '#FFFFFF',
                        borderRadius: 20,
                        border: '1px solid #EDE8E2',
                        boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
                        padding: 20,
                        display: 'flex', flexDirection: 'column', gap: 12,
                    }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                            <span style={{ fontSize: 14, color: '#8C7B6B', fontFamily: 'Outfit, system-ui' }}>Subtotal</span>
                            <span style={{ fontSize: 14, fontWeight: 600, color: '#2D2016', fontFamily: 'Outfit, system-ui' }}>{formatRupiah(total)}</span>
                        </div>
                        {isMahasiswa && totalCashback > 0 && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                                    <span style={{ fontSize: 14, color: '#16A34A', fontFamily: 'Outfit, system-ui' }}>Cashback Mahasiswa</span>
                                </div>
                                <span style={{ fontSize: 14, fontWeight: 600, color: '#16A34A', fontFamily: 'Outfit, system-ui' }}>
                                    - {formatRupiah(totalCashback)}
                                </span>
                            </div>
                        )}
                        <div style={{ height: 1, background: '#F5F0EB' }} />
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <span style={{ fontSize: 18, fontWeight: 700, color: '#2D2016', fontFamily: '"DM Sans", system-ui' }}>Total</span>
                            <span style={{ fontSize: 18, fontWeight: 700, color: '#E8763A', fontFamily: '"DM Sans", system-ui' }}>{formatRupiah(grandTotal)}</span>
                        </div>
                    </div>

                    {errorMsg && (
                        <div style={{
                            background: '#FEF2F2', border: '1px solid #FECACA',
                            borderRadius: 10, padding: '10px 14px',
                            fontSize: 13, color: '#DC2626', fontFamily: 'Outfit, system-ui',
                        }}>
                            {errorMsg}
                        </div>
                    )}

                    {/* Pay button */}
                    <button
                        onClick={handleCheckout}
                        disabled={loading}
                        style={{
                            width: '100%', height: 54,
                            background: loading ? '#C4B5A5' : '#E8763A',
                            color: '#FFFFFF', border: 'none', borderRadius: 18,
                            fontSize: 16, fontWeight: 700, cursor: loading ? 'not-allowed' : 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                            boxShadow: loading ? 'none' : '0 4px 16px rgba(232,118,58,0.30)',
                            fontFamily: '"DM Sans", system-ui',
                        }}
                    >
                        {loading ? 'Memproses...' : 'Pesan'}
                    </button>
                </div>
            )}
        </CustomerLayout>
    );
}
