<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #0F4C81;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            padding: 0;
            color: #0F4C81;
        }
        .header p {
            margin: 5px 0;
            color: #707070;
        }
        .filters {
            margin-bottom: 20px;
            font-size: 12px;
        }
        .filters p {
            margin: 5px 0;
        }
        .footer {
            font-size: 10px;
            color: #707070;
            text-align: right;
            margin-top: 20px;
        }
        
        /* Status Badge Styles */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
        }
        .status-pending {
            color: #F59E0B;
        }
        .status-approved {
            color: #3B82F6;
        }
        .status-borrowed {
            color: #8B5CF6;
        }
        .status-rejected {
            color: #EF4444;
        }
        .status-pending-return {
            color: #FDB813;
        }
        .status-late-return, .status-overdue {
            color: #F44336;
        }
        .status-completed {
            color: #10B981;
        }
        .status-cancelled {
            color: #6B7280;
        }
        .status-dipinjam {
            color: #C026D3;
        }
        .status-dikembalikan {
            color: #14B8A6;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Peminjaman Barang</h1>
        <p>Lab IoT Vokasi UB</p>
    </div>

    <div class="filters">
        <p><strong>Tanggal:</strong> 
            @if(request('start_date') && request('end_date'))
                {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
            @elseif(request('date'))
                {{ request('date') }}
            @else
                Semua Tanggal
            @endif
        </p>
        <p><strong>Status:</strong> {{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}</p>
        <p><strong>Filter Pencarian:</strong> {{ request('search') ?? '-' }}</p>
        <p><strong>Dicetak pada:</strong> {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Peminjam</th>
                <th>Barang</th>
                <th>Jumlah</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Tujuan Peminjaman</th>
                <th style="text-align: center;">Status</th>
                <th>Catatan Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($borrowRequests as $index => $request)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $request->user->name ?? 'Unknown' }}</td>
                    <td>{{ $request->item->name ?? 'Unknown' }}</td>
                    <td>{{ $request->quantity ?? '1' }}</td>
                    <td>{{ $request->borrow_date ? $request->borrow_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $request->return_deadline ? $request->return_deadline->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $request->purpose ?? '-' }}</td>
                    <td style="text-align: center;">
                        <div class="status-badge status-{{ is_string($request->status) ? $request->status : $request->status->value }}">
                            {{ $request->getStatusLabel() }}
                        </div>
                    </td>
                    <td>{{ $request->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data peminjaman</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan secara otomatis oleh Sistem Manajemen Lab IoT Vokasi UB.</p>
    </div>
</body>
</html> 