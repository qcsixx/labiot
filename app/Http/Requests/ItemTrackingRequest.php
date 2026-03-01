<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ItemTrackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'borrow_request_id' => 'required|exists:borrow_requests,id',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // Photo is required only when creating a new tracking
        if ($this->isMethod('post')) {
            $rules['photo'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
        } else {
            $rules['photo'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
        }

        // Status is only required when updating a borrow request status
        if ($this->has('status')) {
            $rules['status'] = 'required|string|in:pending,approved,rejected,borrowed,returned,lost';
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'borrow_request_id.required' => 'ID peminjaman wajib diisi',
            'borrow_request_id.exists' => 'ID peminjaman tidak valid',
            'location.required' => 'Lokasi wajib diisi',
            'location.max' => 'Lokasi maksimal 255 karakter',
            'photo.required' => 'Foto wajib diunggah',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format foto harus jpeg, png, atau jpg',
            'photo.max' => 'Ukuran foto maksimal 2MB',
            'notes.max' => 'Catatan maksimal 1000 karakter',
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status tidak valid',
        ];
    }
} 