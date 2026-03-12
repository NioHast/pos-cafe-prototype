import { useState, useMemo } from 'react';
import { router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import MenuGridItem from '@/Components/Cashier/MenuGridItem';
import KeranjangItem from '@/Components/Cashier/KeranjangItem';
import { formatRupiah } from '@/helpers';

const METHOD_LABELS = {
    cash:     'Tunai',
    qris:     'QRIS',
    ewallet:  'E-Wallet',
    transfer: 'Transfer Bank',
};

export default function PesananBaru({ categories }) {
    const [cartItems,      setCartItems]     = useState([]);
    const [activeCategory, setActiveCategory] = useState('Semua');
    const [search,         setSearch]         = useState('');
    const [showPayModal,   setShowPayModal]   = useState(false);
    const [payMethod,      setPayMethod]      = useState('cash');
    const [processing,     setProcessing]     = useState(false);

    /* ── Helpers ── */
    const allMenus = useMemo(
        () => categories.flatMap(c => c.menus.map(m => ({ ...m, category: { name: c.name } }))),
        [categories]
    );

    const filteredMenus = useMemo(() => {
        let menus = activeCategory === 'Semua'
            ? allMenus
            : allMenus.filter(m => m.category.name === activeCategory);
        if (search.trim()) {
            const q = search.toLowerCase();
            menus = menus.filter(m => m.name.toLowerCase().includes(q));
        }
        return menus;
    }, [allMenus, activeCategory, search]);

    const totalQty  = cartItems.reduce((s, i) => s + i.quantity, 0);
    const total     = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);

    /* ── Cart actions ── */
    function addToCart(menu) {
        setCartItems(prev => {
            const existing = prev.find(i => i.menuId === menu.id);
            if (existing) return prev.map(i => i.menuId === menu.id ? { ...i, quantity: i.quantity + 1 } : i);
            return [...prev, { menuId: menu.id, name: menu.name, price: Number(menu.price), quantity: 1 }];
        });
    }

    function increment(menuId) {
        setCartItems(prev => prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity + 1 } : i));
    }

    function decrement(menuId) {
        setCartItems(prev => {
            const updated = prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity - 1 } : i);
            return updated.filter(i => i.quantity > 0);
        });
    }

    /* ── Submit ── */
    function handleSubmitOrder() {
        setProcessing(true);
        router.post(
            route('cashier.pesanan-baru.store'),
            {
                items: cartItems.map(i => ({ menu_id: i.menuId, quantity: i.quantity })),
                payment_method: payMethod,
            },
            {
                onSuccess: () => {
                    setCartItems([]);
                    setShowPayModal(false);
                    setProcessing(false);
                },
                onError: () => setProcessing(false),
            }
        );
    }

    return (
        <CashierLayout title="Pesanan Baru">
            {/* Override white card padding — negatif margin trick */}
            <div style={{ margin: '-24px', display: 'flex', minHeight: 'calc(100vh - 96px)' }}>

                {/* ══ PANEL TENGAH ══ */}
                <div style={{
                    flex: 1,
                    padding: 24,
                    background: '#F8FAFC',
                    overflowY: 'auto',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 16,
                }}>
                    {/* Search */}
                    <div style={{ position: 'relative' }}>
                        <Search size={18} style={{
                            position: 'absolute', left: 14, top: '50%',
                            transform: 'translateY(-50%)', color: '#94A3B8', pointerEvents: 'none',
                        }} />
                        <input
                            type="text"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Cari menu..."
                            style={{
                                width: '100%', height: 44,
                                border: '1px solid #E2E8F0', borderRadius: 8,
                                padding: '0 40px 0 44px',
                                fontSize: 14, color: '#0F172A',
                                background: '#FFFFFF',
                                outline: 'none', boxSizing: 'border-box',
                                boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
                            }}
                        />
                        {search && (
                            <button
                                onClick={() => setSearch('')}
                                style={{
                                    position: 'absolute', right: 12, top: '50%',
                                    transform: 'translateY(-50%)',
                                    background: 'none', border: 'none', cursor: 'pointer',
                                    color: '#94A3B8', padding: 0, display: 'flex',
                                }}
                            >
                                <X size={16} />
                            </button>
                        )}
                    </div>

                    {/* Category chips */}
                    <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 4, flexShrink: 0 }}>
                        {['Semua', ...categories.map(c => c.name)].map(cat => {
                            const active = activeCategory === cat;
                            return (
                                <button
                                    key={cat}
                                    onClick={() => setActiveCategory(cat)}
                                    style={{
                                        height: 36,
                                        padding: '0 16px',
                                        borderRadius: 100,
                                        border: 'none',
                                        cursor: 'pointer',
                                        fontSize: 13,
                                        fontWeight: active ? 600 : 500,
                                        background: active ? '#3B6FD4' : '#E2E8F0',
                                        color: active ? '#FFFFFF' : '#64748B',
                                        whiteSpace: 'nowrap',
                                        transition: 'background 0.15s, color 0.15s',
                                        flexShrink: 0,
                                    }}
                                >
                                    {cat}
                                </button>
                            );
                        })}
                    </div>

                    {/* Menu grid */}
                    {filteredMenus.length === 0 ? (
                        <div style={{ textAlign: 'center', color: '#94A3B8', paddingTop: 48, fontSize: 14 }}>
                            Tidak ada menu ditemukan
                        </div>
                    ) : (
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 16 }}>
                            {filteredMenus.map(menu => (
                                <MenuGridItem key={menu.id} menu={menu} onAdd={addToCart} />
                            ))}
                        </div>
                    )}
                </div>

                {/* ══ PANEL KANAN — Keranjang ══ */}
                <div style={{
                    width: 380,
                    background: '#FFFFFF',
                    borderLeft: '1px solid #E2E8F0',
                    padding: 24,
                    display: 'flex',
                    flexDirection: 'column',
                    flexShrink: 0,
                }}>
                    {/* Cart header */}
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                        <span style={{ fontSize: 16, fontWeight: 700, color: '#0F172A', letterSpacing: '-0.2px' }}>
                            Keranjang Pesanan
                        </span>
                        <span style={{
                            background: '#3B6FD4', color: 'white',
                            borderRadius: '50%', width: 28, height: 28,
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            fontSize: 12, fontWeight: 700,
                        }}>
                            {totalQty}
                        </span>
                    </div>

                    {/* Cart items */}
                    <div style={{ flex: 1, overflowY: 'auto' }}>
                        {cartItems.length === 0 ? (
                            <p style={{ color: '#94A3B8', textAlign: 'center', marginTop: 40, fontSize: 14 }}>
                                Keranjang kosong
                            </p>
                        ) : (
                            cartItems.map(item => (
                                <KeranjangItem
                                    key={item.menuId}
                                    item={item}
                                    onIncrement={increment}
                                    onDecrement={decrement}
                                />
                            ))
                        )}
                    </div>

                    {/* Cart footer */}
                    <div style={{ borderTop: '1px solid #E2E8F0', paddingTop: 16, marginTop: 'auto' }}>
                        {/* Subtotal */}
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                            <span style={{ fontSize: 14, color: '#64748B' }}>Subtotal</span>
                            <span style={{ fontSize: 14, fontWeight: 500, color: '#0F172A' }}>{formatRupiah(total)}</span>
                        </div>
                        {/* Total */}
                        <div style={{
                            display: 'flex', justifyContent: 'space-between',
                            padding: '12px 0', borderTop: '1px solid #E2E8F0', marginBottom: 16,
                        }}>
                            <span style={{ fontSize: 16, fontWeight: 700, color: '#0F172A' }}>Total</span>
                            <span style={{ fontSize: 16, fontWeight: 700, color: '#0F172A' }}>{formatRupiah(total)}</span>
                        </div>
                        {/* Pay button */}
                        <button
                            onClick={() => setShowPayModal(true)}
                            disabled={cartItems.length === 0}
                            style={{
                                width: '100%', height: 52,
                                background: cartItems.length === 0 ? '#CBD5E1' : '#3B6FD4',
                                color: 'white', border: 'none', borderRadius: 14,
                                fontSize: 16, fontWeight: 700, cursor: cartItems.length === 0 ? 'not-allowed' : 'pointer',
                                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                                padding: '0 20px',
                                boxShadow: cartItems.length > 0 ? '0 4px 16px rgba(59,111,212,0.30)' : 'none',
                                transition: 'background 0.15s',
                            }}
                        >
                            <span>BAYAR</span>
                            <span style={{ fontSize: 18 }}>{formatRupiah(total)}</span>
                        </button>
                    </div>
                </div>
            </div>

            {/* ══ MODAL BAYAR ══ */}
            {showPayModal && (
                <div
                    style={{
                        position: 'fixed', inset: 0,
                        background: 'rgba(15,23,42,0.5)',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        zIndex: 1000,
                    }}
                    onClick={e => { if (e.target === e.currentTarget) setShowPayModal(false); }}
                >
                    <div style={{
                        background: 'white', borderRadius: 16, padding: 28,
                        width: 360, boxShadow: '0 20px 60px rgba(0,0,0,0.20)',
                    }}>
                        <h3 style={{ fontSize: 18, fontWeight: 700, color: '#0F172A', margin: '0 0 6px' }}>
                            Pilih Metode Pembayaran
                        </h3>
                        <p style={{ fontSize: 14, color: '#64748B', margin: '0 0 20px' }}>
                            Total: <strong style={{ color: '#3B6FD4' }}>{formatRupiah(total)}</strong>
                        </p>

                        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 24 }}>
                            {Object.entries(METHOD_LABELS).map(([value, label]) => (
                                <label
                                    key={value}
                                    style={{
                                        display: 'flex', alignItems: 'center', gap: 12,
                                        padding: '12px 16px', borderRadius: 10, cursor: 'pointer',
                                        border: `1.5px solid ${payMethod === value ? '#3B6FD4' : '#E2E8F0'}`,
                                        background: payMethod === value ? '#EFF6FF' : '#FFFFFF',
                                        transition: 'border-color 0.15s, background 0.15s',
                                    }}
                                >
                                    <input
                                        type="radio" value={value}
                                        checked={payMethod === value}
                                        onChange={() => setPayMethod(value)}
                                        style={{ accentColor: '#3B6FD4', width: 16, height: 16 }}
                                    />
                                    <span style={{
                                        fontSize: 14, fontWeight: payMethod === value ? 600 : 400,
                                        color: payMethod === value ? '#3B6FD4' : '#0F172A',
                                    }}>
                                        {label}
                                    </span>
                                </label>
                            ))}
                        </div>

                        <div style={{ display: 'flex', gap: 10 }}>
                            <button
                                onClick={() => setShowPayModal(false)}
                                style={{
                                    flex: 1, height: 44,
                                    background: '#F1F5F9', color: '#64748B',
                                    border: 'none', borderRadius: 10, fontSize: 14, fontWeight: 500, cursor: 'pointer',
                                }}
                            >
                                Batal
                            </button>
                            <button
                                onClick={handleSubmitOrder}
                                disabled={processing}
                                style={{
                                    flex: 2, height: 44,
                                    background: processing ? '#93AEDF' : '#3B6FD4',
                                    color: 'white', border: 'none', borderRadius: 10,
                                    fontSize: 14, fontWeight: 600, cursor: processing ? 'not-allowed' : 'pointer',
                                    boxShadow: '0 4px 12px rgba(59,111,212,0.25)',
                                }}
                            >
                                {processing ? 'Memproses...' : 'Konfirmasi Pesanan'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </CashierLayout>
    );
}
