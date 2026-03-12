import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { Search, Clock, CircleCheck } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import { formatDate } from '@/helpers';

/* ── Status helpers ── */
function getStatus(student) {
    if (student.is_student_verified === true)  return 'disetujui';
    if (student.is_student_verified === false) return 'ditolak';
    return 'menunggu';
}

const STATUS_STYLE = {
    menunggu:  { dot: '#F59E0B', text: '#F59E0B', label: 'Menunggu' },
    disetujui: { dot: '#16A34A', text: '#16A34A', label: 'Disetujui' },
    ditolak:   { dot: '#DC2626', text: '#DC2626', label: 'Ditolak' },
};

function StudentBadge({ status }) {
    const s = STATUS_STYLE[status] ?? STATUS_STYLE.menunggu;
    return (
        <span style={{ fontSize: 13, fontWeight: 500, color: s.text }}>
            <span style={{ color: s.dot, marginRight: 4 }}>●</span>{s.label}
        </span>
    );
}

const TABS = [
    { key: 'semua',    label: 'Semua' },
    { key: 'menunggu', label: 'Menunggu' },
    { key: 'disetujui',label: 'Disetujui' },
    { key: 'ditolak',  label: 'Ditolak' },
];

export default function VerifikasiAkun({ students, counts }) {
    const [search,    setSearch]    = useState('');
    const [activeTab, setActiveTab] = useState('semua');

    /* ── Filter ── */
    const displayedStudents = students.filter(s => {
        const status = getStatus(s);
        if (activeTab === 'menunggu'  && status !== 'menunggu') return false;
        if (activeTab === 'disetujui' && status !== 'disetujui') return false;
        if (activeTab === 'ditolak'   && status !== 'ditolak')   return false;
        if (search.trim()) {
            const q = search.toLowerCase();
            if (!s.name?.toLowerCase().includes(q) && !s.nim?.toLowerCase().includes(q)) return false;
        }
        return true;
    });

    return (
        <CashierLayout title="Verifikasi Akun Mahasiswa">

            {/* ── Header ── */}
            <div style={{
                display: 'flex', alignItems: 'center',
                justifyContent: 'space-between', marginBottom: 28,
            }}>
                {/* Left */}
                <div>
                    <h1 style={{
                        fontSize: 24, fontWeight: 700, color: '#0F172A',
                        margin: '0 0 4px', letterSpacing: '-0.5px',
                    }}>
                        Verifikasi Akun Mahasiswa
                    </h1>
                    <p style={{ fontSize: 14, color: '#64748B', margin: 0 }}>
                        Kelola dan verifikasi pendaftaran akun pelanggan mahasiswa
                    </p>
                </div>

                {/* Right: stat chips */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <div style={{
                        display: 'flex', alignItems: 'center', gap: 8,
                        height: 36, padding: '0 14px', borderRadius: 8,
                        background: '#FFFBEB',
                    }}>
                        <Clock size={16} color="#F59E0B" />
                        <span style={{ fontSize: 13, fontWeight: 600, color: '#F59E0B' }}>
                            {counts.menunggu} Menunggu
                        </span>
                    </div>
                    <div style={{
                        display: 'flex', alignItems: 'center', gap: 8,
                        height: 36, padding: '0 14px', borderRadius: 8,
                        background: '#F0FDF4',
                    }}>
                        <CircleCheck size={16} color="#16A34A" />
                        <span style={{ fontSize: 13, fontWeight: 600, color: '#16A34A' }}>
                            {counts.disetujui} Disetujui
                        </span>
                    </div>
                </div>
            </div>

            {/* ── Toolbar ── */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                {/* Search */}
                <div style={{ position: 'relative', width: 320, flexShrink: 0 }}>
                    <Search size={18} style={{
                        position: 'absolute', left: 14, top: '50%',
                        transform: 'translateY(-50%)', color: '#94A3B8', pointerEvents: 'none',
                    }} />
                    <input
                        type="text"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        placeholder="Cari nama atau NIM..."
                        style={{
                            width: '100%', height: 44,
                            border: '1px solid #E2E8F0', borderRadius: 8,
                            padding: '0 16px 0 44px', fontSize: 14, color: '#0F172A',
                            background: '#FFFFFF', outline: 'none', boxSizing: 'border-box',
                        }}
                    />
                </div>

                {/* Tab pills */}
                {TABS.map(tab => {
                    const active = activeTab === tab.key;
                    return (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            style={{
                                height: 36, padding: '0 16px', borderRadius: 100,
                                border: 'none', cursor: 'pointer', fontSize: 13,
                                fontWeight: active ? 600 : 500,
                                background: active ? '#3B6FD4' : '#E2E8F0',
                                color: active ? '#FFFFFF' : '#64748B',
                                transition: 'background 0.15s, color 0.15s',
                            }}
                        >
                            {tab.label}
                        </button>
                    );
                })}
            </div>

            {/* ── Table Card ── */}
            <div style={{
                background: '#FFFFFF', borderRadius: 16,
                border: '1px solid #E2E8F0',
                boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                overflow: 'hidden',
            }}>
                {/* Head */}
                <div style={{
                    display: 'flex', alignItems: 'center',
                    background: '#F1F5F9', padding: '12px 16px',
                    borderBottom: '1px solid #E2E8F0',
                }}>
                    {[
                        { label: 'No',        w: 50 },
                        { label: 'Nama',      w: 160 },
                        { label: 'NIM',       w: 130 },
                        { label: 'Tgl Daftar',w: 120 },
                        { label: 'Status',    w: 120 },
                        { label: 'Aksi',      flex: 1 },
                    ].map(col => (
                        <div key={col.label} style={{ width: col.w, flex: col.flex, flexShrink: 0 }}>
                            <span style={{ fontSize: 12, fontWeight: 600, color: '#64748B' }}>
                                {col.label}
                            </span>
                        </div>
                    ))}
                </div>

                {/* Rows */}
                {displayedStudents.length === 0 ? (
                    <div style={{
                        textAlign: 'center', color: '#94A3B8',
                        padding: '48px 16px', fontSize: 14,
                    }}>
                        Tidak ada data
                    </div>
                ) : (
                    displayedStudents.map((student, idx) => (
                        <StudentRow
                            key={student.id}
                            student={student}
                            no={idx + 1}
                            status={getStatus(student)}
                        />
                    ))
                )}
            </div>

        </CashierLayout>
    );
}

/* ── StudentRow ── */
function StudentRow({ student, no, status }) {
    const [hovered, setHovered] = useState(false);
    const { patch, processing } = useForm();

    function handleApprove() {
        patch(route('cashier.verifikasi.approve', student.id));
    }

    function handleReject() {
        patch(route('cashier.verifikasi.reject', student.id));
    }

    const isPending = status === 'menunggu';

    return (
        <div
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            style={{
                display: 'flex', alignItems: 'center',
                padding: '14px 16px', borderBottom: '1px solid #E2E8F0',
                background: hovered ? '#F8FAFC' : '#FFFFFF',
                transition: 'background 0.1s',
            }}
        >
            <div style={{ width: 50, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: '#64748B' }}>{no}</span>
            </div>
            <div style={{ width: 160, flexShrink: 0 }}>
                <span style={{ fontSize: 13, fontWeight: 600, color: '#0F172A' }}>
                    {student.name}
                </span>
            </div>
            <div style={{ width: 130, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: '#64748B', fontFamily: 'monospace' }}>
                    {student.nim ?? '—'}
                </span>
            </div>
            <div style={{ width: 120, flexShrink: 0 }}>
                <span style={{ fontSize: 13, color: '#64748B' }}>
                    {formatDate(student.created_at)}
                </span>
            </div>
            <div style={{ width: 120, flexShrink: 0 }}>
                <StudentBadge status={status} />
            </div>
            <div style={{ flex: 1, display: 'flex', alignItems: 'center', gap: 8 }}>
                {isPending ? (
                    <>
                        <button
                            onClick={handleApprove}
                            disabled={processing}
                            style={{
                                background: 'none', border: 'none', padding: 0,
                                fontSize: 13, fontWeight: 600, color: '#16A34A',
                                cursor: processing ? 'not-allowed' : 'pointer',
                            }}
                        >
                            Setujui
                        </button>
                        <button
                            onClick={handleReject}
                            disabled={processing}
                            style={{
                                background: 'none', border: 'none', padding: 0,
                                fontSize: 13, fontWeight: 600, color: '#DC2626',
                                cursor: processing ? 'not-allowed' : 'pointer',
                            }}
                        >
                            Tolak
                        </button>
                    </>
                ) : (
                    <span style={{ fontSize: 13, fontWeight: 500, color: '#3B6FD4', cursor: 'pointer' }}>
                        Detail
                    </span>
                )}
            </div>
        </div>
    );
}
