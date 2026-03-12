import { useState } from 'react';
import { router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import RiwayatCard from '@/Components/Customer/RiwayatCard';

const TABS = [
    { key: 'all',      label: 'Semua'    },
    { key: 'diproses', label: 'Diproses' },
    { key: 'selesai',  label: 'Selesai'  },
];

export default function CustomerRiwayat({ orders = [] }) {
    const [activeTab, setActiveTab] = useState('all');

    const filteredOrders = orders.filter(o => {
        if (activeTab === 'all')      return true;
        if (activeTab === 'selesai')  return o.status === 'completed';
        return o.status !== 'completed'; // diproses
    });

    function goDetail(id) {
        router.visit(route('customer.order.detail', id));
    }

    return (
        <CustomerLayout activeTab="riwayat">
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
                        Riwayat Pesanan
                    </span>
                </div>

                {/* ── Segment control ── */}
                <div style={{
                    background: '#F5F0EB', borderRadius: 18,
                    border: '1px solid #EDE8E2',
                    padding: 4, display: 'flex', height: 46,
                }}>
                    {TABS.map(tab => (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            style={{
                                flex: 1, height: '100%',
                                borderRadius: 14, border: 'none', cursor: 'pointer',
                                fontSize: 14,
                                fontFamily: 'Outfit, system-ui, sans-serif',
                                background:  activeTab === tab.key ? '#FFFFFF' : 'transparent',
                                color:       activeTab === tab.key ? '#2D2016' : '#B5A898',
                                fontWeight:  activeTab === tab.key ? 700 : 500,
                                boxShadow:   activeTab === tab.key
                                    ? '0 2px 6px rgba(45,32,22,0.10)' : 'none',
                                transition: 'background 0.15s, box-shadow 0.15s',
                            }}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* ── Order list ── */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14, flex: 1 }}>
                    {filteredOrders.length === 0 ? (
                        <div style={{
                            textAlign: 'center', color: '#B5A898',
                            padding: '48px 0', fontSize: 14,
                            fontFamily: 'Outfit, system-ui, sans-serif',
                        }}>
                            Tidak ada pesanan
                        </div>
                    ) : (
                        filteredOrders.map(order => (
                            <RiwayatCard
                                key={order.id}
                                order={order}
                                onDetail={goDetail}
                            />
                        ))
                    )}
                </div>

            </div>
        </CustomerLayout>
    );
}
