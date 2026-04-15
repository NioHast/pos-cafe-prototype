import { useEffect, useState } from 'react';
import { formatRupiah } from '@/helpers';

export default function MenuGridItem({ menu, onAdd }) {
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
            cardBg: '#0B0F1A',
            border: '#2B3345',
            shadow: '0 3px 12px rgba(0,0,0,0.25)',
            shadowHover: '0 6px 16px rgba(0,0,0,0.35)',
            category: '#94A3B8',
            name: '#E5E7EB',
            price: '#5B8CFF',
        }
        : {
            cardBg: '#FFFFFF',
            border: '#E9ECEF',
            shadow: 'none',
            shadowHover: '0 4px 12px rgba(0,0,0,0.08)',
            category: '#9CA3AF',
            name: '#111827',
            price: '#3B6FD4',
        };

    return (
        <div
            onClick={() => onAdd(menu)}
            style={{
                background: c.cardBg,
                border: `1px solid ${c.border}`,
                borderRadius: 8,
                padding: '20px 16px',
                cursor: 'pointer',
                boxShadow: c.shadow,
                transition: 'box-shadow 0.15s, transform 0.1s',
                display: 'flex',
                flexDirection: 'column',
                gap: 6,
                textAlign: 'center',
                fontFamily: "'Inter', system-ui, sans-serif",
            }}
            onMouseEnter={e => {
                e.currentTarget.style.boxShadow = c.shadowHover;
                e.currentTarget.style.transform = 'translateY(-1px)';
            }}
            onMouseLeave={e => {
                e.currentTarget.style.boxShadow = c.shadow;
                e.currentTarget.style.transform = 'translateY(0)';
            }}
        >
            <div style={{ fontSize: 11, textTransform: 'uppercase', color: c.category, letterSpacing: '0.6px', fontWeight: 500 }}>
                {menu.category?.name}
            </div>
            <div style={{ fontSize: 15, fontWeight: 700, color: c.name, lineHeight: 1.3 }}>
                {menu.name}
            </div>
            <div style={{ fontSize: 14, fontWeight: 500, color: c.price }}>
                {formatRupiah(menu.price)}
            </div>
        </div>
    );
}
