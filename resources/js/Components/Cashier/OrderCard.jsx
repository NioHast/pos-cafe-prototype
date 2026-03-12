import { router } from '@inertiajs/react';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

export default function OrderCard({ order, onDetail }) {
    return (
        <div style={{
            background: '#FFFFFF',
            border: '1px solid #E2E8F0',
            borderRadius: 16,
            padding: 16,
            display: 'flex',
            flexDirection: 'column',
            gap: 10,
            boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
        }}>
            {/* Top: order code + status badge */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontSize: 15, fontWeight: 600, color: '#0F172A' }}>
                    #{order.order_code}
                </span>
                <StatusBadge status={order.status} />
            </div>

            {/* Time */}
            <div style={{ fontSize: 12, color: '#94A3B8' }}>
                {formatTime(order.created_at)} - {formatDate(order.created_at)}
            </div>

            {/* Items summary */}
            <div style={{
                fontSize: 13,
                color: '#64748B',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap',
            }}>
                {order.items_summary || '-'}
            </div>

            {/* Bottom: total + detail button */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 2 }}>
                <span style={{ fontSize: 16, fontWeight: 700, color: '#0F172A' }}>
                    {formatRupiah(order.total_amount)}
                </span>
                <button
                    onClick={() => onDetail(order.id)}
                    style={{
                        height: 36,
                        padding: '0 16px',
                        background: '#FFFFFF',
                        border: '1px solid #E2E8F0',
                        borderRadius: 10,
                        fontSize: 13,
                        fontWeight: 500,
                        color: '#0F172A',
                        cursor: 'pointer',
                        transition: 'background 0.15s',
                    }}
                    onMouseEnter={e => e.currentTarget.style.background = '#F8FAFC'}
                    onMouseLeave={e => e.currentTarget.style.background = '#FFFFFF'}
                >
                    Detail
                </button>
            </div>
        </div>
    );
}
