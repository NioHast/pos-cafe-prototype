<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerMenuController extends Controller
{
    public function showIdentitas(Request $request)
    {
        $tableId = $request->query('table');
        $table   = $tableId ? CafeTable::find($tableId) : null;

        return Inertia::render('Customer/Identitas', ['table' => $table]);
    }

    public function index(Request $request)
    {
        $categories = Category::with([
            'menus' => fn($q) => $q->where('is_available', true)->orderBy('name'),
        ])->where('is_active', true)->orderBy('name')->get();

        $table = CafeTable::find($request->query('table'));

        return Inertia::render('Customer/Menu/Index', compact('categories', 'table'));
    }
}
