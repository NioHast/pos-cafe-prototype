const statusMap = {
    pending:    { dot: '#FFC107', text: '#FFC107', label: 'Pending' },
    confirmed:  { dot: '#3B6FD4', text: '#3B6FD4', label: 'Dikonfirmasi' },
    preparing:  { dot: '#17A2B8', text: '#17A2B8', label: 'Diproses' },
    ready:      { dot: '#28A745', text: '#28A745', label: 'Siap' },
    completed:  { dot: '#28A745', text: '#28A745', label: 'Selesai' },
    cancelled:  { dot: '#DC3545', text: '#DC3545', label: 'Dibatalkan' },
    menunggu:   { dot: '#FFC107', text: '#FFC107', label: 'Menunggu' },
    disetujui:  { dot: '#28A745', text: '#28A745', label: 'Disetujui' },
    ditolak:    { dot: '#DC3545', text: '#DC3545', label: 'Ditolak' },
    dibayar:    { dot: '#17A2B8', text: '#17A2B8', label: 'Dibayar' },
};

export default function StatusBadge({ status }) {
    const s = statusMap[status] || { dot: '#6C757D', text: '#6C757D', label: status };
    return (
        <span style={{ color: s.text, fontSize: 13, fontWeight: 500 }}>
            <span style={{ color: s.dot, marginRight: 4 }}>●</span>{s.label}
        </span>
    );
}
