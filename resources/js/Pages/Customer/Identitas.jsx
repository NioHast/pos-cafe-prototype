import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { User, Phone, Coffee, Check, MapPin } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';

export default function Identitas({ table }) {
    const [name,        setName]        = useState('');
    const [phone,       setPhone]       = useState('');
    const [isMahasiswa, setIsMahasiswa] = useState(false);
    const [nameError,   setNameError]   = useState('');
    const [phoneError,  setPhoneError]  = useState('');

    /* Jika sudah pernah isi identitas untuk meja ini → skip ke menu */
    useEffect(() => {
        if (!table) return;
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (saved) {
                const data = JSON.parse(saved);
                if (data.name && data.phone && data.tableId === table.id) {
                    router.visit(`/customer/menu?table=${table.id}`);
                }
            }
        } catch (_) {}
    }, []);

    function handleLanjut() {
        let valid = true;

        if (!name.trim() || name.trim().length < 2) {
            setNameError('Nama minimal 2 karakter');
            valid = false;
        } else {
            setNameError('');
        }

        const phoneClean = phone.replace(/\D/g, '');
        if (!phoneClean || phoneClean.length < 10 || phoneClean.length > 15) {
            setPhoneError('Nomor telepon tidak valid (min 10 digit)');
            valid = false;
        } else {
            setPhoneError('');
        }

        if (!valid) return;

        sessionStorage.setItem('w9_customer', JSON.stringify({
            name:        name.trim(),
            phone:       phoneClean,
            isMahasiswa: isMahasiswa,
            tableId:     table?.id ?? null,
            tableNumber: table?.table_number ?? null,
        }));

        router.visit(table ? `/customer/menu?table=${table.id}` : '/customer/menu');
    }

    /* Jika tidak ada meja (akses langsung tanpa QR) */
    if (!table) {
        return (
            <CustomerLayout activeTab="menu" showBottomNav={false}>
                <div style={{
                    minHeight: '100vh',
                    background: '#FAF6F1',
                    display: 'flex', flexDirection: 'column',
                    alignItems: 'center', justifyContent: 'center',
                    padding: '40px 28px',
                    fontFamily: "'Outfit', system-ui, sans-serif",
                    textAlign: 'center',
                }}>
                    <div style={{
                        width: 80, height: 80, borderRadius: 20,
                        background: 'radial-gradient(ellipse 140% 140% at 50% 30%, #2A4F5F 0%, #1B3A4B 100%)',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        marginBottom: 24,
                        boxShadow: '0 8px 30px rgba(0,0,0,0.20)',
                    }}>
                        <Coffee size={36} color="#FFFFFF" />
                    </div>
                    <h1 style={{ fontFamily: "'DM Serif Display', Georgia, serif", fontSize: 24, fontWeight: 700, color: '#2D2016', margin: '0 0 12px' }}>
                        Scan QR Meja
                    </h1>
                    <p style={{ fontSize: 14, color: '#8C7B6B', lineHeight: 1.6, margin: '0 0 8px' }}>
                        Silakan scan QR code yang ada di meja Anda untuk mulai memesan.
                    </p>
                    <p style={{ fontSize: 12, color: '#B5A898' }}>
                        Hubungi kasir jika membutuhkan bantuan.
                    </p>
                </div>
            </CustomerLayout>
        );
    }

    return (
        <CustomerLayout activeTab="menu" showBottomNav={false}>
            <div style={{
                minHeight: '100vh',
                background: '#FAF6F1',
                fontFamily: "'Outfit', 'DM Sans', system-ui, sans-serif",
                display: 'flex',
                flexDirection: 'column',
                position: 'relative',
                overflow: 'hidden',
            }}>

                {/* ── Hero Section (teal gradient) ── */}
                <div style={{
                    background: 'radial-gradient(ellipse 140% 140% at 50% 30%, #2A4F5F 0%, #1B3A4B 100%)',
                    height: 280,
                    position: 'relative',
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    overflow: 'hidden',
                    flexShrink: 0,
                }}>
                    {/* Deco circles */}
                    <div style={{ position:'absolute', width:180, height:180, borderRadius:'50%', background:'#FFFFFF06', top:-40, left:-60 }}/>
                    <div style={{ position:'absolute', width:100, height:100, borderRadius:'50%', background:'#FFFFFF04', top:20, right:-10 }}/>
                    <div style={{ position:'absolute', width:60,  height:60,  borderRadius:'50%', background:'#FFFFFF05', bottom:40, right:50 }}/>

                    {/* Logo */}
                    <div style={{
                        width: 130, height: 130,
                        borderRadius: 24,
                        overflow: 'hidden',
                        boxShadow: '0 8px 30px rgba(0,0,0,0.40)',
                        zIndex: 1,
                    }}>
                        <img
                            src="/images/logo.jpg"
                            alt="W9 Cafe"
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            onError={e => {
                                e.target.style.display = 'none';
                                e.target.parentElement.style.background = '#2A4F5F';
                                e.target.parentElement.style.display = 'flex';
                                e.target.parentElement.style.alignItems = 'center';
                                e.target.parentElement.style.justifyContent = 'center';
                                e.target.parentElement.innerHTML = '<span style="color:white;font-size:32px;font-style:italic;font-weight:700">w9</span>';
                            }}
                        />
                    </div>

                    {/* Curved bottom */}
                    <div style={{
                        position: 'absolute', bottom: -1, left: 0, right: 0,
                        height: 40,
                        background: '#FAF6F1',
                        borderRadius: '50% 50% 0 0 / 100% 100% 0 0',
                    }}/>
                </div>

                {/* ── Content ── */}
                <div style={{ padding: '28px 28px 40px', flex: 1 }}>

                    {/* Heading */}
                    <div style={{ textAlign: 'center', marginBottom: 28 }}>
                        <h1 style={{
                            fontFamily: "'DM Serif Display', Georgia, serif",
                            fontSize: 26, fontWeight: 700, color: '#2D2016', margin: '0 0 8px',
                        }}>
                            Selamat Datang!
                        </h1>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 4 }}>
                            <MapPin size={12} color="#B5A898" />
                            <span style={{ fontSize: 13, color: '#8C7B6B' }}>
                                Meja No. <strong style={{ color: '#2D2016' }}>{table.table_number}</strong>
                            </span>
                        </div>
                    </div>

                    {/* Form */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>

                        {/* Nama */}
                        <div>
                            <label style={{ fontSize: 13, fontWeight: 600, color: '#2D2016', display: 'block', marginBottom: 7 }}>
                                Nama Lengkap
                            </label>
                            <div style={{ position: 'relative' }}>
                                <User size={20} color="#C4A882" style={{
                                    position: 'absolute', left: 16, top: '50%',
                                    transform: 'translateY(-50%)', pointerEvents: 'none',
                                }}/>
                                <input
                                    type="text"
                                    value={name}
                                    onChange={e => setName(e.target.value)}
                                    onKeyDown={e => e.key === 'Enter' && handleLanjut()}
                                    placeholder="Masukkan nama lengkap kamu"
                                    maxLength={100}
                                    style={{
                                        width: '100%', height: 52,
                                        border: `1.5px solid ${nameError ? '#DC3545' : '#EDE8E2'}`,
                                        borderRadius: 16,
                                        padding: '0 16px 0 48px',
                                        fontSize: 14, color: '#2D2016',
                                        background: '#FFFFFF', outline: 'none',
                                        boxShadow: '0 3px 10px rgba(45,32,22,0.03)',
                                        boxSizing: 'border-box',
                                        fontFamily: "'Outfit', system-ui, sans-serif",
                                    }}
                                />
                            </div>
                            {nameError && <p style={{ color: '#DC3545', fontSize: 12, margin: '4px 0 0' }}>{nameError}</p>}
                        </div>

                        {/* Telepon */}
                        <div>
                            <label style={{ fontSize: 13, fontWeight: 600, color: '#2D2016', display: 'block', marginBottom: 7 }}>
                                No. Telepon
                            </label>
                            <div style={{ position: 'relative' }}>
                                <Phone size={20} color="#C4A882" style={{
                                    position: 'absolute', left: 16, top: '50%',
                                    transform: 'translateY(-50%)', pointerEvents: 'none',
                                }}/>
                                <input
                                    type="tel"
                                    value={phone}
                                    onChange={e => setPhone(e.target.value.replace(/[^0-9]/g, ''))}
                                    onKeyDown={e => e.key === 'Enter' && handleLanjut()}
                                    placeholder="08xxxxxxxxxx"
                                    maxLength={15}
                                    style={{
                                        width: '100%', height: 52,
                                        border: `1.5px solid ${phoneError ? '#DC3545' : '#EDE8E2'}`,
                                        borderRadius: 16,
                                        padding: '0 16px 0 48px',
                                        fontSize: 14, color: '#2D2016',
                                        background: '#FFFFFF', outline: 'none',
                                        boxShadow: '0 3px 10px rgba(45,32,22,0.03)',
                                        boxSizing: 'border-box',
                                        fontFamily: "'Outfit', system-ui, sans-serif",
                                    }}
                                />
                            </div>
                            {phoneError && <p style={{ color: '#DC3545', fontSize: 12, margin: '4px 0 0' }}>{phoneError}</p>}
                        </div>

                        {/* Mahasiswa checkbox */}
                        <div
                            onClick={() => setIsMahasiswa(p => !p)}
                            style={{
                                background: '#FEF3EC',
                                borderRadius: 14,
                                border: '1px solid #F0DDD0',
                                padding: '12px 14px',
                                display: 'flex', alignItems: 'center', gap: 10,
                                cursor: 'pointer',
                            }}
                        >
                            <div style={{
                                width: 22, height: 22, borderRadius: 6, flexShrink: 0,
                                border: `1.5px solid ${isMahasiswa ? '#E8763A' : '#D4B89C'}`,
                                background: '#FFFFFF',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                {isMahasiswa && <Check size={14} color="#E8763A" />}
                            </div>
                            <div>
                                <div style={{ fontSize: 13, fontWeight: 500, color: '#2D2016' }}>
                                    Saya adalah mahasiswa STIE Totalwin Semarang
                                </div>
                                <div style={{ fontSize: 11, color: '#B5906E', marginTop: 2 }}>
                                    Opsional
                                </div>
                            </div>
                        </div>

                        {/* CTA Button */}
                        <button
                            onClick={handleLanjut}
                            style={{
                                width: '100%', height: 54,
                                background: '#E8763A', color: '#FFFFFF',
                                border: 'none', borderRadius: 20,
                                fontSize: 16, fontWeight: 700, cursor: 'pointer',
                                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                                marginTop: 8,
                                boxShadow: '0 6px 20px rgba(232,118,58,0.40)',
                                fontFamily: "'DM Sans', system-ui, sans-serif",
                            }}
                        >
                            <Coffee size={20} color="#FFFFFF" />
                            Masuk
                        </button>
                    </div>
                </div>
            </div>
        </CustomerLayout>
    );
}
