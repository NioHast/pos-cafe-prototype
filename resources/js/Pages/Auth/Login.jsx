import { useForm } from '@inertiajs/react';
import { Mail, Lock, AlertCircle } from 'lucide-react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <div style={{ display: 'flex', minHeight: '100vh', fontFamily: "'Inter', system-ui, sans-serif" }}>

            {/* ── KIRI — Navy Panel ── */}
            <div style={{
                width: '50%',
                background: '#0F172A',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                gap: 14,
            }}>
                {/* Logo */}
                <img
                    src="/images/logo.jpg"
                    alt="W9 Cafe"
                    style={{
                        width: 120,
                        height: 120,
                        borderRadius: 24,
                        objectFit: 'cover',
                        boxShadow: '0 4px 20px rgba(0,0,0,0.30)',
                    }}
                />

                <h1 style={{
                    color: 'white',
                    fontSize: 32,
                    fontWeight: 700,
                    margin: '4px 0 0',
                    letterSpacing: '-0.5px',
                }}>W9 Cafe</h1>

                <p style={{ color: 'rgba(255,255,255,0.5)', fontSize: 15, margin: 0 }}>
                    Sistem Point of Sale
                </p>
            </div>

            {/* ── KANAN — Form Panel ── */}
            <div style={{
                width: '50%',
                background: '#FFFFFF',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '0 56px',
            }}>
                <form
                    onSubmit={handleSubmit}
                    style={{ width: '100%', maxWidth: 400, display: 'flex', flexDirection: 'column', gap: 24 }}
                >
                    {/* Heading */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                        <h2 style={{
                            fontSize: 26,
                            fontWeight: 700,
                            color: '#0F172A',
                            margin: 0,
                            letterSpacing: '-0.5px',
                        }}>Masuk ke Akun Anda</h2>
                        <p style={{ fontSize: 14, color: '#64748B', margin: 0 }}>
                            Masukkan email dan kata sandi untuk melanjutkan
                        </p>
                    </div>

                    {/* Error Box */}
                    {errors.email && (
                        <div style={{
                            background: '#FEF2F2',
                            border: '1px solid #FCA5A5',
                            borderRadius: 12,
                            padding: '12px 14px',
                            display: 'flex',
                            alignItems: 'center',
                            gap: 8,
                            fontSize: 13,
                            color: '#DC2626',
                        }}>
                            <AlertCircle size={16} style={{ flexShrink: 0 }} />
                            {errors.email}
                        </div>
                    )}

                    {/* Email */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <label style={{ fontSize: 13, fontWeight: 500, color: '#0F172A' }}>Email</label>
                        <div style={{ position: 'relative' }}>
                            <Mail size={18} style={{
                                position: 'absolute',
                                left: 14,
                                top: '50%',
                                transform: 'translateY(-50%)',
                                color: '#94A3B8',
                                pointerEvents: 'none',
                            }} />
                            <input
                                type="email"
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                placeholder="kasir@kafenusantara.com"
                                autoComplete="email"
                                style={{
                                    width: '100%',
                                    height: 44,
                                    border: '1px solid #E2E8F0',
                                    borderRadius: 8,
                                    padding: '0 14px 0 44px',
                                    fontSize: 14,
                                    color: '#0F172A',
                                    outline: 'none',
                                    boxSizing: 'border-box',
                                    boxShadow: '0 2px 8px rgba(15,23,42,0.03)',
                                    transition: 'border-color 0.15s',
                                }}
                                onFocus={e => e.target.style.borderColor = '#3B6FD4'}
                                onBlur={e => e.target.style.borderColor = '#E2E8F0'}
                            />
                        </div>
                    </div>

                    {/* Password */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                        <label style={{ fontSize: 13, fontWeight: 500, color: '#0F172A' }}>Kata Sandi</label>
                        <div style={{ position: 'relative' }}>
                            <Lock size={18} style={{
                                position: 'absolute',
                                left: 14,
                                top: '50%',
                                transform: 'translateY(-50%)',
                                color: '#94A3B8',
                                pointerEvents: 'none',
                            }} />
                            <input
                                type="password"
                                value={data.password}
                                onChange={e => setData('password', e.target.value)}
                                placeholder="Masukkan kata sandi..."
                                autoComplete="current-password"
                                style={{
                                    width: '100%',
                                    height: 44,
                                    border: '1px solid #E2E8F0',
                                    borderRadius: 8,
                                    padding: '0 14px 0 44px',
                                    fontSize: 14,
                                    color: '#0F172A',
                                    outline: 'none',
                                    boxSizing: 'border-box',
                                    boxShadow: '0 2px 8px rgba(15,23,42,0.03)',
                                    transition: 'border-color 0.15s',
                                }}
                                onFocus={e => e.target.style.borderColor = '#3B6FD4'}
                                onBlur={e => e.target.style.borderColor = '#E2E8F0'}
                            />
                        </div>
                    </div>

                    {/* Submit */}
                    <button
                        type="submit"
                        disabled={processing}
                        style={{
                            width: '100%',
                            height: 44,
                            background: processing ? '#93AEDF' : '#3B6FD4',
                            color: 'white',
                            border: 'none',
                            borderRadius: 8,
                            fontSize: 14,
                            fontWeight: 600,
                            cursor: processing ? 'not-allowed' : 'pointer',
                            boxShadow: '0 4px 16px rgba(59,111,212,0.30)',
                            transition: 'background 0.15s',
                        }}
                    >
                        {processing ? 'Memuat...' : 'Masuk'}
                    </button>
                </form>
            </div>
        </div>
    );
}
