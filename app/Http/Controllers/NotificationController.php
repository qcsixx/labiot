<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\BorrowRequest;
use App\Models\Notification;
use App\Enums\BorrowStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Get borrow requests for the current user or all if admin
        $borrowRequestIds = [];
        
        if (Auth::user()->role === 'admin') {
            $borrowRequestIds = BorrowRequest::pluck('request_id')->toArray();
        } else {
            $borrowRequestIds = BorrowRequest::where('user_id', Auth::id())->pluck('request_id')->toArray();
        }
        
        // Get notifications for those borrow requests
        $query = Notification::with('borrowRequest.item')
            ->where(function($query) use ($borrowRequestIds) {
                $query->whereIn('request_id', $borrowRequestIds);
            })
            ->latest();
        
        // Filter untuk hanya menampilkan notifikasi dari peminjaman yang belum selesai
        $query->whereHas('borrowRequest', function($query) {
            $query->where('status', '!=', BorrowStatus::COMPLETED->value);
        });
        
        $notifications = $query->paginate(10);
        
        return NotificationResource::collection($notifications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi request
        $request->validate([
            'request_id' => 'required|exists:borrow_requests,request_id',
            'message' => 'required|string',
            'status' => 'required|string'
        ]);
        
        // Get borrowRequest untuk mendapatkan user_id
        $borrowRequest = BorrowRequest::findOrFail($request->request_id);
        
        // Buat notifikasi
        $notification = Notification::create([
            'user_id' => $borrowRequest->user_id,
            'message' => $request->message,
            'status' => $request->status,
            'request_id' => $borrowRequest->request_id
        ]);
        
        return response()->json([
            'message' => 'Notifikasi berhasil dibuat',
            'data' => new NotificationResource($notification)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification): JsonResponse
    {
        // Authorize akses
        $this->authorize('view', $notification);
        
        return response()->json([
            'data' => new NotificationResource($notification)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Authorize akses
        $this->authorize('delete', $notification);
            
        $notification->delete();
        
        return response()->json([
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}
