import { formatRupiah, formatDate, formatTime } from '@/helpers';

const STATUS_MAP = {
    pending:  { label: 'Pending',  bg: '#FEF9EC', color: '#D97706' },
    diproses: { label: 'Diproses', bg: '#FEF3EC', color: '#E8763A' },
    selesai:  { label: 'Selesai',  bg: '#EDF7F5', color: '#14B8A6' },
};

function getPaymentLabel(method) {
    if (method === 'cash') return 'Tunai';
    if (method === 'qris') return 'QRIS';
    return '';
}

export default function RiwayatCard({ order, onDetail }) {
    const s = STATUS_MAP[order.status] ?? { label: order.status, bg: '#F5F0EB', color: '#8C7B6B' };
    const payLabel = getPaymentLabel(order.payment_method);
    const titleLabel = payLabel ? `${order.order_code} - ${payLabel}` : order.order_code;

    return (
        <div style={{
            background: '#FFFFFF', borderRadius: 20,
            border: '1px solid #EDE8E2', padding: 20,
            display: 'flex', flexDirection: 'column', gap: 10,
            boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
        }}>
            {/* Top: title + status badge */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{
                    fontSize: 15, fontWeight: 700, color: '#2D2016',
                    fontFamily: '"DM Sans", system-ui, sans-serif',
                }}>
                    {titleLabel}
                </span>
                <span style={{
                    background: s.bg, color: s.color,
                    borderRadius: 12, padding: '4px 10px',
                    fontSize: 12, fontWeight: 600,
                    fontFamily: 'Outfit, system-ui, sans-serif',
                    whiteSpace: 'nowrap',
                }}>
                    {s.label}
                </span>
            </div>

            {/* Date */}
            <span style={{ fontSize: 12, color: '#B5A898', fontFamily: 'Outfit, system-ui' }}>
                {formatTime(order.created_at)} - {formatDate(order.created_at)}
            </span>

            {/* Items summary */}
            <span style={{
                fontSize: 13, color: '#8C7B6B', fontFamily: 'Outfit, system-ui',
                overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
            }}>
                {order.items_summary}
            </span>

            {/* Bottom: price + detail button */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{
                    fontSize: 16, fontWeight: 700, color: '#2D2016',
                    fontFamily: '"DM Sans", system-ui',
                }}>
                    {formatRupiah(order.total_amount)}
                </span>
                <button
                    onClick={() => onDetail(order)}
                    style={{
                        background: order.status === 'selesai' ? '#F5F0EB' : '#E8763A',
                        color: order.status === 'selesai' ? '#8C7B6B' : '#FFFFFF',
                        border: 'none', borderRadius: 14,
                        padding: '8px 16px', fontSize: 13, fontWeight: 600,
                        fontFamily: 'Outfit, system-ui', cursor: 'pointer',
                        boxShadow: order.status === 'selesai' ? 'none' : '0 2px 8px rgba(232,118,58,0.25)',
                    }}
                >
                    Detail
                </button>
            </div>
        </div>
    );
}
