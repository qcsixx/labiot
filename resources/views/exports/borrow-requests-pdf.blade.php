<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #0F4C81;
        }
        .header p {
            margin: 5px 0;
            color: #707070;
        }
        .filters {
            margin-bottom: 20px;
            font-size: 14px;
        }
        .filters p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #0F4C81;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            font-size: 12px;
            color: #707070;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Peminjaman Barang</h1>
        <p>Lab IoT Vokasi UB</p>
    </div>

    <div class="filters">
        <p><strong>Tanggal:</strong> {{ $date }}</p>
        <p><strong>Status:</strong> {{ $status }}</p>
        <p><strong>Filter Nama:</strong> {{ $search }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Peminjam</th>
                <th>Nama Barang</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($borrowRequests as $index => $request)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->item->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->borrow_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->return_deadline)->format('d/m/Y') }}</td>
                    <td>{{ $request->getStatusLabel() }}</td>
                    <td>{{ $request->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data peminjaman</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $generated_at }}</p>
    </div>
</body>
</html> 