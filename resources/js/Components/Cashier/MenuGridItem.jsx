import { Plus } from 'lucide-react';
import { formatRupiah } from '@/helpers';

export default function MenuGridItem({ menu, onAdd }) {
    return (
        <div
            onClick={() => onAdd(menu)}
            style={{
                background: '#FFFFFF',
                border: '1px solid #E2E8F0',
                borderRadius: 16,
                overflow: 'hidden',
                cursor: 'pointer',
                boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
                transition: 'box-shadow 0.15s, transform 0.1s',
            }}
            onMouseEnter={e => {
                e.currentTarget.style.boxShadow = '0 4px 16px rgba(0,0,0,0.10)';
                e.currentTarget.style.transform = 'translateY(-1px)';
            }}
            onMouseLeave={e => {
                e.currentTarget.style.boxShadow = '0 2px 8px rgba(0,0,0,0.04)';
                e.currentTarget.style.transform = 'translateY(0)';
            }}
        >
            {/* Image placeholder */}
            <div style={{
                height: 100,
                background: '#E2E8F0',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#94A3B8',
                fontSize: 12,
            }}>
                {menu.image
                    ? <img src={menu.image} alt={menu.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                    : '📷'}
            </div>

            {/* Content */}
            <div style={{ padding: 12, display: 'flex', flexDirection: 'column', gap: 4 }}>
                <div style={{
                    fontSize: 11,
                    textTransform: 'uppercase',
                    color: '#64748B',
                    letterSpacing: '0.5px',
                    fontWeight: 500,
                }}>
                    {menu.category?.name}
                </div>
                <div style={{ fontSize: 14, fontWeight: 600, color: '#0F172A' }}>
                    {menu.name}
                </div>
                <div style={{ fontSize: 13, fontWeight: 500, color: '#3B6FD4' }}>
                    {formatRupiah(menu.price)}
                </div>

                {/* Tambah button */}
                <button
                    onClick={e => { e.stopPropagation(); onAdd(menu); }}
                    style={{
                        marginTop: 4,
                        height: 34,
                        width: '100%',
                        background: '#3B6FD4',
                        color: 'white',
                        border: 'none',
                        borderRadius: 8,
                        fontSize: 12,
                        fontWeight: 600,
                        cursor: 'pointer',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        gap: 4,
                    }}
                >
                    <Plus size={13} /> Tambah
                </button>
            </div>
        </div>
    );
}
