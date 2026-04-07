import { useState, useRef, useEffect } from 'react';
import { MoreVertical, Banknote, QrCode, Check, Clock } from 'lucide-react';

const METHOD_META = {
    cash:        { label: 'Tunai',       bg: '#FEF9EC', color: '#B45309', Icon: Banknote },
    qris:        { label: 'QRIS',        bg: '#EEF2FF', color: '#3B6FD4', Icon: QrCode   },
    bayar_nanti: { label: 'Bayar Nanti', bg: '#EEF2FF', color: '#3B6FD4', Icon: Clock    },
};

import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';


const STATUS_META = {
    pending:  { dot: '#D08068', label: 'Pending'  },
    diproses: { dot: '#D4A64A', label: 'Diproses' },
    selesai:  { dot: '#4D9B6A', label: 'Selesai'  },
};

export default function OrderCard({ order, onDetail, onOpenQrisModal, onMarkDone, onConfirmPayment }) {
    const [menuOpen,    setMenuOpen]    = useState(false);
    const [payPopover,  setPayPopover]  = useState(false);
    const menuRef    = useRef(null);
    const payRef     = useRef(null);

    useEffect(() => {
        if (!menuOpen) return;
        function handler(e) {
            if (menuRef.current && !menuRef.current.contains(e.target)) setMenuOpen(false);
        }
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, [menuOpen]);

    useEffect(() => {
        if (!payPopover) return;
        function handler(e) {
            if (payRef.current && !payRef.current.contains(e.target)) setPayPopover(false);
        }
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, [payPopover]);

    const isQrisPending  = order.status === 'pending' && order.payment_method === 'qris';
    const hasProof       = !!order.payment_proof;
    const belumBayar     = order.is_paid === false;
    const ALL_STATUSES   = ['pending', 'diproses', 'selesai'];
    const statusIndex    = ALL_STATUSES.indexOf(order.status);

    return (
        <div style={{
            background: '#FFFFFF',
            border: belumBayar ? '2px solid #EF4444' : '1px solid #E2E8F0',
            borderRadius: 16,
            padding: 16,
            display: 'flex',
            flexDirection: 'column',
            gap: 10,
            boxShadow: belumBayar
                ? '0 4px 14px rgba(239,68,68,0.10)'
                : '0 4px 14px rgba(15,23,42,0.06)',
            position: 'relative',
        }}>
            {/* ── Top row: order code + payment badge | status + ellipsis ── */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8 }}>
                {/* Left */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, minWidth: 0 }}>
                    <span style={{ fontSize: 15, fontWeight: 600, color: '#0F172A', fontFamily: 'Outfit, system-ui', flexShrink: 0 }}>
                        #{order.order_code}
                    </span>
                    {order.payment_method && order.payment_method !== 'bayar_nanti' && (() => {
                        const m = METHOD_META[order.payment_method] ?? METHOD_META['cash'];
                        const isQris = order.payment_method === 'qris';
                        return (
                            <span style={{
                                display: 'flex', alignItems: 'center', gap: 3,
                                background: m.bg, color: m.color,
                                borderRadius: 6, padding: isQris ? '2px 7px 2px 5px' : '2px 7px',
                                fontSize: 11, fontWeight: 600, flexShrink: 0,
                                fontFamily: 'Outfit, system-ui',
                            }}>
                                {isQris
                                    ? <img src="/images/logo-qris.png" alt="QRIS" style={{ height: 14, objectFit: 'contain' }} />
                                    : <><m.Icon size={11} />{m.label}</>
                                }
                            </span>
                        );
                    })()}

                </div>

                {/* Right: status badge + ellipsis */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 6, flexShrink: 0 }}>
                    <StatusBadge status={order.status} />

                    {order.status !== 'selesai' && (
                        <div ref={menuRef} style={{ position: 'relative' }}>
                            <button
                                onClick={() => setMenuOpen(v => !v)}
                                style={{
                                    width: 28, height: 28, borderRadius: 8,
                                    background: menuOpen ? '#F1F5F9' : 'transparent',
                                    border: 'none', cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    color: '#64748B',
                                    transition: 'background 0.1s',
                                }}
                            >
                                <MoreVertical size={16} />
                            </button>

                            {menuOpen && (
                                <div style={{
                                    position: 'absolute', right: 0, top: 32, zIndex: 100,
                                    width: 200,
                                    background: '#FFFFFF',
                                    borderRadius: 14,
                                    border: '1px solid #E5E4E1',
                                    boxShadow: '0 6px 20px rgba(15,23,42,0.094), 0 1px 3px rgba(15,23,42,0.031)',
                                    padding: 6,
                                    display: 'flex', flexDirection: 'column',
                                }}>
                                    <div style={{
                                        padding: '6px 10px 4px',
                                        fontSize: 11, fontWeight: 600,
                                        letterSpacing: '0.5px',
                                        color: '#9C9B99',
                                        fontFamily: 'Outfit, system-ui',
                                    }}>
                                        Ubah Status
                                    </div>

                                    {ALL_STATUSES.map((s, i) => {
                                        const meta       = STATUS_META[s];
                                        const isCurrent  = s === order.status;
                                        const isNext     = i === statusIndex + 1;
                                        const isDisabled = !isCurrent && !isNext;
                                        // Blok "Selesai" jika belum bayar
                                        const isBlocked  = s === 'selesai' && belumBayar;

                                        return (
                                            <button
                                                key={s}
                                                disabled={isDisabled || isCurrent || isBlocked}
                                                onClick={() => {
                                                    if (!isNext || isBlocked) return;
                                                    setMenuOpen(false);
                                                    onMarkDone(order.id, s);
                                                }}
                                                style={{
                                                    display: 'flex', alignItems: 'center',
                                                    justifyContent: 'space-between',
                                                    width: '100%',
                                                    padding: 10, gap: 10,
                                                    borderRadius: 8,
                                                    border: 'none',
                                                    background: isCurrent ? '#FAFAF8' : 'transparent',
                                                    cursor: (isNext && !isBlocked) ? 'pointer' : 'default',
                                                    opacity: (isDisabled || isBlocked) ? 0.4 : 1,
                                                    transition: 'background 0.1s',
                                                    textAlign: 'left',
                                                }}
                                                onMouseEnter={e => {
                                                    if (isNext && !isBlocked) e.currentTarget.style.background = '#F1F5F9';
                                                }}
                                                onMouseLeave={e => {
                                                    e.currentTarget.style.background = isCurrent ? '#FAFAF8' : 'transparent';
                                                }}
                                            >
                                                <span style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                    <span style={{
                                                        width: 8, height: 8, borderRadius: '50%',
                                                        background: meta.dot, flexShrink: 0,
                                                        display: 'inline-block',
                                                    }} />
                                                    <span style={{
                                                        fontSize: 13,
                                                        fontWeight: isCurrent ? 600 : 500,
                                                        color: '#1A1918',
                                                        fontFamily: 'Outfit, system-ui',
                                                    }}>
                                                        {meta.label}
                                                    </span>
                                                </span>
                                                {isCurrent && (
                                                    <Check size={14} color="#3D8A5A" strokeWidth={2.5} />
                                                )}
                                            </button>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* ── Time ── */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 12, color: '#94A3B8', fontFamily: 'Outfit, system-ui' }}>
                <span>{formatTime(order.created_at)} · {formatDate(order.created_at)}</span>
                {order.table_number && (
                    <span style={{ color: '#CBD5E1' }}>· Meja {order.table_number}</span>
                )}
                {belumBayar && (
                    <span style={{
                        display: 'flex', alignItems: 'center', gap: 3,
                        background: '#FEF2F2', color: '#EF4444',
                        borderRadius: 6, padding: '2px 7px',
                        fontSize: 11, fontWeight: 700,
                    }}>
                        Belum Bayar
                    </span>
                )}
            </div>

            {/* ── Items summary ── */}
            <div style={{
                fontSize: 13, color: '#64748B', fontFamily: 'Outfit, system-ui',
                overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
            }}>
                {order.items_summary || '-'}
            </div>

            {/* ── Bottom: total + buttons ── */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 2, gap: 8 }}>
                <span style={{ fontSize: 16, fontWeight: 700, color: '#0F172A', fontFamily: 'Outfit, system-ui' }}>
                    {formatRupiah(order.total_amount)}
                </span>

                <div style={{ display: 'flex', gap: 6 }}>
                    {isQrisPending && hasProof && (
                        <button
                            onClick={() => onOpenQrisModal(order)}
                            style={{
                                height: 32, padding: '0 12px',
                                background: '#3B6FD4', color: '#FFFFFF',
                                border: 'none', borderRadius: 8,
                                fontSize: 12, fontWeight: 600, cursor: 'pointer',
                                fontFamily: 'Outfit, system-ui',
                            }}
                        >
                            Lihat Bukti
                        </button>
                    )}

                    {/* Tombol Konfirmasi Lunas + Popover */}
                    {belumBayar && order.status === 'diproses' && (
                        <div ref={payRef} style={{ position: 'relative' }}>
                            <button
                                onClick={() => setPayPopover(v => !v)}
                                style={{
                                    height: 32, padding: '0 12px',
                                    background: '#EF4444', color: '#FFFFFF',
                                    border: 'none', borderRadius: 8,
                                    fontSize: 12, fontWeight: 600, cursor: 'pointer',
                                    fontFamily: 'Outfit, system-ui',
                                    boxShadow: '0 2px 6px rgba(239,68,68,0.25)',
                                }}
                            >
                                Konfirmasi Lunas
                            </button>

                            {payPopover && (
                                <div style={{
                                    position: 'absolute', bottom: 38, right: 0, zIndex: 200,
                                    background: '#FFFFFF',
                                    borderRadius: 12,
                                    border: '1px solid #E2E8F0',
                                    boxShadow: '0 6px 20px rgba(15,23,42,0.12)',
                                    padding: 8,
                                    display: 'flex', flexDirection: 'column', gap: 4,
                                    minWidth: 150,
                                }}>
                                    <span style={{
                                        fontSize: 11, fontWeight: 600, color: '#94A3B8',
                                        padding: '2px 8px 4px',
                                        fontFamily: 'Outfit, system-ui', letterSpacing: 0.4,
                                    }}>
                                        Metode Pembayaran
                                    </span>
                                    {[
                                        { method: 'cash', label: 'Tunai',  Icon: Banknote },
                                        { method: 'qris', label: 'QRIS',   Icon: QrCode  },
                                    ].map(({ method, label, Icon }) => (
                                        <button
                                            key={method}
                                            onClick={() => {
                                                setPayPopover(false);
                                                onConfirmPayment(order.id, method);
                                            }}
                                            style={{
                                                display: 'flex', alignItems: 'center', gap: 8,
                                                padding: '8px 10px', borderRadius: 8,
                                                border: 'none', background: 'transparent',
                                                cursor: 'pointer', textAlign: 'left',
                                                fontSize: 13, fontWeight: 500, color: '#0F172A',
                                                fontFamily: 'Outfit, system-ui',
                                            }}
                                            onMouseEnter={e => e.currentTarget.style.background = '#F1F5F9'}
                                            onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                                        >
                                            <Icon size={15} color="#64748B" />
                                            {label}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    <button
                        onClick={() => onDetail(order.id)}
                        style={{
                            height: 32, padding: '0 14px',
                            background: '#FFFFFF', border: '1px solid #E2E8F0',
                            borderRadius: 8, fontSize: 13, fontWeight: 500,
                            color: '#0F172A', cursor: 'pointer',
                            fontFamily: 'Outfit, system-ui',
                        }}
                    >
                        Detail
                    </button>
                </div>
            </div>
        </div>
    );
}
