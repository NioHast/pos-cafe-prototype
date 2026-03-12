import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { User, Lock, LogIn } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';

export default function CustomerLogin() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        nim:  '',
    });

    const [nameActive, setNameActive] = useState(false);
    const [nimActive,  setNimActive]  = useState(false);

    function handleSubmit(e) {
        e.preventDefault();
        post(route('customer.auth.attempt'));
    }

    return (
        <CustomerLayout activeTab="akun" showBottomNav={false}>
            <div style={{
                minHeight: '100vh', background: '#FAF8F5',
                display: 'flex', flexDirection: 'column',
            }}>
                {/* ── Main scrollable content ── */}
                <div style={{
                    flex: 1,
                    padding: '40px 24px 24px',
                    display: 'flex', flexDirection: 'column',
                    gap: 26,
                }}>

                    {/* ── Logo ── */}
                    <div style={{ textAlign: 'center', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 10 }}>
                        <div style={{
                            width: 88, height: 88, borderRadius: 22,
                            background: '#1A2332',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            boxShadow: '0 4px 16px rgba(45,32,22,0.18)',
                        }}>
                            <span style={{
                                color: '#FFFFFF', fontSize: 28,
                                fontStyle: 'italic', fontFamily: 'Georgia, serif',
                                fontWeight: 700, letterSpacing: '-1px',
                            }}>
                                w9
                            </span>
                        </div>
                        <div style={{
                            fontSize: 26, fontWeight: 700, color: '#2D2016',
                            lineHeight: 1.1, marginTop: 0,
                            fontFamily: '"DM Sans", system-ui, sans-serif',
                        }}>
                            W9 Cafe
                        </div>
                        <div style={{ fontSize: 14, color: '#B5A898' }}>
                            Pemesanan Online
                        </div>
                    </div>

                    {/* ── Info banner ── */}
                    <div style={{
                        background: '#FEF3EC', borderRadius: 18, padding: 16,
                    }}>
                        <div style={{
                            fontSize: 14, fontWeight: 700, color: '#E8763A',
                            fontFamily: '"DM Sans", system-ui, sans-serif',
                        }}>
                            Login sebagai Mahasiswa
                        </div>
                        <div style={{ fontSize: 12, color: '#8C7B6B', marginTop: 3 }}>
                            Dapatkan diskon 10% untuk semua menu!
                        </div>
                    </div>

                    {/* ── Form ── */}
                    <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
                        {/* Username */}
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                            <label style={{ fontSize: 13, fontWeight: 600, color: '#2D2016' }}>
                                Username (Nama Lengkap)
                            </label>
                            <div style={{ position: 'relative' }}>
                                <User size={20} style={{
                                    position: 'absolute', left: 20, top: '50%',
                                    transform: 'translateY(-50%)', color: nameActive ? '#E8763A' : '#B5A898',
                                    pointerEvents: 'none', transition: 'color 0.15s',
                                }} />
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    onFocus={() => setNameActive(true)}
                                    onBlur={() => setNameActive(false)}
                                    placeholder="Masukkan nama lengkap..."
                                    style={{
                                        width: '100%', height: 50,
                                        background: '#FFFFFF',
                                        border: `1px solid ${nameActive ? '#E8763A' : '#EDE8E2'}`,
                                        borderRadius: 16, padding: '0 20px 0 54px',
                                        fontSize: 14, color: '#2D2016',
                                        outline: 'none', boxSizing: 'border-box',
                                        boxShadow: '0 2px 8px rgba(45,32,22,0.04)',
                                        transition: 'border-color 0.15s',
                                    }}
                                />
                            </div>
                        </div>

                        {/* NIM / Password */}
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                            <label style={{ fontSize: 13, fontWeight: 600, color: '#2D2016' }}>
                                Password (NIM)
                            </label>
                            <div style={{ position: 'relative' }}>
                                <Lock size={20} style={{
                                    position: 'absolute', left: 20, top: '50%',
                                    transform: 'translateY(-50%)', color: nimActive ? '#E8763A' : '#B5A898',
                                    pointerEvents: 'none', transition: 'color 0.15s',
                                }} />
                                <input
                                    type="password"
                                    value={data.nim}
                                    onChange={e => setData('nim', e.target.value)}
                                    onFocus={() => setNimActive(true)}
                                    onBlur={() => setNimActive(false)}
                                    placeholder="Masukkan NIM..."
                                    style={{
                                        width: '100%', height: 50,
                                        background: '#FFFFFF',
                                        border: `1px solid ${nimActive ? '#E8763A' : '#EDE8E2'}`,
                                        borderRadius: 16, padding: '0 20px 0 54px',
                                        fontSize: 14, color: '#2D2016',
                                        outline: 'none', boxSizing: 'border-box',
                                        boxShadow: '0 2px 8px rgba(45,32,22,0.04)',
                                        transition: 'border-color 0.15s',
                                    }}
                                />
                            </div>
                            {errors.nim && (
                                <span style={{ fontSize: 12, color: '#DC2626', marginTop: 2 }}>
                                    {errors.nim}
                                </span>
                            )}
                        </div>

                        {/* Submit button */}
                        <button
                            type="submit"
                            disabled={processing}
                            style={{
                                width: '100%', height: 54,
                                background: processing ? '#F0A882' : '#E8763A',
                                color: '#FFFFFF', border: 'none', borderRadius: 18,
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                gap: 8,
                                fontSize: 16, fontWeight: 700,
                                fontFamily: '"DM Sans", system-ui, sans-serif',
                                cursor: processing ? 'not-allowed' : 'pointer',
                                boxShadow: '0 4px 16px rgba(232,118,58,0.30)',
                                transition: 'background 0.15s',
                            }}
                        >
                            <LogIn size={20} />
                            {processing ? 'Masuk...' : 'Masuk'}
                        </button>
                    </form>

                    {/* ── Cara login note ── */}
                    <div style={{
                        background: '#FFFFFF', borderRadius: 18, padding: 18,
                        border: '1px solid #EDE8E2',
                        boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
                        display: 'flex', flexDirection: 'column', gap: 10,
                    }}>
                        <div style={{
                            fontSize: 14, fontWeight: 700, color: '#2D2016',
                            fontFamily: '"DM Sans", system-ui, sans-serif',
                        }}>
                            Cara Login:
                        </div>
                        <div style={{ fontSize: 13, color: '#8C7B6B', lineHeight: 1.6 }}>
                            • Username: Gunakan nama lengkap Anda
                        </div>
                        <div style={{ fontSize: 13, color: '#8C7B6B', lineHeight: 1.6 }}>
                            • Password: Gunakan NIM Anda
                        </div>
                        <div style={{ fontSize: 13, fontWeight: 600, color: '#E8763A', lineHeight: 1.6 }}>
                            • Tunjukkan KTM pada Kasir untuk memverifikasi akun.
                        </div>
                    </div>

                </div>
            </div>
        </CustomerLayout>
    );
}
