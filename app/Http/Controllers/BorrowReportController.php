<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Enums\BorrowStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BorrowRequestsExport;

class BorrowReportController extends Controller
{
    public function index(Request $request)
    {
        // Fitur ini ditangani oleh AdminController::reportManagement()
        return redirect()->route('admin.laporan-peminjaman');
    }

    public function exportPDF(Request $request)
    {
        try {
            $query = BorrowRequest::with(['user', 'item']);

            // Filter by date range
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $query->whereBetween('borrow_date', [$startDate, $endDate]);
            }
            // Kompatibilitas dengan filter date lama jika masih digunakan
            else if ($request->filled('date')) {
                $date = $request->date;
                $query->whereDate('borrow_date', $date);
            }

            // Filter by status
            if ($request->filled('status')) {
                $status = strtolower($request->status);
                
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
            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($sq) use ($search) {
                        $sq->where('name', 'like', $search);
                    })->orWhereHas('item', function($sq) use ($search) {
                        $sq->where('name', 'like', $search);
                    });
                });
            }

            // Data dari terlama ke terbaru (ascending)
            $borrowRequests = $query->orderBy('borrow_date', 'asc')->get();
            
            $pdf = PDF::loadView('admin.laporan-peminjaman-pdf', [
                'borrowRequests' => $borrowRequests
            ]);

            $fileName = 'laporan-peminjaman-' . now()->format('Y-m-d') . '.pdf';
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $fileName = 'laporan-peminjaman';
            
            // Menambahkan informasi filter ke nama file
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = \Carbon\Carbon::parse($request->start_date)->format('Ymd');
                $endDate = \Carbon\Carbon::parse($request->end_date)->format('Ymd');
                $fileName .= '-' . $startDate . '-' . $endDate;
            }
            elseif ($request->filled('date')) {
                $fileName .= '-tanggal-' . str_replace('-', '', $request->date);
            }
            
            if ($request->filled('status')) {
                $fileName .= '-status-' . $request->status;
            }
            
            $fileName .= '-' . Carbon::now()->format('YmdHis') . '.xlsx';
            
            // Memastikan nama file aman dan tidak terlalu panjang
            $fileName = substr(preg_replace('/[^a-zA-Z0-9-_.]/', '', $fileName), 0, 100);
            
            return Excel::download(new BorrowRequestsExport($request), $fileName);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor Excel: ' . $e->getMessage());
        }
    }
} 