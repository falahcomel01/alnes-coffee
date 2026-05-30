<?php

namespace App\Http\Requests\Order;

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
            'table_id'           => ['required', 'exists:cafe_tables,id'],
            'customer_name'      => ['required', 'string', 'max:255'],
            'customer_phone'     => ['required', 'string', 'max:20'],
            'order_type'         => ['required', 'in:dine_in,takeaway'],
            'payment_method'     => ['required', 'in:qris,cash,transfer'],
            'promo_code'         => ['nullable', 'string', 'max:50'],
            'notes'              => ['nullable', 'string', 'max:500'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.notes'      => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'table_id.required'           => 'Meja wajib dipilih.',
            'table_id.exists'             => 'Meja tidak ditemukan.',
            'customer_name.required'      => 'Nama pelanggan wajib diisi.',
            'customer_phone.required'     => 'Nomor HP wajib diisi.',
            'order_type.required'         => 'Tipe pesanan wajib dipilih.',
            'order_type.in'               => 'Tipe pesanan tidak valid.',
            'payment_method.required'     => 'Metode pembayaran wajib dipilih.',
            'payment_method.in'           => 'Metode pembayaran tidak valid.',
            'items.required'              => 'Pesanan tidak boleh kosong.',
            'items.min'                   => 'Minimal 1 item pesanan.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists'   => 'Produk tidak ditemukan.',
            'items.*.qty.required'        => 'Jumlah item wajib diisi.',
            'items.*.qty.min'             => 'Jumlah item minimal 1.',
        ];
    }
}