Database: POSOfflineDB_v3
│
│  CATATAN OPTIMIZED (v3):
│  - Data referensi (menu, categories, promotions, users) menggunakan INTEGER ID dari server
│  - HANYA transaction_queue yang pakai UUID (client create offline)
│  - Storage lebih efisien: 8 bytes (int) vs 16 bytes (uuid) per field
│
├── Object Store: menu
│     ├── Key Path: id (integer dari server)
│     ├── Index: name
│     ├── Index: category_id
│     └── Data Example (Denormalized):
│           { 
│             "id": 1, 
│             "name": "Nasi Goreng", 
│             "price": 30000,
│             "student_price": 25000,
│             "category_id": 2,
│             "recipe": [
│               { "ingredient_id": 5, "quantity_used": 100 },
│               { "ingredient_id": 8, "quantity_used": 15 }
│             ]
│           }
│
├── Object Store: categories
│     ├── Key Path: id (integer dari server)
│     ├── Index: name
│     └── Data Example:
│           { "id": 2, "name": "Makanan Berat" }
│
├── Object Store: ingredient_stock
│     ├── Key Path: ingredient_id (integer dari server)
│     └── Data Example:
│           { "ingredient_id": 5, "name": "Beras", "quantity": 9900 }
│
├── Object Store: promotions
│     ├── Key Path: id (integer dari server)
│     └── Data Example (Denormalized):
│           {
│             "id": 3,
│             "name": "Diskon Akhir Pekan Kopi",
│             "type": "percentage",
│             "value": 15,
│             "rules": [
│               { "applicable_type": "category", "applicable_id": 2 }
│             ]
│           }
│
├── Object Store: currentUser
│     ├── Key Path: id (integer dari server)
│     └── Data Example:
│           { "id": 10, "name": "Budi", "role": "cashier" }
│
└── Object Store: transaction_queue
      ├── Key Path: id (UUID string, CLIENT-GENERATED)
      ├── Index: created_at
      ├── Index: sync_status
      └── Data Example:
            {
              "id": "019471a2-6666-7fff-0000-111122223333",
              "created_at": "2025-10-18T14:30:00.000Z",
              "sync_status": "pending",
              "payload": { 
                "cashier_id": 10, 
                "customer_id": null,
                "total_price": 42500,
                "payment_method": "cash",
                "items": [
                  { "menu_id": 1, "quantity": 1, "price_at_transaction": 25000, "prepared_by": 10 }
                ],
                "applied_promotions": [
                  { "promotion_id": 3, "amount_saved": 7500 }
                ]
              }
            }

CATATAN PENTING:
- HANYA transaction_queue.id yang pakai UUID (client generate offline)
- Semua data referensi (menu, categories, promotions, users) pakai INTEGER ID dari server
- Field referensi dalam payload (menu_id, cashier_id, promotion_id) tetap INTEGER
- Sync status: 'pending' | 'synced' | 'failed' untuk tracking
