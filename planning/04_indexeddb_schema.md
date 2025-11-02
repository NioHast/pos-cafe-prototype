Database: POSOfflineDB_v2
│
├── Object Store: menu
│     ├── Key Path: id
│     ├── Index: name
│     ├── Index: category_id
│     └── Data Example (Denormalized):
│           { 
│             "id": 2, 
│             "name": "Nasi Goreng", 
│             "price": 30000,
│             "student_price": 25000,
│             "category_id": 3,
│             "recipe": [
│               { "ingredient_id": 10, "quantity_used": 100 },
│               { "ingredient_id": 25, "quantity_used": 15 }
│             ]
│           }
│
├── Object Store: categories
│     ├── Key Path: id
│     ├── Index: name
│     └── Data Example:
│           { "id": 3, "name": "Makanan Berat" }
│
├── Object Store: ingredient_stock
│     ├── Key Path: ingredient_id
│     └── Data Example:
│           { "ingredient_id": 10, "name": "Beras", "quantity": 9900 }
│
├── Object Store: promotions
│     ├── Key Path: id
│     └── Data Example (Denormalized):
│           {
│             "id": 5,
│             "name": "Diskon Akhir Pekan Kopi",
│             "type": "percentage",
│             "value": 15,
│             "rules": [
│               { "applicable_type": "category", "applicable_id": 1 }
│             ]
│           }
│
├── Object Store: currentUser
│     ├── Key Path: id
│     └── Data Example:
│           { "id": 101, "name": "Budi", "role": "cashier" }
│
└── Object Store: transaction_queue
      ├── Key Path: ++id (auto-increment)
      ├── Index: uuid
      ├── Index: created_at
      └── Data Example:
            {
              "id": 1,
              "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
              "created_at": "2025-10-18T14:30:00.000Z",
              "payload": { 
                "cashier_id": 101, 
                "customer_id": null,
                "total_price": 42500,
                "payment_method": "cash",
                "items": [
                  { "menu_id": 5, "quantity": 1, "price_at_transaction": 25000, "prepared_by": 101 }
                ],
                "applied_promotions": [
                  { "promotion_id": 5, "amount_saved": 7500 }
                ]
              }
            }
