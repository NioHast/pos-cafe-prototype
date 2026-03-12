import { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    LayoutDashboard,
    ShoppingCart,
    ClipboardList,
    History,
    UserCheck,
    User,
    LogOut,
    CheckCircle,
    XCircle,
} from 'lucide-react';

const navItems = [
    { label: 'Dashboard',       route: 'cashier.dashboard',     icon: LayoutDashboard },
    { label: 'Pesanan Baru',    route: 'cashier.pesanan-baru',  icon: ShoppingCart },
    { label: 'Pesanan Aktif',   route: 'cashier.pesanan-aktif', icon: ClipboardList },
    { label: 'Riwayat Pesanan', route: 'cashier.riwayat',       icon: History },
    { label: 'Verifikasi Akun', route: 'cashier.verifikasi',    icon: UserCheck },
    { label: 'Profil',          route: 'cashier.profil',        icon: User },
];

export default function CashierLayout({ children, title = 'Dashboard' }) {
    const { flash } = usePage().props;
    const [toast, setToast] = useState(null);

    useEffect(() => {
        if (flash?.success) {
            setToast({ type: 'success', message: flash.success });
            const t = setTimeout(() => setToast(null), 3000);
            return () => clearTimeout(t);
        }
        if (flash?.error) {
            setToast({ type: 'error', message: flash.error });
            const t = setTimeout(() => setToast(null), 3000);
            return () => clearTimeout(t);
        }
    }, [flash]);

    return (
        <div style={{ display: 'flex', minHeight: '100vh', fontFamily: "'Inter', system-ui, sans-serif" }}>

            {/* ── SIDEBAR ── */}
            <aside style={{
                width: 260,
                minHeight: '100vh',
                background: '#0F172A',
                display: 'flex',
                flexDirection: 'column',
                flexShrink: 0,
            }}>

                {/* Brand / Logo */}
                <div style={{
                    padding: '24px 20px',
                    borderBottom: '1px solid rgba(255,255,255,0.06)',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 12,
                }}>
                    <div style={{
                        width: 36,
                        height: 36,
                        background: '#1E293B',
                        borderRadius: 10,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        boxShadow: '0 2px 10px rgba(0,0,0,0.20)',
                        flexShrink: 0,
                    }}>
                        <span style={{
                            color: 'white',
                            fontSize: 13,
                            fontStyle: 'italic',
                            fontWeight: 700,
                            fontFamily: 'Georgia, serif',
                        }}>w9</span>
                    </div>
                    <span style={{ color: 'white', fontWeight: 700, fontSize: 16 }}>W9 Cafe</span>
                </div>

                {/* Nav */}
                <nav style={{ flex: 1, padding: '20px 20px 0', display: 'flex', flexDirection: 'column', gap: 4, overflowY: 'auto' }}>
                    {navItems.map(({ label, route: r, icon: Icon }) => {
                        const active = route().current(r);
                        return (
                            <Link
                                key={r}
                                href={route(r)}
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 12,
                                    height: 44,
                                    padding: '0 16px',
                                    borderRadius: 8,
                                    textDecoration: 'none',
                                    fontSize: 14,
                                    fontWeight: active ? 600 : 500,
                                    color: active ? '#FFFFFF' : '#94A3B8',
                                    background: active ? '#3B6FD4' : 'transparent',
                                    transition: 'background 0.15s, color 0.15s',
                                }}
                                onMouseEnter={e => { if (!active) e.currentTarget.style.background = '#1E293B'; }}
                                onMouseLeave={e => { if (!active) e.currentTarget.style.background = 'transparent'; }}
                            >
                                <Icon size={20} />
                                {label}
                            </Link>
                        );
                    })}
                </nav>

                {/* Logout */}
                <div style={{ padding: '12px 20px 24px', borderTop: '1px solid rgba(255,255,255,0.06)' }}>
                    <button
                        onClick={() => router.post(route('logout'))}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 12,
                            height: 44,
                            padding: '0 16px',
                            borderRadius: 8,
                            width: '100%',
                            background: 'transparent',
                            border: 'none',
                            color: '#DC2626',
                            fontSize: 14,
                            fontWeight: 500,
                            cursor: 'pointer',
                            transition: 'background 0.15s',
                        }}
                        onMouseEnter={e => e.currentTarget.style.background = 'rgba(220,38,38,0.08)'}
                        onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                    >
                        <LogOut size={20} />
                        Keluar
                    </button>
                </div>
            </aside>

            {/* ── MAIN CONTENT ── */}
            <main style={{ flex: 1, background: '#F8FAFC', padding: 32, minHeight: '100vh' }}>
                <div style={{
                    background: 'white',
                    borderRadius: 12,
                    padding: 24,
                    minHeight: 'calc(100vh - 64px)',
                    border: '1px solid #E2E8F0',
                    boxShadow: '0 2px 8px rgba(15,23,42,0.03)',
                }}>
                    {children}
                </div>
            </main>

            {/* ── TOAST ── */}
            {toast && (
                <div style={{
                    position: 'fixed',
                    top: 24,
                    right: 24,
                    zIndex: 9999,
                    background: toast.type === 'success' ? '#F0FDF4' : '#FEF2F2',
                    border: `1px solid ${toast.type === 'success' ? '#86EFAC' : '#FCA5A5'}`,
                    borderRadius: 10,
                    padding: '12px 16px',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 10,
                    fontSize: 14,
                    color: toast.type === 'success' ? '#15803D' : '#DC2626',
                    boxShadow: '0 4px 16px rgba(0,0,0,0.10)',
                    minWidth: 280,
                    maxWidth: 380,
                }}>
                    {toast.type === 'success'
                        ? <CheckCircle size={18} style={{ flexShrink: 0 }} />
                        : <XCircle size={18} style={{ flexShrink: 0 }} />}
                    {toast.message}
                </div>
            )}
        </div>
    );
}
