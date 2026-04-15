<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                  => 'required|array|min:1',
            'items.*.menu_id'        => 'required|integer|exists:menus,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'payment_method'         => 'required|in:cash,qris,bayar_nanti',
            'customer_name'          => 'nullable|string|max:100',
            'promotion_ids'          => 'nullable|array',
            'promotion_ids.*'        => 'integer|exists:promotions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'         => 'Keranjang tidak boleh kosong.',
            'items.min'              => 'Minimal 1 item dalam pesanan.',
            'items.*.menu_id.exists' => 'Menu tidak ditemukan.',
            'items.*.quantity.min'   => 'Jumlah item minimal 1.',
        ];
    }
}
