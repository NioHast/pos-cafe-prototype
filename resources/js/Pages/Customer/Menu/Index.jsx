import { useState, useEffect, useMemo } from 'react';
import { router } from '@inertiajs/react';
import { Search, User } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import MenuCard from '@/Components/Customer/MenuCard';
import useCart from '@/Hooks/useCart';

export default function CustomerMenu({ categories, table }) {
    const [activeCategory, setActiveCategory] = useState(null);
    const [search,         setSearch]         = useState('');
    const [customer,       setCustomer]       = useState(null);

    const { addItem, setTable } = useCart();

    /* ── Guard: cek sessionStorage, redirect ke identitas jika belum ── */
    useEffect(() => {
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (!saved) {
                const fallbackTable = table?.id ?? '';
                router.visit(fallbackTable ? `/order?table=${fallbackTable}` : '/order');
                return;
            }
            const data = JSON.parse(saved);
            if (!data.name || !data.phone) {
                sessionStorage.removeItem('w9_customer');
                const fallbackTable = table?.id ?? '';
                router.visit(fallbackTable ? `/order?table=${fallbackTable}` : '/order');
                return;
            }
            // Jika URL menyertakan ?table= tapi tidak cocok → redirect
            if (table?.id && data.tableId !== table.id) {
                sessionStorage.removeItem('w9_customer');
                router.visit(`/order?table=${table.id}`);
                return;
            }
            setCustomer(data);
            // Gunakan tableId dari session jika URL tidak menyertakan ?table=
            setTable(table?.id ?? data.tableId ?? null);
        } catch (_) {
            router.visit('/order');
        }
    }, [table?.id]);

    /* ── Flatten + filter menus ── */
    const allMenus = useMemo(
        () => categories.flatMap(c => c.menus.map(m => ({ ...m, categoryName: c.name }))),
        [categories]
    );

    const filteredMenus = useMemo(() => {
        let list = activeCategory
            ? allMenus.filter(m => m.categoryName === activeCategory)
            : allMenus;
        if (search.trim()) {
            const q = search.toLowerCase();
            list = list.filter(m => m.name.toLowerCase().includes(q));
        }
        return list;
    }, [allMenus, activeCategory, search]);

    function toggleCategory(name) {
        setActiveCategory(prev => (prev === name ? null : name));
    }

    const firstName = customer?.name?.split(' ')[0] ?? 'Tamu';

    return (
        <CustomerLayout activeTab="menu">

            {/* ── Header ── */}
            <div style={{
                background: '#FFFFFF',
                padding: '0 22px 22px',
                boxShadow: '0 2px 8px rgba(45,32,22,0.04)',
            }}>
                {/* Greeting row */}
                <div style={{
                    display: 'flex', alignItems: 'center',
                    justifyContent: 'space-between',
                    gap: 14, paddingTop: 20, marginBottom: 16,
                }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                        {/* Avatar */}
                        <div style={{
                            width: 50, height: 50, borderRadius: '50%',
                            background: '#F5F0EB',
                            border: '2.5px solid #E8763A',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            flexShrink: 0,
                        }}>
                            <User size={26} color="#B5A898" />
                        </div>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                            <span style={{ fontSize: 13, fontWeight: 500, color: '#8C7B6B' }}>
                                Selamat datang,
                            </span>
                            <span style={{
                                fontSize: 22, fontWeight: 700, color: '#2D2016',
                                fontFamily: '"DM Sans", system-ui, sans-serif',
                                lineHeight: 1.2,
                            }}>
                                {firstName}
                            </span>
                        </div>
                    </div>

                </div>

                {/* Search bar */}
                <div style={{ position: 'relative' }}>
                    <Search size={20} style={{
                        position: 'absolute', left: 20, top: '50%',
                        transform: 'translateY(-50%)', color: '#B5A898', pointerEvents: 'none',
                    }} />
                    <input
                        type="text"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        placeholder="Cari kopi, teh, snack..."
                        style={{
                            width: '100%', height: 50,
                            background: '#FFFFFF',
                            border: '1px solid #EDE8E2', borderRadius: 16,
                            padding: '0 20px 0 52px',
                            fontSize: 14, color: '#2D2016',
                            outline: 'none', boxSizing: 'border-box',
                            boxShadow: '0 2px 8px rgba(45,32,22,0.04)',
                        }}
                    />
                </div>
            </div>

            {/* ── Content ── */}
            <div style={{ padding: '0 22px 22px', display: 'flex', flexDirection: 'column', gap: 22 }}>

                {/* Kategori */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14, paddingTop: 22 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <span style={{ fontSize: 18, fontWeight: 700, color: '#2D2016', fontFamily: '"DM Sans", system-ui, sans-serif' }}>
                            Kategori
                        </span>
                        <span style={{ fontSize: 13, fontWeight: 600, color: '#E8763A', cursor: 'pointer' }} onClick={() => setActiveCategory(null)}>
                            Lihat Semua
                        </span>
                    </div>
                    {/* Horizontal scroll chips */}
                    <div style={{
                        display: 'flex', gap: 8,
                        overflowX: 'auto', paddingBottom: 4,
                        scrollbarWidth: 'none', msOverflowStyle: 'none',
                        margin: '0 -22px', padding: '0 22px 4px',
                    }}>
                        {categories.map(c => {
                            const active = activeCategory === c.name;
                            return (
                                <button
                                    key={c.id}
                                    onClick={() => toggleCategory(c.name)}
                                    style={{
                                        flexShrink: 0,
                                        background: active ? '#E8763A' : '#FFFFFF',
                                        borderRadius: 50,
                                        border: active ? 'none' : '1px solid #EDE8E2',
                                        padding: '8px 18px',
                                        fontSize: 13,
                                        fontWeight: active ? 700 : 600,
                                        color: active ? '#FFFFFF' : '#8C7B6B',
                                        fontFamily: 'Outfit, system-ui, sans-serif',
                                        cursor: 'pointer',
                                        boxShadow: active ? '0 3px 10px rgba(232,118,58,0.30)' : 'none',
                                        transition: 'all 0.15s',
                                        whiteSpace: 'nowrap',
                                    }}
                                >
                                    {c.name}
                                </button>
                            );
                        })}
                    </div>
                </div>

                {/* Menu */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                    <span style={{ fontSize: 18, fontWeight: 700, color: '#2D2016', fontFamily: '"DM Sans", system-ui, sans-serif' }}>
                        {activeCategory ? activeCategory : 'Menu Populer'}
                    </span>
                    {filteredMenus.length === 0 ? (
                        <div style={{ textAlign: 'center', color: '#B5A898', padding: '32px 0', fontSize: 14 }}>
                            Tidak ada menu ditemukan
                        </div>
                    ) : (
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 14 }}>
                            {filteredMenus.map(menu => (
                                <MenuCard key={menu.id} menu={menu} onAdd={addItem} />
                            ))}
                        </div>
                    )}
                </div>

            </div>
        </CustomerLayout>
    );
}
