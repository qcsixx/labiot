<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    */
    'error' => [
        'general' => 'Terjadi kesalahan: :message',
        'not_found' => 'Data tidak ditemukan',
        'unauthorized' => 'Anda tidak memiliki izin untuk melakukan aksi ini',
        'validation' => 'Data yang Anda masukkan tidak valid',
        'server_error' => 'Terjadi kesalahan pada server. Tim teknis telah diberitahu.',
        'database' => 'Terjadi kesalahan saat mengakses database',
    ],

    /*
    |--------------------------------------------------------------------------
    | Success Messages
    |--------------------------------------------------------------------------
    */
    'success' => [
        'created' => ':item berhasil ditambahkan',
        'updated' => ':item berhasil diperbarui',
        'deleted' => ':item berhasil dihapus',
        'saved' => 'Data berhasil disimpan',
        'sent' => ':item berhasil dikirim',
    ],

    /*
    |--------------------------------------------------------------------------
    | Borrow Request Messages
    |--------------------------------------------------------------------------
    */
    'borrow' => [
        'request_created' => 'Permintaan peminjaman berhasil diajukan',
        'request_approved' => 'Permintaan peminjaman berhasil disetujui',
        'request_rejected' => 'Permintaan peminjaman berhasil ditolak',
        'item_unavailable' => 'Barang tidak tersedia untuk dipinjam',
        'item_borrowed' => 'Barang sedang dipinjam',
        'phone_required' => 'Anda harus mengisi nomor handphone di profil Anda sebelum dapat mengajukan peminjaman',
        'quantity_insufficient' => 'Jumlah barang tersedia tidak mencukupi',
        'already_processed' => 'Permintaan sudah diproses sebelumnya',
        'pending_return' => 'dalam pengajuan pengembalian',
        'return_confirmed' => 'Pengembalian barang berhasil dikonfirmasi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Item Messages
    |--------------------------------------------------------------------------
    */
    'item' => [
        'created' => 'Barang berhasil ditambahkan',
        'updated' => 'Barang berhasil diperbarui',
        'deleted' => 'Barang berhasil dihapus',
        'cannot_delete_borrowed' => 'Barang tidak dapat dihapus karena sedang dipinjam',
        'not_found' => 'Barang tidak ditemukan',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Messages
    |--------------------------------------------------------------------------
    */
    'user' => [
        'profile_updated' => 'Profil berhasil diperbarui',
        'password_updated' => 'Password berhasil diperbarui',
        'password_mismatch' => 'Password saat ini tidak cocok',
        'suspended' => 'Pengelolaan user berhasil ditangguhkan',
        'activated' => 'Pengelolaan user berhasil diaktifkan',
        'deleted' => 'Pengelolaan user berhasil dihapus',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'required' => ':attribute harus diisi',
        'email' => ':attribute harus berupa email yang valid',
        'min' => ':attribute minimal :min karakter',
        'max' => ':attribute maksimal :max karakter',
        'numeric' => ':attribute harus berupa angka',
        'date' => ':attribute harus berupa tanggal yang valid',
        'image' => ':attribute harus berupa gambar',
        'mimes' => ':attribute harus berupa file dengan format: :values',
        'max_file' => 'Ukuran :attribute maksimal :max KB',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Messages
    |--------------------------------------------------------------------------
    */
    'notification' => [
        'borrow_approved' => 'Permintaan peminjaman Anda telah disetujui',
        'borrow_rejected' => 'Permintaan peminjaman Anda telah ditolak',
        'return_reminder' => 'Pengingat: Peminjaman Anda akan jatuh tempo',
        'overdue' => 'Peminjaman Anda telah melewati batas waktu pengembalian',
        'deadline_today' => 'Hari ini peminjaman Anda harus dikembalikan',
    ],
];
