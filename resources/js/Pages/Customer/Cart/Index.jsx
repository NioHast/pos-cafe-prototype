import { router } from '@inertiajs/react';
import { CreditCard } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import CartItem from '@/Components/Customer/CartItem';
import useCart from '@/Hooks/useCart';
import { formatRupiah } from '@/helpers';

export default function CustomerCart() {
    const { items, tableId, updateQty, total, count } = useCart();

    const isEmpty = items.length === 0;

    function handleIncrement(menuId) {
        const item = items.find(i => i.menuId === menuId);
        if (item) updateQty(menuId, item.quantity + 1);
    }

    function handleDecrement(menuId) {
        const item = items.find(i => i.menuId === menuId);
        if (item) updateQty(menuId, item.quantity - 1);
    }

    function handleCheckout() {
        router.post(route('customer.order.store'), {
            table_id: tableId,
            items: items.map(i => ({ menu_id: i.menuId, quantity: i.quantity })),
        });
    }

    return (
        <CustomerLayout activeTab="cart">
            <div style={{
                display: 'flex', flexDirection: 'column',
                minHeight: 'calc(100vh - 92px)',
                gap: 20, padding: '0 24px 24px',
            }}>

                {/* ── Header ── */}
                <div style={{
                    height: 56, display: 'flex',
                    alignItems: 'center', justifyContent: 'center',
                }}>
                    <span style={{
                        fontSize: 20, fontWeight: 700, color: '#2D2016',
                        fontFamily: '"DM Sans", system-ui, sans-serif',
                    }}>
                        Keranjang
                    </span>
                </div>

                {/* ── Item count + price badge ── */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <span style={{
                        fontSize: 14, fontWeight: 600, color: '#8C7B6B',
                        fontFamily: 'Outfit, system-ui, sans-serif',
                    }}>
                        {count} item
                    </span>
                    {!isEmpty && (
                        <div style={{
                            background: '#FEF3EC', borderRadius: 14,
                            padding: '4px 10px',
                        }}>
                            <span style={{
                                fontSize: 12, fontWeight: 600, color: '#E8763A',
                                fontFamily: 'Outfit, system-ui, sans-serif',
                            }}>
                                {formatRupiah(total)}
                            </span>
                        </div>
                    )}
                </div>

                {/* ── Item list card ── */}
                {isEmpty ? (
                    <div style={{
                        background: '#FFFFFF', borderRadius: 20,
                        border: '1px solid #EDE8E2',
                        padding: '48px 0', textAlign: 'center',
                        color: '#B5A898', fontSize: 14,
                        fontFamily: 'Outfit, system-ui, sans-serif',
                        boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
                    }}>
                        Keranjang masih kosong
                    </div>
                ) : (
                    <div style={{
                        background: '#FFFFFF', borderRadius: 20,
                        border: '1px solid #EDE8E2', overflow: 'hidden',
                        boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
                    }}>
                        {items.map((item, idx) => (
                            <CartItem
                                key={item.menuId}
                                item={item}
                                onIncrement={handleIncrement}
                                onDecrement={handleDecrement}
                                showDivider={idx < items.length - 1}
                            />
                        ))}
                    </div>
                )}

                {/* ── Spacer pushes summary to bottom ── */}
                <div style={{ flex: 1 }} />

                {/* ── Summary card ── */}
                {!isEmpty && (
                    <div style={{
                        background: '#FFFFFF', borderRadius: 20,
                        border: '1px solid #EDE8E2',
                        padding: 20, display: 'flex', flexDirection: 'column', gap: 12,
                        boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
                    }}>
                        {/* Subtotal */}
                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                            <span style={{
                                fontSize: 14, color: '#8C7B6B',
                                fontFamily: 'Outfit, system-ui, sans-serif',
                            }}>
                                Subtotal
                            </span>
                            <span style={{
                                fontSize: 14, fontWeight: 600, color: '#2D2016',
                                fontFamily: 'Outfit, system-ui, sans-serif',
                            }}>
                                {formatRupiah(total)}
                            </span>
                        </div>

                        {/* Diskon */}
                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                            <span style={{
                                fontSize: 14, color: '#E8763A',
                                fontFamily: 'Outfit, system-ui, sans-serif',
                            }}>
                                Diskon
                            </span>
                            <span style={{
                                fontSize: 14, fontWeight: 600, color: '#E8763A',
                                fontFamily: 'Outfit, system-ui, sans-serif',
                            }}>
                                - Rp 0
                            </span>
                        </div>

                        {/* Divider */}
                        <div style={{ height: 1, background: '#F5F0EB' }} />

                        {/* Total */}
                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                            <span style={{
                                fontSize: 18, fontWeight: 700, color: '#2D2016',
                                fontFamily: '"DM Sans", system-ui, sans-serif',
                            }}>
                                Total
                            </span>
                            <span style={{
                                fontSize: 18, fontWeight: 700, color: '#E8763A',
                                fontFamily: '"DM Sans", system-ui, sans-serif',
                            }}>
                                {formatRupiah(total)}
                            </span>
                        </div>
                    </div>
                )}

                {/* ── Pay button ── */}
                <button
                    onClick={handleCheckout}
                    disabled={isEmpty}
                    style={{
                        width: '100%', height: 54,
                        background: isEmpty ? '#F0EBE5' : '#E8763A',
                        color: isEmpty ? '#B5A898' : '#FFFFFF',
                        border: 'none', borderRadius: 18,
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        gap: 8, cursor: isEmpty ? 'not-allowed' : 'pointer',
                        fontSize: 16, fontWeight: 700,
                        fontFamily: '"DM Sans", system-ui, sans-serif',
                        boxShadow: isEmpty ? 'none' : '0 4px 16px rgba(232,118,58,0.30)',
                        transition: 'background 0.15s',
                    }}
                >
                    <CreditCard size={20} />
                    Bayar Sekarang
                </button>

            </div>
        </CustomerLayout>
    );
}
