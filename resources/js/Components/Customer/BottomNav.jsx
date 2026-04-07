import { Link } from '@inertiajs/react';
import { Coffee, ShoppingCart, Clock } from 'lucide-react';
import useCart from '@/Hooks/useCart';

function getRiwayatHref() {
    try {
        const saved = sessionStorage.getItem('w9_customer');
        if (saved) {
            const data = JSON.parse(saved);
            if (data?.phone) return `/customer/riwayat?phone=${encodeURIComponent(data.phone)}`;
        }
    } catch (_) {}
    return '/customer/riwayat';
}

export default function BottomNav({ activeTab }) {
    const { count } = useCart();

    const TABS = [
        { key: 'menu',    label: 'Menu',      Icon: Coffee,       href: '/customer/menu' },
        { key: 'cart',    label: 'Keranjang', Icon: ShoppingCart, href: '/customer/cart' },
        { key: 'riwayat', label: 'Riwayat',   Icon: Clock,        href: getRiwayatHref() },
    ];

    return (
        <div style={{
            position: 'fixed', bottom: 0,
            left: '50%', transform: 'translateX(-50%)',
            width: '100%', maxWidth: 430,
            padding: '10px 18px 18px',
            background: '#FAF8F5',
            zIndex: 100,
        }}>
            <nav style={{
                background: '#FFFFFF',
                borderRadius: 22,
                height: 64,
                padding: 4,
                border: '1px solid #EDE8E2',
                boxShadow: '0 -2px 12px rgba(45,32,22,0.06)',
                display: 'flex',
            }}>
                {TABS.map(({ key, label, Icon, href }) => {
                    const active = activeTab === key;
                    const showBadge = key === 'cart' && count > 0;
                    return (
                        <Link
                            key={key}
                            href={href}
                            style={{
                                flex: 1,
                                display: 'flex', flexDirection: 'column',
                                alignItems: 'center', justifyContent: 'center',
                                gap: 2,
                                borderRadius: 18,
                                textDecoration: 'none',
                                color: active ? '#E8763A' : '#B5A898',
                                background: active ? 'rgba(232,118,58,0.08)' : 'transparent',
                                transition: 'color 0.15s, background 0.15s',
                                position: 'relative',
                            }}
                        >
                            <div style={{ position: 'relative' }}>
                                <Icon size={22} />
                                {showBadge && (
                                    <span style={{
                                        position: 'absolute', top: -6, right: -8,
                                        background: '#E8363A', color: '#FFFFFF',
                                        borderRadius: '50%', width: 17, height: 17,
                                        fontSize: 10, fontWeight: 700,
                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        border: '1.5px solid #FAF8F5',
                                    }}>
                                        {count > 9 ? '9+' : count}
                                    </span>
                                )}
                            </div>
                            <span style={{
                                fontSize: 11,
                                fontWeight: active ? 700 : 500,
                            }}>
                                {label}
                            </span>
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}
