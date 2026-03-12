import { useEffect } from 'react';
import { openDB } from 'idb';
import useCartStore from '@/Store/cartStore';

const DB_NAME = 'w9cafe';
const STORE   = 'cart';

const initDB = () => openDB(DB_NAME, 1, {
    upgrade(db) { db.createObjectStore(STORE); },
});

const saveToIDB = async (items, tableId) => {
    try {
        const db = await initDB();
        await db.put(STORE, { items, tableId }, 'current');
    } catch { /* ignore */ }
};

const loadFromIDB = async () => {
    try { const db = await initDB(); return await db.get(STORE, 'current'); }
    catch { return null; }
};

export default function useCart() {
    const items     = useCartStore(s => s.items);
    const tableId   = useCartStore(s => s.tableId);
    const setTable  = useCartStore(s => s.setTable);
    const addItem   = useCartStore(s => s.addItem);
    const removeItem= useCartStore(s => s.removeItem);
    const updateQty = useCartStore(s => s.updateQty);
    const clearCart = useCartStore(s => s.clearCart);
    const total     = useCartStore(s => s.total());
    const count     = useCartStore(s => s.count());

    /* Hydrate from IDB when offline */
    useEffect(() => {
        if (typeof window === 'undefined' || navigator.onLine) return;
        loadFromIDB().then(data => {
            if (data?.items?.length && useCartStore.getState().items.length === 0) {
                useCartStore.setState(data);
            }
        });
    }, []);

    /* Persist to IDB on every change (debounced 500ms) */
    useEffect(() => {
        const t = setTimeout(() => saveToIDB(items, tableId), 500);
        return () => clearTimeout(t);
    }, [items, tableId]);

    return {
        items, tableId, setTable, addItem, removeItem, updateQty, clearCart,
        total, count,
        totalQty: count, /* backward-compat alias used in Menu/Index */
        isOffline: typeof window !== 'undefined' && !navigator.onLine,
    };
}
