import { formatRupiah, formatDate, formatTime } from '@/helpers';

export default function RiwayatCard({ order, onDetail }) {
    const isCompleted = order.status === 'completed';

    return (
        <div style={{
            background: '#FFFFFF', borderRadius: 20,
            border: '1px solid #EDE8E2',
            padding: 20,
            display: 'flex', flexDirection: 'column', gap: 10,
            boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
        }}>
            {/* Top: order code + status badge */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{
                    fontSize: 15, fontWeight: 600, color: '#2D2016',
                    fontFamily: 'Outfit, system-ui, sans-serif',
                }}>
                    #{order.order_code}
                </span>
                <span style={{
                    background: isCompleted ? '#EDF7F0' : '#E8763A',
                    color: isCompleted ? '#5A9A6E' : '#FFFFFF',
                    borderRadius: 50, padding: '3px 10px',
                    fontSize: 12, fontWeight: 600,
                    fontFamily: 'Outfit, system-ui, sans-serif',
                }}>
                    {isCompleted ? 'Selesai' : 'Diproses'}
                </span>
            </div>

            {/* Date */}
            <span style={{
                fontSize: 12, color: '#B5A898',
                fontFamily: 'Outfit, system-ui, sans-serif',
            }}>
                {formatTime(order.created_at)} - {formatDate(order.created_at)}
            </span>

            {/* Items summary */}
            <span style={{
                fontSize: 13, color: '#8C7B6B',
                fontFamily: 'Outfit, system-ui, sans-serif',
                overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
            }}>
                {order.items_summary}
            </span>

            {/* Bottom: total + detail button */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{
                    fontSize: 16, fontWeight: 700, color: '#2D2016',
                    fontFamily: 'Outfit, system-ui, sans-serif',
                }}>
                    {formatRupiah(order.total_amount)}
                </span>
                <button
                    onClick={() => onDetail(order.id)}
                    style={{
                        background: '#E8763A', color: '#FFFFFF',
                        border: 'none', borderRadius: 12,
                        padding: '7px 18px', fontSize: 13, fontWeight: 600,
                        fontFamily: 'Outfit, system-ui, sans-serif',
                        cursor: 'pointer',
                        boxShadow: '0 2px 8px rgba(232,118,58,0.20)',
                    }}
                >
                    Detail
                </button>
            </div>
        </div>
    );
}
