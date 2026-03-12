import { Link } from '@inertiajs/react';
import { Coffee, ShoppingCart, Clock, User } from 'lucide-react';

const TABS = [
    { key: 'menu',    label: 'Menu',      Icon: Coffee,       routeName: 'customer.menu' },
    { key: 'cart',    label: 'Keranjang', Icon: ShoppingCart, routeName: 'customer.cart' },
    { key: 'riwayat', label: 'Riwayat',   Icon: Clock,        routeName: 'customer.riwayat' },
    { key: 'akun',    label: 'Akun',      Icon: User,         routeName: 'customer.auth.login' },
];

export default function BottomNav({ activeTab }) {
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
                {TABS.map(({ key, label, Icon, routeName }) => {
                    const active = activeTab === key;
                    return (
                        <Link
                            key={key}
                            href={route(routeName)}
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
                            }}
                        >
                            <Icon size={22} />
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
