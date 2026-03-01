<?php

namespace App\Exports;

use App\Models\BorrowRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class BorrowRequestsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithStrictNullComparison, WithCustomStartCell
{
    protected $request;
    protected $rowIndex = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Define dimana data tabel dimulai
     */
    public function startCell(): string
    {
        return 'A4';
    }

    public function collection()
    {
        $query = BorrowRequest::with(['user', 'item']);
        
        // Filter by date range
        if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
            $startDate = $this->request->start_date;
            $endDate = $this->request->end_date;
            $query->whereBetween('borrow_date', [$startDate, $endDate]);
        }
        // Kompatibilitas dengan filter date lama jika masih digunakan
        else if ($this->request->filled('date')) {
            $date = $this->request->date;
            $query->whereDate('borrow_date', $date);
        }

        // Filter by status
        if ($this->request->filled('status')) {
            $status = strtolower($this->request->status);
            
            // Filter untuk status gabungan
            if ($status === 'dipinjam') {
                $query->where(function($q) {
                    $q->whereIn('status', ['approved', 'borrowed', 'pending-return', 'overdue']);
                });
            } 
            // Filter untuk status spesifik
            elseif ($status === 'dikembalikan') {
                $query->where(function($q) {
                    $q->where('status', 'completed');
                });
            }
            // Filter untuk status lainnya
            else {
                $query->where('status', $status);
            }
        }

        // Filter by search term
        if ($this->request->filled('search')) {
            $search = '%' . $this->request->search . '%';
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($sq) use ($search) {
                    $sq->where('name', 'like', $search);
                })->orWhereHas('item', function($sq) use ($search) {
                    $sq->where('name', 'like', $search);
                });
            });
        }
        
        // Mengurutkan data dari terlama ke terbaru
        $result = $query->orderBy('borrow_date', 'asc')->get();
        $this->rowIndex = $result->count();
        return $result;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Peminjam',
            'Barang',
            'Jumlah',
            'Tanggal Pinjam',
            'Tanggal Kembali',
            'Tujuan Peminjaman',
            'Status',
            'Catatan Admin'
        ];
    }

    public function map($request): array
    {
        static $index = 0;
        $index++;
        
        return [
            $index,
            $request->user->name ?? 'Unknown',
            $request->item->name ?? 'Unknown',
            $request->quantity ?? '1',
            $request->borrow_date ? $request->borrow_date->format('d/m/Y') : 'N/A',
            $request->return_deadline ? $request->return_deadline->format('d/m/Y') : 'N/A',
            $request->purpose ?? '-',
            $request->getStatusLabel(),
            $request->notes ?? '-'  // Menggunakan kolom 'notes' untuk catatan admin
        ];
    }
    
    /**
     * @return string
     */
    public function title(): string
    {
        $title = 'Laporan Peminjaman';
        
        if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
            $title .= ' - ' . $this->request->start_date . ' s/d ' . $this->request->end_date;
        }
        elseif ($this->request->filled('date')) {
            $title .= ' - ' . $this->request->date;
        }
        
        if ($this->request->filled('status')) {
            $title .= ' - ' . ucfirst($this->request->status);
        }
        
        return $title;
    }
    
    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Menambah judul di atas tabel
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'LAPORAN PEMINJAMAN BARANG LAB IOT VOKASI UB');
        
        // Tanggal cetak
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Dicetak pada: ' . now()->format('d/m/Y H:i:s'));
        
        // Filter yang digunakan
        $filterText = 'Filter: ';
        if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($this->request->start_date)->format('d/m/Y');
            $endDate = \Carbon\Carbon::parse($this->request->end_date)->format('d/m/Y');
            $filterText .= 'Tanggal: ' . $startDate . ' s/d ' . $endDate . ', ';
        }
        elseif ($this->request->filled('date')) {
            $filterText .= 'Tanggal: ' . $this->request->date . ', ';
        }
        
        if ($this->request->filled('status')) {
            $filterText .= 'Status: ' . ucfirst($this->request->status) . ', ';
        }
        
        if ($this->request->filled('search')) {
            $filterText .= 'Pencarian: ' . $this->request->search . ', ';
        }
        
        if ($filterText == 'Filter: ') {
            $filterText .= 'Semua Data';
        } else {
            $filterText = rtrim($filterText, ', ');
        }
        
        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', $filterText);
        
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(25); // Header row height
        
        // Style untuk header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F4C81'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        
        // Style untuk judul laporan
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '0F4C81'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        // Style untuk info laporan (tanggal cetak dan filter)
        $infoStyle = [
            'font' => [
                'size' => 10,
                'color' => ['rgb' => '5A5A5A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        // Style untuk data dalam tabel
        $bodyStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        // Style khusus untuk kolom nomor (kolom A)
        $centerStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        
        // Style khusus untuk kolom status
        $statusStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ];
        
        // Apply styles
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $sheet->getStyle('A2:A3')->applyFromArray($infoStyle);
        
        // Style untuk baris header (baris ke-4)
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);
        
        // Apply style untuk seluruh data
        $lastRow = $this->rowIndex + 4; // +4 karena ada 3 baris header + 1 baris judul kolom
        if ($lastRow > 4) {
            // Style dasar untuk semua sel data
            $sheet->getStyle('A5:I' . $lastRow)->applyFromArray($bodyStyle);
            
            // Center align kolom nomor
            $sheet->getStyle('A5:A' . $lastRow)->applyFromArray($centerStyle);
            
            // Style kolom jumlah
            $sheet->getStyle('D5:D' . $lastRow)->applyFromArray($centerStyle);
            
            // Style kolom tanggal
            $sheet->getStyle('E5:F' . $lastRow)->applyFromArray($centerStyle);
            
            // Style kolom status
            $sheet->getStyle('H5:H' . $lastRow)->applyFromArray($statusStyle);
            
            // Memberikan warna zebra-striping untuk baris
            for ($row = 5; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                          ->setFillType(Fill::FILL_SOLID)
                          ->getStartColor()->setRGB('F9F9F9');
                }
                
                // Set warna status berdasarkan nilai status
                $statusValue = $sheet->getCell('H' . $row)->getValue();
                $statusColor = $this->getStatusColor($statusValue);
                
                if ($statusColor) {
                    // Jika ada warna yang sesuai, terapkan ke sel
                    $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB($statusColor);
                }
            }
            
            // Set wrap text untuk kolom tujuan dan catatan
            $sheet->getStyle('G5:I' . $lastRow)->getAlignment()->setWrapText(true);
        }
        
        return [];
    }
    
    /**
     * Get color code for status
     * 
     * @param string $status
     * @return string|null
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'Menunggu Persetujuan' => 'F59E0B', // Orange
            'Disetujui' => '3B82F6',           // Blue
            'Sedang Dipinjam' => '8B5CF6',      // Purple
            'Ditolak' => 'EF4444',             // Red
            'Pengajuan Pengembalian' => 'FDB813', // Yellow
            'Terlambat Dikembalikan', 'Melewati Deadline' => 'F44336', // Red
            'Selesai' => '10B981',              // Green
            'Dibatalkan' => '6B7280',           // Gray
            'Semua Dipinjam' => 'C026D3',       // Magenta
            'Semua Dikembalikan' => '14B8A6',   // Teal
            default => null,
        };
    }
} 