import { useEffect, useState } from 'react';
import { formatRupiah } from '@/helpers';

export default function KeranjangItem({ item, onIncrement, onDecrement }) {
    const [isDarkMode, setIsDarkMode] = useState(() => {
        if (typeof window === 'undefined') return false;
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    });

    useEffect(() => {
        if (typeof window === 'undefined') return;
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const onChange = (event) => setIsDarkMode(event.matches);
        media.addEventListener('change', onChange);
        return () => media.removeEventListener('change', onChange);
    }, []);

    const c = isDarkMode
        ? {
            border: '#2A3142',
            name: '#E5E7EB',
            sub: '#9CA3AF',
            minusBg: '#111827',
            minusBorder: '#374151',
            minusText: '#9CA3AF',
            qty: '#E5E7EB',
            plusBg: '#3B6FD4',
            plusText: '#FFFFFF',
            total: '#E5E7EB',
        }
        : {
            border: '#F3F4F6',
            name: '#111827',
            sub: '#9CA3AF',
            minusBg: '#FFFFFF',
            minusBorder: '#E5E7EB',
            minusText: '#6B7280',
            qty: '#111827',
            plusBg: '#3B6FD4',
            plusText: '#FFFFFF',
            total: '#111827',
        };

    return (
        <div style={{ display: 'flex', alignItems: 'center', padding: '14px 0', borderBottom: `1px solid ${c.border}`, gap: 12 }}>
            {/* Name + unit price */}
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 14, fontWeight: 600, color: c.name, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {item.name}
                </div>
                <div style={{ fontSize: 12, color: c.sub, marginTop: 2 }}>
                    {formatRupiah(item.price)}
                </div>
            </div>

            {/* [-] qty [+] */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0 }}>
                <button onClick={() => onDecrement(item.menuId)}
                    style={{ width: 30, height: 30, background: c.minusBg, color: c.minusText, border: `1px solid ${c.minusBorder}`, borderRadius: 6, fontSize: 18, fontWeight: 700, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    −
                </button>

                <span style={{ fontSize: 14, fontWeight: 600, color: c.qty, minWidth: 20, textAlign: 'center' }}>
                    {item.quantity}
                </span>

                <button onClick={() => onIncrement(item.menuId)}
                    style={{ width: 30, height: 30, background: c.plusBg, color: c.plusText, border: 'none', borderRadius: 6, fontSize: 18, fontWeight: 700, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    +
                </button>

                {/* Item total */}
                <span style={{ fontSize: 14, fontWeight: 700, color: c.total, minWidth: 76, textAlign: 'right', flexShrink: 0 }}>
                    {formatRupiah(item.price * item.quantity)}
                </span>
            </div>
        </div>
    );
}
