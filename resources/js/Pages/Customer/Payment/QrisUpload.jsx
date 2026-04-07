import { useState } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { ChevronLeft, Camera, Send, CheckCircle } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah } from '@/helpers';
import useCart from '@/Hooks/useCart';

export default function QrisUpload({ order, qrisImage, qrisName, totalAmount, rejectedMessage }) {
    const [file,      setFile]      = useState(null);
    const [preview,   setPreview]   = useState(null);
    const [uploading, setUploading] = useState(false);
    const [error,     setError]     = useState(rejectedMessage ?? '');
    const [uploaded,  setUploaded]  = useState(false);
    const { clearCart } = useCart();

    function handleFileChange(e) {
        const f = e.target.files[0];
        if (!f) return;
        if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(f.type)) {
            setError('File harus berupa gambar (JPG atau PNG)');
            return;
        }
        if (f.size > 5 * 1024 * 1024) {
            setError('Ukuran file maksimal 5MB');
            return;
        }
        setError('');
        setFile(f);
        const reader = new FileReader();
        reader.onload = ev => setPreview(ev.target.result);
        reader.readAsDataURL(f);
    }

    async function handleUpload() {
        if (!file || uploading) return;
        setUploading(true);
        setError('');
        const formData = new FormData();
        formData.append('proof', file);
        try {
            await axios.post(`/api/order/${order.id}/qris-proof`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            clearCart();
            setUploaded(true);
        } catch (err) {
            setError(err.response?.data?.message ?? 'Upload gagal. Coba lagi.');
        } finally {
            setUploading(false);
        }
    }

    return (
        <CustomerLayout activeTab="cart">
            {/* ── Header ── */}
            <div style={{
                background: '#FFFFFF',
                borderBottom: '1px solid #F0EBE5',
                padding: '22px 24px 16px',
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                    <button
                        onClick={() => router.visit(`/customer/payment/${order.order_code}/choose`)}
                        style={{
                            width: 36, height: 36, borderRadius: 12,
                            background: '#F0EBE5', border: 'none', cursor: 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            flexShrink: 0,
                        }}
                    >
                        <ChevronLeft size={20} color="#2D2016" />
                    </button>
                    <div>
                        <div style={{ fontSize: 20, fontWeight: 700, color: '#2D2016', fontFamily: '"DM Sans", system-ui' }}>
                            Pembayaran QRIS
                        </div>
                        <div style={{ fontSize: 12, color: '#8C7B6B', fontFamily: 'Outfit, system-ui' }}>
                            #{order.order_code}
                        </div>
                    </div>
                </div>
            </div>

            {/* ── Content ── */}
            <div style={{ padding: '0 24px 20px', display: 'flex', flexDirection: 'column', gap: 14 }}>

                {/* Rejection banner */}
                {rejectedMessage && !uploaded && (
                    <div style={{
                        background: '#FEF2F2', border: '1px solid #FECACA',
                        borderRadius: 14, padding: 14, marginTop: 14,
                    }}>
                        <div style={{ fontSize: 13, fontWeight: 700, color: '#DC2626', marginBottom: 4, fontFamily: 'Outfit, system-ui' }}>
                            Bukti Ditolak Kasir
                        </div>
                        <div style={{ fontSize: 13, color: '#5C4A3A', fontFamily: 'Outfit, system-ui' }}>{rejectedMessage}</div>
                        <div style={{ fontSize: 12, color: '#8C7B6B', marginTop: 6, fontFamily: 'Outfit, system-ui' }}>
                            Silakan upload ulang bukti pembayaran yang valid.
                        </div>
                    </div>
                )}

                {!uploaded ? (
                    <>
                        {/* QR card */}
                        <div style={{
                            background: '#FFFFFF', borderRadius: 20,
                            border: '1px solid #EDE8E2',
                            boxShadow: '0 3px 14px rgba(45,32,22,0.05)',
                            padding: '18px 20px 14px',
                            display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 10,
                            marginTop: rejectedMessage ? 0 : 14,
                        }}>
                            {/* QR image */}
                            <div style={{
                                width: 170, height: 170, borderRadius: 14,
                                overflow: 'hidden',
                                boxShadow: '0 2px 8px rgba(45,32,22,0.10)',
                                background: '#F5F0EB',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                <img
                                    src={qrisImage}
                                    alt="QRIS W9 Cafe"
                                    style={{ width: '100%', height: '100%', objectFit: 'contain' }}
                                    onError={e => { e.target.src = '/images/logo.jpg'; }}
                                />
                            </div>

                            <span style={{ fontSize: 14, fontWeight: 500, color: '#8C7B6B', fontFamily: 'Outfit, system-ui' }}>
                                {qrisName || 'W9 Cafe STIE Totalwin'}
                            </span>

                            <span style={{
                                fontSize: 24, fontWeight: 700, color: '#E8763A',
                                fontFamily: '"DM Sans", system-ui', letterSpacing: -0.5,
                            }}>
                                {formatRupiah(totalAmount)}
                            </span>

                            <span style={{
                                fontSize: 11, color: '#B5A898', textAlign: 'center',
                                maxWidth: 260, fontFamily: 'Outfit, system-ui',
                            }}>
                                Scan QR menggunakan aplikasi dompet digital Anda
                            </span>

                            {/* Divider */}
                            <div style={{ width: '100%', height: 1, background: '#F0EBE5' }} />

                            {/* Upload section */}
                            <div style={{ width: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8 }}>
                                <div style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 4 }}>
                                    <span style={{ fontSize: 13, fontWeight: 600, color: '#2D2016', fontFamily: 'Outfit, system-ui' }}>
                                        Upload Bukti Pembayaran
                                    </span>
                                    <span style={{ fontSize: 13, fontWeight: 600, color: '#E8763A' }}>*</span>
                                </div>

                                <label htmlFor="proof-upload" style={{ width: '100%', cursor: 'pointer' }}>
                                    <div style={{
                                        width: '100%', height: preview ? 'auto' : 80,
                                        background: '#FAF8F5',
                                        borderRadius: 14,
                                        border: `1px solid ${file ? '#E8763A' : '#D6CFC6'}`,
                                        display: 'flex', flexDirection: 'column',
                                        alignItems: 'center', justifyContent: 'center', gap: 6,
                                        overflow: 'hidden',
                                        padding: preview ? 0 : undefined,
                                    }}>
                                        {preview ? (
                                            <img
                                                src={preview}
                                                alt="preview"
                                                style={{ width: '100%', maxHeight: 200, objectFit: 'contain' }}
                                            />
                                        ) : (
                                            <>
                                                <Camera size={24} color="#B5A898" />
                                                <span style={{ fontSize: 13, fontWeight: 600, color: '#2D2016', fontFamily: 'Outfit, system-ui' }}>
                                                    Pilih atau Foto Bukti Bayar
                                                </span>
                                                <span style={{ fontSize: 11, color: '#B5A898', fontFamily: 'Outfit, system-ui' }}>
                                                    JPG, PNG, max 5MB
                                                </span>
                                            </>
                                        )}
                                    </div>
                                    <input
                                        id="proof-upload" type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                        style={{ display: 'none' }}
                                        onChange={handleFileChange}
                                        capture="environment"
                                    />
                                </label>

                                {error && (
                                    <p style={{ color: '#DC2626', fontSize: 12, margin: '2px 0 0', fontFamily: 'Outfit, system-ui', alignSelf: 'flex-start' }}>
                                        {error}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Spacer */}
                        <div style={{ flex: 1 }} />

                        {/* CTA */}
                        <button
                            onClick={handleUpload}
                            disabled={!file || uploading}
                            style={{
                                width: '100%', height: 54,
                                background: !file ? '#EDE8E2' : '#E8763A',
                                color: !file ? '#9AA3AF' : '#FFFFFF',
                                border: 'none', borderRadius: 18,
                                fontSize: 16, fontWeight: 700,
                                cursor: !file ? 'default' : 'pointer',
                                boxShadow: !file ? 'none' : '0 4px 16px rgba(232,118,58,0.30)',
                                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                                fontFamily: '"DM Sans", system-ui',
                            }}
                        >
                            <Send size={18} />
                            {uploading ? 'Mengupload...' : 'Kirim Bukti Pembayaran'}
                        </button>
                    </>
                ) : (
                    /* Setelah upload berhasil */
                    <div style={{ textAlign: 'center', padding: '40px 0', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 16 }}>
                        <CheckCircle size={72} color="#28A745" />
                        <h2 style={{ fontSize: 22, fontWeight: 700, color: '#2D2016', margin: 0, fontFamily: '"DM Sans", system-ui' }}>
                            Bukti Dikirim!
                        </h2>
                        <p style={{ fontSize: 14, color: '#8C7B6B', margin: 0, lineHeight: 1.6, fontFamily: 'Outfit, system-ui' }}>
                            Kasir sedang memverifikasi pembayaran Anda.<br/>
                            Harap tunggu konfirmasi.
                        </p>
                        <button
                            onClick={() => router.visit('/customer/riwayat')}
                            style={{
                                width: '100%', height: 52, background: '#E8763A', color: 'white',
                                border: 'none', borderRadius: 18, fontSize: 15, fontWeight: 700,
                                cursor: 'pointer', fontFamily: '"DM Sans", system-ui',
                                boxShadow: '0 4px 16px rgba(232,118,58,0.30)',
                            }}
                        >
                            Cek Status Pesanan
                        </button>
                    </div>
                )}
            </div>
        </CustomerLayout>
    );
}
