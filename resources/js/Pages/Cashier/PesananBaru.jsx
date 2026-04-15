import { useState, useMemo, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { Search, X, Banknote, QrCode, ShieldCheck, Lock, User, CircleCheck, Clock, PanelRightClose, PanelRightOpen } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import MenuGridItem from '@/Components/Cashier/MenuGridItem';
import KeranjangItem from '@/Components/Cashier/KeranjangItem';
import { formatRupiah } from '@/helpers';

export default function PesananBaru({ categories }) {
    const [cartItems,      setCartItems]     = useState([]);
    const [activeCategory, setActiveCategory] = useState('Semua');
    const [search,         setSearch]         = useState('');
    const [showPayModal,   setShowPayModal]   = useState(false);
    const [payMethod,      setPayMethod]      = useState('cash');
    const [customerName,   setCustomerName]   = useState('');
    const [processing,     setProcessing]     = useState(false);
    const [showSuccess,    setShowSuccess]    = useState(false);
    const [successTotal,   setSuccessTotal]   = useState(0);
    const [isCartCollapsed, setIsCartCollapsed] = useState(false);
    const [isLeftSidebarCollapsed, setIsLeftSidebarCollapsed] = useState(() => {
        if (typeof window === 'undefined') return false;
        return window.localStorage.getItem('cashier-sidebar-collapsed') === 'true';
    });
    const [viewport, setViewport] = useState(() => {
        if (typeof window === 'undefined') return { width: 1280, height: 720 };
        return { width: window.innerWidth, height: window.innerHeight };
    });

    /* ── Derived ── */
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

    const [isMahasiswa, setIsMahasiswa] = useState(false);

    const totalQty      = cartItems.reduce((s, i) => s + i.quantity, 0);
    const total         = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
    const totalCashback = isMahasiswa ? cartItems.reduce((s, i) => s + (i.cashback ?? 0) * i.quantity, 0) : 0;
    const grandTotal    = total - totalCashback;
    const isPortrait    = viewport.height > viewport.width;

    const cartExpandedWidth = isLeftSidebarCollapsed ? 380 : 340;
    const cartPanelWidth = isCartCollapsed ? 78 : cartExpandedWidth;
    const menuGridColumns = isPortrait
        ? 'repeat(auto-fill, minmax(170px, 1fr))'
        : isCartCollapsed
            ? 'repeat(auto-fill, minmax(185px, 1fr))'
            : 'repeat(auto-fill, minmax(210px, 1fr))';

    useEffect(() => {
        const onResize = () => setViewport({ width: window.innerWidth, height: window.innerHeight });
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);

    useEffect(() => {
        if (!isPortrait) return;
        setIsCartCollapsed(true);
    }, [isPortrait]);

    useEffect(() => {
        const onSidebarToggle = (event) => {
            setIsLeftSidebarCollapsed(Boolean(event.detail?.collapsed));
        };

        window.addEventListener('cashier-sidebar-toggle', onSidebarToggle);
        return () => window.removeEventListener('cashier-sidebar-toggle', onSidebarToggle);
    }, []);

    /* ── Cart actions ── */
    function addToCart(menu) {
        setCartItems(prev => {
            const existing = prev.find(i => i.menuId === menu.id);
            if (existing) return prev.map(i => i.menuId === menu.id ? { ...i, quantity: i.quantity + 1 } : i);
            return [...prev, { menuId: menu.id, name: menu.name, price: Number(menu.price), cashback: Number(menu.cashback ?? 0), quantity: 1 }];
        });
    }
    const increment = (menuId) => setCartItems(prev => prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity + 1 } : i));
    const decrement = (menuId) => setCartItems(prev => prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity - 1 } : i).filter(i => i.quantity > 0));

    /* ── Modal open/close ── */
    function openModal() {
        setPayMethod('cash');
        setShowPayModal(true);
    }
    function closeModal() {
        if (processing) return;
        setShowPayModal(false);
        setCustomerName('');
    }

    /* ── Step 1: proceed from choose ── */
    function handleChooseProceed() {
        submitOrder(payMethod);
    }

    /* ── Submit ── */
    function submitOrder(method) {
        const orderTotal = grandTotal;
        setProcessing(true);
        router.post(
            '/cashier/pesanan-baru',
            { items: cartItems.map(i => ({ menu_id: i.menuId, quantity: i.quantity })), payment_method: method, customer_name: customerName.trim() || null, is_mahasiswa: isMahasiswa },
            {
                onSuccess: () => {
                    setProcessing(false);
                    setShowPayModal(false);
                    setSuccessTotal(orderTotal);
                    setShowSuccess(true);
                },
                onError: () => setProcessing(false),
            }
        );
    }

    function handleSuccessOk() {
        setShowSuccess(false);
        setCartItems([]);
        setCustomerName('');
        setPayMethod('cash');
    }

    /* ── Design tokens ── */
    const T = {
        accent:   '#3B6FD4',
        accentBg: '#EFF6FF',
        accentRing: '#BFDBFE',
        text:     '#0F172A',
        sub:      '#64748B',
        border:   '#E2E8F0',
        elevated: '#F1F5F9',
        surface:  '#FFFFFF',
    };

    return (
        <CashierLayout title="Pesanan Baru" fullscreen>
            <div style={{ display: 'flex', flexDirection: isPortrait ? 'column' : 'row', height: '100vh', overflow: 'hidden' }}>

                {/* ══ PANEL TENGAH ══ */}
                <div
                    style={{
                        flex: 1,
                        padding: isPortrait ? 14 : 24,
                        background: '#F8FAFC',
                        overflowY: 'auto',
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 16,
                        height: isPortrait ? 'auto' : '100vh',
                        minHeight: 0,
                    }}
                >
                    {/* Search */}
                    <div style={{ position: 'relative' }}>
                        <Search size={18} style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', color: '#94A3B8', pointerEvents: 'none' }} />
                        <input
                            type="text" value={search} onChange={e => setSearch(e.target.value)}
                            placeholder="Cari menu..."
                            style={{ width: '100%', height: 44, border: `1px solid ${T.border}`, borderRadius: 8, padding: '0 40px 0 44px', fontSize: 14, color: T.text, background: T.surface, outline: 'none', boxSizing: 'border-box', boxShadow: '0 2px 8px rgba(15,23,42,0.04)' }}
                        />
                        {search && (
                            <button onClick={() => setSearch('')} style={{ position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', cursor: 'pointer', color: '#94A3B8', padding: 0, display: 'flex' }}>
                                <X size={16} />
                            </button>
                        )}
                    </div>

                    {/* Category chips */}
                    <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 4, flexShrink: 0 }}>
                        {['Semua', ...categories.map(c => c.name)].map(cat => {
                            const active = activeCategory === cat;
                            return (
                                <button key={cat} onClick={() => setActiveCategory(cat)} style={{ height: 36, padding: '0 16px', borderRadius: 100, border: 'none', cursor: 'pointer', fontSize: 13, fontWeight: active ? 600 : 500, background: active ? T.accent : T.elevated, color: active ? '#FFFFFF' : T.sub, whiteSpace: 'nowrap', transition: 'background 0.15s, color 0.15s', flexShrink: 0 }}>
                                    {cat}
                                </button>
                            );
                        })}
                    </div>

                    {/* Menu grid */}
                    {filteredMenus.length === 0 ? (
                        <div style={{ textAlign: 'center', color: '#94A3B8', paddingTop: 48, fontSize: 14 }}>Tidak ada menu ditemukan</div>
                    ) : (
                        <div style={{ display: 'grid', gridTemplateColumns: menuGridColumns, gap: isPortrait ? 10 : 16 }}>
                            {filteredMenus.map(menu => <MenuGridItem key={menu.id} menu={menu} onAdd={addToCart} />)}
                        </div>
                    )}
                </div>

                {/* ══ PANEL KANAN — Keranjang ══ */}
                <div
                    style={{
                        width: isPortrait ? '100%' : cartPanelWidth,
                        background: T.surface,
                        borderLeft: isPortrait ? 'none' : `1px solid ${T.border}`,
                        borderTop: isPortrait ? `1px solid ${T.border}` : 'none',
                        padding: isCartCollapsed ? '14px 12px' : (isPortrait ? '14px 16px 16px' : 24),
                        display: 'flex',
                        flexDirection: 'column',
                        flexShrink: 0,
                        height: isPortrait ? (isCartCollapsed ? 72 : '44vh') : '100vh',
                        overflowY: 'auto',
                        overflowX: 'hidden',
                        transition: 'width 0.2s ease, height 0.2s ease, padding 0.2s ease',
                    }}
                >
                    <div style={{ display: 'flex', justifyContent: isCartCollapsed ? 'center' : 'space-between', alignItems: 'center', marginBottom: isCartCollapsed ? 0 : 16, gap: 10 }}>
                        {!isCartCollapsed && <span style={{ fontSize: 16, fontWeight: 700, color: T.text, letterSpacing: '-0.2px' }}>Keranjang Pesanan</span>}
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                            <span style={{ background: T.accent, color: 'white', borderRadius: '50%', width: 28, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12, fontWeight: 700 }}>{totalQty}</span>
                            <button
                                type="button"
                                onClick={() => setIsCartCollapsed(prev => !prev)}
                                title={isCartCollapsed ? 'Expand keranjang' : 'Collapse keranjang'}
                                style={{
                                    width: 32,
                                    height: 32,
                                    borderRadius: 8,
                                    border: `1px solid ${T.border}`,
                                    background: '#F8FAFC',
                                    color: '#334155',
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    cursor: 'pointer',
                                }}
                            >
                                {isCartCollapsed ? <PanelRightOpen size={16} /> : <PanelRightClose size={16} />}
                            </button>
                        </div>
                    </div>
                    {!isCartCollapsed && (
                        <>
                            <div style={{ flex: 1, overflowY: 'auto' }}>
                                {cartItems.length === 0 ? (
                                    <p style={{ color: '#94A3B8', textAlign: 'center', marginTop: 40, fontSize: 14 }}>Keranjang kosong</p>
                                ) : (
                                    cartItems.map(item => <KeranjangItem key={item.menuId} item={item} onIncrement={increment} onDecrement={decrement} />)
                                )}
                            </div>
                            <div style={{ borderTop: `1px solid ${T.border}`, paddingTop: 16, marginTop: 'auto' }}>
                        {/* Toggle Mahasiswa */}
                        <div
                            onClick={() => setIsMahasiswa(p => !p)}
                            style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12, cursor: 'pointer', userSelect: 'none' }}
                        >
                            <div style={{
                                width: 16, height: 16, borderRadius: 4, flexShrink: 0,
                                border: `1.5px solid ${isMahasiswa ? T.accent : '#CBD5E1'}`,
                                background: isMahasiswa ? T.accent : 'white',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                {isMahasiswa && <span style={{ color: 'white', fontSize: 11, lineHeight: 1 }}>✓</span>}
                            </div>
                            <span style={{ fontSize: 13, color: T.sub }}>Mahasiswa STIE Totalwin Semarang</span>
                        </div>

                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                            <span style={{ fontSize: 14, color: T.sub }}>Subtotal</span>
                            <span style={{ fontSize: 14, fontWeight: 500, color: T.text }}>{formatRupiah(total)}</span>
                        </div>
                        {isMahasiswa && totalCashback > 0 && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                                <span style={{ fontSize: 13, color: '#16A34A' }}>Cashback Mahasiswa</span>
                                <span style={{ fontSize: 13, fontWeight: 600, color: '#16A34A' }}>- {formatRupiah(totalCashback)}</span>
                            </div>
                        )}
                        <div style={{ display: 'flex', justifyContent: 'space-between', padding: '12px 0', borderTop: `1px solid ${T.border}`, marginBottom: 16 }}>
                            <span style={{ fontSize: 16, fontWeight: 700, color: T.text }}>Total</span>
                            <span style={{ fontSize: 16, fontWeight: 700, color: T.text }}>{formatRupiah(grandTotal)}</span>
                        </div>
                        <button
                            onClick={openModal} disabled={cartItems.length === 0}
                            style={{ width: '100%', height: 52, background: cartItems.length === 0 ? '#CBD5E1' : T.accent, color: 'white', border: 'none', borderRadius: 14, fontSize: 16, fontWeight: 700, cursor: cartItems.length === 0 ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 20px', boxShadow: cartItems.length > 0 ? '0 4px 16px rgba(59,111,212,0.30)' : 'none', transition: 'background 0.15s' }}
                        >
                            <span>BAYAR</span>
                            <span style={{ fontSize: 18 }}>{formatRupiah(grandTotal)}</span>
                        </button>
                            </div>
                        </>
                    )}
                </div>
            </div>

            {/* ══ MODAL ══ */}
            {showPayModal && (
                <div
                    style={{ position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.55)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: 24 }}
                    onClick={e => { if (e.target === e.currentTarget) closeModal(); }}
                >
                    <div style={{ background: T.surface, borderRadius: 24, width: '100%', maxWidth: 440, boxShadow: '0 24px 64px rgba(15,23,42,0.18), 0 2px 8px rgba(15,23,42,0.06)', overflow: 'hidden' }}>

                        {/* ─── Pilih Cara Bayar ─── */}
                        {(<>
                                {/* Header */}
                                <div style={{ padding: '22px 24px 16px', borderBottom: '1px solid #E2E8F0' }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                                        <button onClick={closeModal} style={{ width: 36, height: 36, borderRadius: 12, background: '#F1F5F9', border: 'none', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                            <X size={20} color="#0F172A" />
                                        </button>
                                        <div style={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
                                            <span style={{ fontSize: 20, fontWeight: 700, color: '#0F172A', fontFamily: '"DM Sans", system-ui', lineHeight: 1.2 }}>Metode Pembayaran</span>
                                            <span style={{ fontSize: 12, color: '#64748B', fontFamily: 'Outfit, system-ui' }}>{cartItems.length} item · {formatRupiah(total)}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Body */}
                                <div style={{ padding: '0 24px 24px', display: 'flex', flexDirection: 'column', gap: 18, maxHeight: '68vh', overflowY: 'auto' }}>

                                    {/* Nama Pelanggan */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6, paddingTop: 8, borderTop: '1px solid #E2E8F0', marginTop: 4 }}>
                                        <label style={{ fontSize: 13, fontWeight: 500, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>Nama Pelanggan</label>
                                        <div style={{ position: 'relative' }}>
                                            <User size={18} style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', color: '#94A3B8', pointerEvents: 'none' }} />
                                            <input
                                                type="text"
                                                value={customerName}
                                                onChange={e => setCustomerName(e.target.value)}
                                                placeholder="Masukkan nama pelanggan..."
                                                style={{ width: '100%', height: 44, border: '1px solid #E2E8F0', borderRadius: 12, padding: '0 14px 0 42px', fontSize: 14, color: '#0F172A', background: '#FFFFFF', outline: 'none', boxSizing: 'border-box', fontFamily: 'Outfit, system-ui', transition: 'border-color 0.15s' }}
                                                onFocus={e => e.target.style.borderColor = '#3B6FD4'}
                                                onBlur={e => e.target.style.borderColor = '#E2E8F0'}
                                            />
                                        </div>
                                    </div>

                                    {/* Metode Pembayaran */}
                                    <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: '#64748B', fontFamily: 'Outfit, system-ui', letterSpacing: 0.5 }}>Metode Pembayaran</p>

                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: -8 }}>
                                        {/* Cash */}
                                        <button
                                            onClick={() => setPayMethod('cash')}
                                            style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px 16px 16px', borderRadius: 16, cursor: 'pointer', textAlign: 'left', border: payMethod === 'cash' ? '2px solid #3B6FD4' : '1px solid #E2E8F0', background: payMethod === 'cash' ? '#EFF6FF' : '#FFFFFF', boxShadow: payMethod === 'cash' ? '0 2px 10px rgba(59,111,212,0.10)' : 'none', transition: 'all 0.15s' }}
                                        >
                                            <div style={{ width: 42, height: 42, borderRadius: 12, background: payMethod === 'cash' ? '#DBEAFE' : '#F1F5F9', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <Banknote size={22} color={payMethod === 'cash' ? '#3B6FD4' : '#64748B'} />
                                            </div>
                                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                                                <span style={{ fontSize: 15, fontWeight: 600, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>Bayar ke Kasir (Cash)</span>
                                                <span style={{ fontSize: 12, color: '#64748B', fontFamily: 'Outfit, system-ui' }}>Bayar langsung di kasir setelah pesanan dikonfirmasi</span>
                                            </div>
                                            <div style={{ width: 22, height: 22, borderRadius: '50%', border: payMethod === 'cash' ? '2px solid #3B6FD4' : '1.5px solid #CBD5E1', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                {payMethod === 'cash' && <div style={{ width: 12, height: 12, borderRadius: '50%', background: '#3B6FD4' }} />}
                                            </div>
                                        </button>

                                        {/* QRIS */}
                                        <button
                                            onClick={() => setPayMethod('qris')}
                                            style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px 16px 16px', borderRadius: 16, cursor: 'pointer', textAlign: 'left', border: payMethod === 'qris' ? '2px solid #3B6FD4' : '1px solid #E2E8F0', background: payMethod === 'qris' ? '#EFF6FF' : '#FFFFFF', boxShadow: payMethod === 'qris' ? '0 2px 10px rgba(59,111,212,0.10)' : 'none', transition: 'all 0.15s' }}
                                        >
                                            <div style={{ width: 42, height: 42, borderRadius: 12, background: payMethod === 'qris' ? '#DBEAFE' : '#F1F5F9', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <img src="/images/logo-qris.png" alt="QRIS" style={{ width: 28, height: 28, objectFit: 'contain' }} />
                                            </div>
                                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                                                <span style={{ fontSize: 15, fontWeight: 600, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>QRIS</span>
                                                <span style={{ fontSize: 12, color: '#94A3B8', fontFamily: 'Outfit, system-ui' }}>Scan QR code & konfirmasi kasir</span>
                                            </div>
                                            <div style={{ width: 22, height: 22, borderRadius: '50%', border: payMethod === 'qris' ? '2px solid #3B6FD4' : '1.5px solid #CBD5E1', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                {payMethod === 'qris' && <div style={{ width: 12, height: 12, borderRadius: '50%', background: '#3B6FD4' }} />}
                                            </div>
                                        </button>

                                        {/* Bayar Nanti */}
                                        <button
                                            onClick={() => setPayMethod('bayar_nanti')}
                                            style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px 16px 16px', borderRadius: 16, cursor: 'pointer', textAlign: 'left', border: payMethod === 'bayar_nanti' ? '2px solid #3B6FD4' : '1px solid #E2E8F0', background: payMethod === 'bayar_nanti' ? '#EFF6FF' : '#FFFFFF', boxShadow: payMethod === 'bayar_nanti' ? '0 2px 10px rgba(59,111,212,0.10)' : 'none', transition: 'all 0.15s' }}
                                        >
                                            <div style={{ width: 42, height: 42, borderRadius: 12, background: payMethod === 'bayar_nanti' ? '#DBEAFE' : '#F1F5F9', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <Clock size={22} color={payMethod === 'bayar_nanti' ? '#3B6FD4' : '#64748B'} />
                                            </div>
                                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                                                <span style={{ fontSize: 15, fontWeight: 600, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>Bayar Nanti</span>
                                                <span style={{ fontSize: 12, color: '#94A3B8', fontFamily: 'Outfit, system-ui' }}>Simpan pesanan, pelanggan bayar nanti</span>
                                            </div>
                                            <div style={{ width: 22, height: 22, borderRadius: '50%', border: payMethod === 'bayar_nanti' ? '2px solid #3B6FD4' : '1.5px solid #CBD5E1', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                {payMethod === 'bayar_nanti' && <div style={{ width: 12, height: 12, borderRadius: '50%', background: '#3B6FD4' }} />}
                                            </div>
                                        </button>
                                    </div>

                                    {/* Security note */}
                                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: 5, padding: '0 0 4px' }}>
                                        <Lock size={11} color="#94A3B8" />
                                        <span style={{ fontSize: 11, color: '#94A3B8', fontFamily: 'Outfit, system-ui' }}></span>
                                    </div>
                                </div>

                                {/* CTA */}
                                <div style={{ padding: '0 24px 24px' }}>
                                    <button
                                        onClick={handleChooseProceed} disabled={processing}
                                        style={{ width: '100%', height: 54, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, background: processing ? '#93AEDF' : '#3B6FD4', color: '#FFFFFF', border: 'none', borderRadius: 14, fontSize: 16, fontWeight: 700, fontFamily: '"DM Sans", system-ui', cursor: processing ? 'not-allowed' : 'pointer', boxShadow: '0 4px 16px rgba(59,111,212,0.28)' }}
                                    >
                                        <ShieldCheck size={20} color="#FFFFFF" />
                                        {processing ? 'Memproses...' : 'Konfirmasi Pembayaran'}
                                    </button>
                                </div>
                            </>
                        )}

                    </div>
                </div>
            )}
            {/* ══ SUCCESS POPUP (3c Pencil — blue kasir theme) ══ */}
            {showSuccess && (
                <div style={{
                    position: 'fixed', inset: 0,
                    background: 'rgba(0,0,0,0.50)',
                    zIndex: 2000,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                    {/* toastPopup — bg #FFFFFF, r:24, w:300, p:[28,24,24,24], gap:16, shadow */}
                    <div style={{
                        background: '#FFFFFF',
                        borderRadius: 24,
                        width: 320,
                        padding: '28px 24px 24px',
                        display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 16,
                        boxShadow: '0 8px 30px rgba(15,23,42,0.18)',
                    }}>
                        {/* iconCircle — 72x72, r:36, bg #EFF6FF (blue adapted), icon check green */}
                        <div style={{
                            width: 72, height: 72, borderRadius: 36,
                            background: '#F0FDF4',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                        }}>
                            <CircleCheck size={40} color="#22C55E" strokeWidth={2} />
                        </div>

                        {/* titleTxt — DM Sans 18/700 #0F172A */}
                        <span style={{ fontSize: 18, fontWeight: 700, color: '#0F172A', fontFamily: '"DM Sans", system-ui', textAlign: 'center' }}>
                            Pesanan Diterima!
                        </span>

                        {/* descTxt — Outfit 13 #64748B */}
                        <span style={{ fontSize: 13, color: '#64748B', fontFamily: 'Outfit, system-ui', textAlign: 'center', lineHeight: 1.5, width: '100%' }}>
                            Pesanan berhasil dibuat dan siap diproses.
                        </span>

                        {/* amountWrap — bg #EFF6FF, r:14, p:[12,16] */}
                        <div style={{
                            background: '#EFF6FF', borderRadius: 14,
                            width: '100%', padding: '12px 16px',
                            display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2,
                        }}>
                            <span style={{ fontSize: 11, fontWeight: 500, color: '#94A3B8', fontFamily: 'Outfit, system-ui' }}>
                                Total Pembayaran
                            </span>
                            <span style={{ fontSize: 22, fontWeight: 700, color: '#3B6FD4', fontFamily: '"DM Sans", system-ui' }}>
                                {formatRupiah(successTotal)}
                            </span>
                        </div>

                        {/* okBtn — bg #3B6FD4, r:14, h:46, DM Sans 15/700 white */}
                        <button
                            onClick={handleSuccessOk}
                            style={{
                                width: '100%', height: 46,
                                background: '#3B6FD4', color: '#FFFFFF',
                                border: 'none', borderRadius: 14,
                                fontSize: 15, fontWeight: 700, fontFamily: '"DM Sans", system-ui',
                                cursor: 'pointer',
                                boxShadow: '0 4px 14px rgba(59,111,212,0.30)',
                            }}
                        >
                            OK
                        </button>
                    </div>
                </div>
            )}
        </CashierLayout>
    );
}
