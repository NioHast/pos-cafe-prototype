import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah } from '@/helpers';

const STEPS = [
    { label: 'Pesanan Diterima',          statuses: ['menunggu_bayar_cash','dikonfirmasi','diproses','siap','selesai'] },
    { label: 'Menunggu Pembayaran Cash',  statuses: ['menunggu_bayar_cash'], active: true },
    { label: 'Pembayaran Dikonfirmasi',   statuses: ['dikonfirmasi','diproses','siap','selesai'] },
    { label: 'Pesanan Diproses',          statuses: ['diproses','siap','selesai'] },
    { label: 'Pesanan Siap Diambil',      statuses: ['siap','selesai'] },
];

export default function CashStatus({ order }) {
    useEffect(() => {
        if (['siap', 'selesai'].includes(order.status)) return;
        const id = setInterval(() => router.reload({ only: ['order'] }), 5000);
        return () => clearInterval(id);
    }, [order.status]);

    return (
        <CustomerLayout>
            <div style={{
                padding: 24, maxWidth: 430, margin: '0 auto',
                fontFamily: "'Outfit', system-ui, sans-serif",
                minHeight: '100vh', background: '#FAF6F1',
                display: 'flex', flexDirection: 'column', alignItems: 'center',
                justifyContent: 'center',
            }}>
                {/* Icon */}
                <div style={{
                    width: 80, height: 80, borderRadius: '50%',
                    background: '#FEF3EC',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    fontSize: 36, marginBottom: 20,
                }}>
                    💵
                </div>

                <h1 style={{ fontSize: 22, fontWeight: 700, color: '#2D2016', marginBottom: 6, textAlign: 'center' }}>
                    {order.status === 'selesai' ? 'Pesanan Selesai!' : 'Pesanan Dikonfirmasi!'}
                </h1>
                <p style={{ fontSize: 14, color: '#8C7B6B', marginBottom: 4, textAlign: 'center' }}>
                    #{order.order_code}
                </p>
                <p style={{ fontSize: 15, color: '#2D2016', marginBottom: 24, textAlign: 'center' }}>
                    Total: <strong style={{ color: '#E8763A' }}>{formatRupiah(order.total_amount)}</strong>
                </p>

                {/* Instruksi */}
                {order.status === 'menunggu_bayar_cash' && (
                    <div style={{
                        background: '#FEF3EC', borderRadius: 16, padding: 18,
                        marginBottom: 20, width: '100%',
                        border: '1px solid #F0DDD0',
                    }}>
                        <div style={{ fontSize: 14, fontWeight: 700, color: '#E8763A', marginBottom: 10 }}>
                            💡 Cara Bayar ke Kasir
                        </div>
                        <div style={{ fontSize: 13, color: '#5C4A3A', lineHeight: 1.7 }}>
                            1. Tunjukkan kode <strong>#{order.order_code}</strong> ke kasir<br/>
                            2. Bayar sesuai total: <strong>{formatRupiah(order.total_amount)}</strong><br/>
                            3. Tunggu kasir mengkonfirmasi
                        </div>
                    </div>
                )}

                {/* Status steps */}
                <div style={{
                    background: '#FFFFFF', borderRadius: 16, border: '1px solid #EDE8E2',
                    padding: 18, width: '100%', marginBottom: 20,
                }}>
                    <div style={{ fontSize: 13, fontWeight: 700, color: '#2D2016', marginBottom: 14 }}>
                        Status Pesanan
                    </div>
                    {STEPS.map((step, i) => {
                        const done   = step.statuses.includes(order.status) && !step.active;
                        const active = step.active && order.status === 'menunggu_bayar_cash';
                        return (
                            <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                                <div style={{
                                    width: 26, height: 26, borderRadius: '50%', flexShrink: 0,
                                    background: done ? '#28A745' : active ? '#E8763A' : '#EDE8E2',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    fontSize: 13, color: 'white', fontWeight: 700,
                                }}>
                                    {done ? '✓' : active ? '◐' : ''}
                                </div>
                                <span style={{
                                    fontSize: 13,
                                    color: done ? '#28A745' : active ? '#E8763A' : '#9AA3AF',
                                    fontWeight: active || done ? 600 : 400,
                                }}>
                                    {step.label}
                                </span>
                            </div>
                        );
                    })}
                </div>

                {order.status !== 'selesai' && (
                    <p style={{ fontSize: 12, color: '#B5A898', textAlign: 'center' }}>
                        Halaman ini otomatis update setiap 5 detik
                    </p>
                )}

                {order.status === 'selesai' && (
                    <button
                        onClick={() => router.visit('/customer/menu')}
                        style={{
                            width: '100%', height: 50, background: '#E8763A', color: 'white',
                            border: 'none', borderRadius: 16, fontSize: 15, fontWeight: 700, cursor: 'pointer',
                        }}
                    >
                        Pesan Lagi
                    </button>
                )}
            </div>
        </CustomerLayout>
    );
}
