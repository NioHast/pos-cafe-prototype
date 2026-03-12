import { useState, useEffect, useMemo } from 'react';
import { router } from '@inertiajs/react';
import { Search, User, ShoppingCart } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import CategoryChip from '@/Components/Customer/CategoryChip';
import MenuCard from '@/Components/Customer/MenuCard';
import useCart from '@/Hooks/useCart';

export default function CustomerMenu({ categories, table }) {
    const [activeCategory, setActiveCategory] = useState(null);
    const [search,         setSearch]         = useState('');

    const { addItem, setTable, totalQty } = useCart();

    /* ── Persist table id into cart store on mount ── */
    useEffect(() => {
        setTable(table?.id ?? null);
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

    function goToCart() {
        router.visit(route('customer.cart'));
    }

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
                            <span style={{ fontSize: 13, fontWeight: 500, color: '#8C7B6B' }}>Hello Guest</span>
                            <span style={{
                                fontSize: 22, fontWeight: 700, color: '#2D2016',
                                fontFamily: '"DM Sans", system-ui, sans-serif',
                                lineHeight: 1.2,
                            }}>
                                selamat Datang
                            </span>
                        </div>
                    </div>

                    {/* Cart badge */}
                    {totalQty > 0 && (
                        <button
                            onClick={goToCart}
                            style={{
                                position: 'relative', background: '#E8763A',
                                border: 'none', borderRadius: 14,
                                width: 44, height: 44, cursor: 'pointer',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                flexShrink: 0,
                            }}
                        >
                            <ShoppingCart size={22} color="#FFFFFF" />
                            <span style={{
                                position: 'absolute', top: -4, right: -4,
                                background: '#2D2016', color: '#FFFFFF',
                                borderRadius: '50%', width: 18, height: 18,
                                fontSize: 10, fontWeight: 700,
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                {totalQty}
                            </span>
                        </button>
                    )}
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

                {/* ── Kategori ── */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14, paddingTop: 22 }}>
                    <div style={{
                        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                    }}>
                        <span style={{
                            fontSize: 18, fontWeight: 700, color: '#2D2016',
                            fontFamily: '"DM Sans", system-ui, sans-serif',
                        }}>
                            Kategori
                        </span>
                        <span
                            style={{ fontSize: 13, fontWeight: 600, color: '#E8763A', cursor: 'pointer' }}
                            onClick={() => setActiveCategory(null)}
                        >
                            Lihat Semua
                        </span>
                    </div>

                    <div style={{
                        display: 'flex', gap: 10,
                        overflowX: 'auto', paddingBottom: 4,
                    }}>
                        {categories.map(c => (
                            <CategoryChip
                                key={c.id}
                                label={c.name}
                                active={activeCategory === c.name}
                                onClick={() => toggleCategory(c.name)}
                            />
                        ))}
                    </div>
                </div>

                {/* ── Menu Populer ── */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                    <span style={{
                        fontSize: 18, fontWeight: 700, color: '#2D2016',
                        fontFamily: '"DM Sans", system-ui, sans-serif',
                    }}>
                        {activeCategory ? activeCategory : 'Menu Populer'}
                    </span>

                    {filteredMenus.length === 0 ? (
                        <div style={{
                            textAlign: 'center', color: '#B5A898',
                            padding: '32px 0', fontSize: 14,
                        }}>
                            Tidak ada menu ditemukan
                        </div>
                    ) : (
                        <div style={{
                            display: 'grid',
                            gridTemplateColumns: 'repeat(2, 1fr)',
                            gap: 14,
                        }}>
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
