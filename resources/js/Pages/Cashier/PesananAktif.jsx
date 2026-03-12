import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import CashierLayout from '@/Layouts/CashierLayout';
import OrderCard from '@/Components/Cashier/OrderCard';

export default function PesananAktif({ orders, counts }) {
    const [activeTab, setActiveTab] = useState('all');

    /* ── Auto-refresh tiap 10 detik ── */
    useEffect(() => {
        const id = setInterval(() => {
            router.reload({ only: ['orders', 'counts'] });
        }, 10000);
        return () => clearInterval(id);
    }, []);

    /* ── Filter tabs ── */
    const tabs = [
        { key: 'all',     label: `Semua (${counts.all})` },
        { key: 'pending', label: `Pending (${counts.pending})` },
        { key: 'paid',    label: `Dibayar (${counts.paid})` },
        { key: 'selesai', label: `Selesai (${counts.selesai})` },
    ];

    const filteredOrders = (() => {
        switch (activeTab) {
            case 'pending': return orders.filter(o => o.status === 'pending');
            case 'paid':    return orders.filter(o => o.payment_status === 'paid');
            case 'selesai': return orders.filter(o => o.status === 'ready');
            default:        return orders;
        }
    })();

    return (
        <CashierLayout title="Pesanan Aktif">

            {/* ── Header ── */}
            <div style={{ marginBottom: 20 }}>
                <h1 style={{
                    fontSize: 26, fontWeight: 700, color: '#0F172A',
                    margin: '0 0 4px', letterSpacing: '-0.5px',
                }}>
                    Pesanan Aktif
                </h1>
                <p style={{ fontSize: 14, color: '#64748B', margin: 0 }}>
                    Kelola semua pesanan yang sedang diproses
                </p>
            </div>

            {/* ── Filter Tabs ── */}
            <div style={{ display: 'flex', gap: 8, marginBottom: 24 }}>
                {tabs.map(tab => {
                    const active = activeTab === tab.key;
                    return (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            style={{
                                height: 36,
                                padding: '0 16px',
                                borderRadius: 100,
                                border: 'none',
                                fontSize: 13,
                                fontWeight: active ? 600 : 500,
                                cursor: 'pointer',
                                background: active ? '#3B6FD4' : '#E2E8F0',
                                color: active ? '#FFFFFF' : '#64748B',
                                transition: 'background 0.15s, color 0.15s',
                            }}
                        >
                            {tab.label}
                        </button>
                    );
                })}
            </div>

            {/* ── Order Grid ── */}
            {filteredOrders.length === 0 ? (
                <div style={{
                    textAlign: 'center', color: '#94A3B8',
                    paddingTop: 64, fontSize: 14,
                }}>
                    Tidak ada pesanan aktif
                </div>
            ) : (
                <div style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(3, 1fr)',
                    gap: 18,
                    alignItems: 'start',
                }}>
                    {filteredOrders.map(order => (
                        <OrderCard
                            key={order.id}
                            order={order}
                            onDetail={id => router.visit(route('cashier.order.show', id))}
                        />
                    ))}
                </div>
            )}

        </CashierLayout>
    );
}
