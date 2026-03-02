<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\StudentProfile;

/**
 * API Routes untuk Testing Admin Panel Features
 * 
 * Digunakan untuk simulate frontend kasir & pelanggan yang belum selesai.
 * Test menggunakan Postman/Insomnia/Thunder Client.
 */

// ============================================
// AUTHENTICATION TESTING
// ============================================

/**
 * Test: Login Kasir
 * POST /api/auth/login
 * Body: { "email": "kasir@cafe.com", "password": "password" }
 */
Route::post('/auth/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user->load('role'),
    ]);
});

/**
 * Test: Register Student Account
 * POST /api/auth/register-student
 * Body: {
 *   "name": "John Doe",
 *   "email": "john@student.ac.id",
 *   "password": "password",
 *   "nim": "2024001",
 *   "faculty": "Engineering",
 *   "major": "Computer Science",
 *   "enrollment_year": 2024
 * }
 */
Route::post('/auth/register-student', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'nim' => 'required|string|max:20',
        'faculty' => 'required|string',
        'major' => 'required|string',
        'enrollment_year' => 'required|integer',
    ]);

    // Create user account
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role_id' => 3, // Assuming role_id 3 = student
    ]);

    // Create student profile (will be created in Phase 1, Sprint 1.2)
    // Uncomment setelah student_profiles table ada:
    /*
    StudentProfile::create([
        'user_id' => $user->id,
        'nim' => $validated['nim'],
        'faculty' => $validated['faculty'],
        'major' => $validated['major'],
        'enrollment_year' => $validated['enrollment_year'],
        'valid_until' => now()->addYear(),
        'is_verified' => false, // Needs admin approval
    ]);
    */

    return response()->json([
        'message' => 'Student account created. Waiting for admin verification.',
        'user' => $user,
    ], 201);
});

// ============================================
// ORDER TESTING (KASIR & PELANGGAN)
// ============================================

/**
 * Test: Get Menu List (untuk kasir & pelanggan)
 * GET /api/menu
 */
Route::get('/menu', function () {
    $menu = Menu::with('category')
        ->where('is_active', true)
        ->get();

    return response()->json($menu);
});

/**
 * Test: Create Order (Kasir)
 * POST /api/orders
 * Body: {
 *   "cashier_id": 2,
 *   "customer_name": "John Doe",
 *   "table_number": null,
 *   "payment_method": "cash",
 *   "items": [
 *     { "menu_id": 1, "quantity": 2, "handled_by": 2 },
 *     { "menu_id": 3, "quantity": 1, "handled_by": 2 }
 *   ]
 * }
 */
Route::post('/orders', function (Request $request) {
    $validated = $request->validate([
        'cashier_id' => 'required|exists:users,id',
        'customer_name' => 'required|string',
        'table_number' => 'nullable|string',
        'payment_method' => 'required|string',
        'items' => 'required|array|min:1',
        'items.*.menu_id' => 'required|exists:menu,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.handled_by' => 'required|exists:users,id',
    ]);

    // Calculate total
    $total = 0;
    $orderItems = [];

    foreach ($validated['items'] as $item) {
        $menu = Menu::findOrFail($item['menu_id']);
        $price = $menu->regular_price; // TODO: Check student_price if student
        $subtotal = $price * $item['quantity'];
        $total += $subtotal;

        $orderItems[] = [
            'menu_id' => $item['menu_id'],
            'quantity' => $item['quantity'],
            'price' => $price,
            'subtotal' => $subtotal,
            'handled_by' => $item['handled_by'],
        ];
    }

    // Create order
    $order = Order::create([
        'order_number' => 'ORD-' . now()->format('YmdHis'),
        'cashier_id' => $validated['cashier_id'],
        'customer_name' => $validated['customer_name'],
        'table_number' => $validated['table_number'],
        'total_amount' => $total,
        'payment_method' => $validated['payment_method'],
        'payment_status' => 'paid',
        'status' => 'completed',
        'order_date' => now(),
        'paid_at' => now(),
    ]);

    // Create order items
    foreach ($orderItems as $item) {
        $order->items()->create($item);
    }

    return response()->json([
        'message' => 'Order created successfully',
        'order' => $order->load('items.menu'),
    ], 201);
});

/**
 * Test: Create Order (Pelanggan Self-Order)
 * POST /api/customer/orders
 * Body: {
 *   "customer_name": "Jane Doe",
 *   "customer_phone": "081234567890",
 *   "table_number": "A5",
 *   "payment_method": "qris",
 *   "items": [
 *     { "menu_id": 1, "quantity": 1 },
 *     { "menu_id": 2, "quantity": 2 }
 *   ]
 * }
 */
Route::post('/customer/orders', function (Request $request) {
    $validated = $request->validate([
        'customer_name' => 'required|string',
        'customer_phone' => 'required|string',
        'table_number' => 'required|string',
        'payment_method' => 'required|string',
        'items' => 'required|array|min:1',
        'items.*.menu_id' => 'required|exists:menu,id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    // Calculate total
    $total = 0;
    $orderItems = [];

    foreach ($validated['items'] as $item) {
        $menu = Menu::findOrFail($item['menu_id']);
        
        // TODO: Check if customer is verified student
        $price = $menu->regular_price;
        
        $subtotal = $price * $item['quantity'];
        $total += $subtotal;

        $orderItems[] = [
            'menu_id' => $item['menu_id'],
            'quantity' => $item['quantity'],
            'price' => $price,
            'subtotal' => $subtotal,
            'handled_by' => null, // Belum assigned ke staf
        ];
    }

    // Create order (no cashier_id for self-order)
    $order = Order::create([
        'order_number' => 'SELF-' . now()->format('YmdHis'),
        'cashier_id' => null,
        'customer_name' => $validated['customer_name'],
        'customer_phone' => $validated['customer_phone'],
        'table_number' => $validated['table_number'],
        'total_amount' => $total,
        'payment_method' => $validated['payment_method'],
        'payment_status' => 'pending', // Waiting for payment
        'status' => 'pending',
        'order_date' => now(),
    ]);

    // Create order items
    foreach ($orderItems as $item) {
        $order->items()->create($item);
    }

    return response()->json([
        'message' => 'Order created. Please complete payment.',
        'order' => $order->load('items.menu'),
        'payment_url' => 'https://midtrans.com/snap/...' // TODO: Midtrans integration
    ], 201);
});

/**
 * Test: Get Order Detail
 * GET /api/orders/{id}
 */
Route::get('/orders/{id}', function ($id) {
    $order = Order::with(['items.menu', 'cashier', 'items.handler'])
        ->findOrFail($id);

    return response()->json($order);
});

// ============================================
// STUDENT PROFILE TESTING
// ============================================

/**
 * Test: Get Student Profile (setelah login)
 * GET /api/student/profile
 * Header: Authorization: Bearer {token}
 */
Route::middleware('auth:sanctum')->get('/student/profile', function (Request $request) {
    $user = $request->user();
    
    // TODO: Uncomment setelah StudentProfile model ada
    // $profile = StudentProfile::where('user_id', $user->id)->first();
    
    return response()->json([
        'user' => $user->load('role'),
        // 'profile' => $profile,
        'message' => 'Student profile feature will be implemented in Sprint 1.2'
    ]);
});

// ============================================
// TESTING UTILITIES
// ============================================

/**
 * Test: Get All Users (untuk testing)
 * GET /api/test/users
 */
Route::get('/test/users', function () {
    return response()->json(User::with('role')->get());
});

/**
 * Test: Get All Orders (untuk testing)
 * GET /api/test/orders
 */
Route::get('/test/orders', function () {
    return response()->json(
        Order::with(['items.menu', 'cashier'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
    );
});
