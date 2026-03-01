<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BorrowRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,item_id',
            'quantity' => 'required|integer|min:1',
            'borrow_date' => 'required|date|after_or_equal:today',
            'return_deadline' => 'required|date|after:borrow_date',
            'purpose' => 'required|string|max:255',
        ];
    }
    
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'Barang harus dipilih',
            'item_id.exists' => 'Barang tidak ditemukan',
            'quantity.required' => 'Jumlah barang harus diisi',
            'quantity.integer' => 'Jumlah barang harus berupa angka',
            'quantity.min' => 'Jumlah barang minimal 1',
            'borrow_date.required' => 'Tanggal peminjaman harus diisi',
            'borrow_date.date' => 'Format tanggal peminjaman tidak valid',
            'borrow_date.after_or_equal' => 'Tanggal peminjaman minimal hari ini',
            'return_deadline.required' => 'Tanggal pengembalian harus diisi',
            'return_deadline.date' => 'Format tanggal pengembalian tidak valid',
            'return_deadline.after' => 'Tanggal pengembalian harus setelah tanggal peminjaman',
            'purpose.required' => 'Tujuan peminjaman harus diisi',
            'purpose.max' => 'Tujuan peminjaman maksimal 255 karakter',
        ];
    }
}
