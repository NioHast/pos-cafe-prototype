import { useEffect } from 'react';
import BottomNav from '@/Components/Customer/BottomNav';

export default function CustomerLayout({ children, activeTab = 'menu', showBottomNav = true }) {

    /* ── Load Midtrans Snap (once) ── */
    useEffect(() => {
        if (typeof window === 'undefined' || window.snap) return;
        const snapUrl = import.meta.env.VITE_MIDTRANS_SNAP_URL;
        const clientKey = import.meta.env.VITE_MIDTRANS_CLIENT_KEY;
        if (!snapUrl || !clientKey) return;
        const s = document.createElement('script');
        s.src = snapUrl;
        s.setAttribute('data-client-key', clientKey);
        document.head.appendChild(s);
    }, []);

    return (
        <div style={{
            maxWidth: 430,
            margin: '0 auto',
            minHeight: '100vh',
            background: '#FAF8F5',
            position: 'relative',
            paddingBottom: showBottomNav ? 92 : 0,
        }}>
            {children}
            {showBottomNav && <BottomNav activeTab={activeTab} />}
        </div>
    );
}
