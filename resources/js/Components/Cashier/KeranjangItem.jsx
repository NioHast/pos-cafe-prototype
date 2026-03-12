import { formatRupiah } from '@/helpers';

export default function KeranjangItem({ item, onIncrement, onDecrement }) {
    return (
        <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            padding: '12px 0',
            borderBottom: '1px solid #E2E8F0',
            gap: 12,
        }}>
            {/* Info */}
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{
                    fontSize: 14,
                    fontWeight: 500,
                    color: '#0F172A',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    whiteSpace: 'nowrap',
                }}>
                    {item.name}
                </div>
                <div style={{ fontSize: 12, color: '#94A3B8', marginTop: 2 }}>
                    {formatRupiah(item.price)}
                </div>
            </div>

            {/* Controls + Subtotal */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0 }}>
                {/* Minus */}
                <button
                    onClick={() => onDecrement(item.menuId)}
                    style={{
                        width: 28, height: 28,
                        background: '#E2E8F0',
                        color: '#0F172A',
                        border: 'none',
                        borderRadius: 8,
                        fontSize: 16,
                        fontWeight: 700,
                        cursor: 'pointer',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        lineHeight: 1,
                    }}
                >
                    −
                </button>

                {/* Qty */}
                <span style={{
                    fontSize: 14,
                    fontWeight: 600,
                    color: '#0F172A',
                    minWidth: 20,
                    textAlign: 'center',
                }}>
                    {item.quantity}
                </span>

                {/* Plus */}
                <button
                    onClick={() => onIncrement(item.menuId)}
                    style={{
                        width: 28, height: 28,
                        background: '#3B6FD4',
                        color: 'white',
                        border: 'none',
                        borderRadius: 8,
                        fontSize: 16,
                        fontWeight: 700,
                        cursor: 'pointer',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        lineHeight: 1,
                    }}
                >
                    +
                </button>

                {/* Subtotal */}
                <span style={{
                    fontSize: 13,
                    fontWeight: 600,
                    color: '#0F172A',
                    minWidth: 72,
                    textAlign: 'right',
                }}>
                    {formatRupiah(item.price * item.quantity)}
                </span>
            </div>
        </div>
    );
}
