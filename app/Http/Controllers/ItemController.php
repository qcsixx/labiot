<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    /**
     * Konstruktor untuk mengatur zona waktu
     */
    public function __construct()
    {
        // Set zona waktu default untuk controller ini
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Item::query();

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort items
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $items = $query->paginate($perPage);

        return ItemResource::collection($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemRequest $request): JsonResponse
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menambah barang',
            ], 403);
        }

        try {
            $validated = $request->validated();
            $validated['status'] = 'available';

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();

                // Store in public directory using Storage facade
                Storage::disk('public')->put('items/' . $imageName, file_get_contents($image));

                // Save the correct public URL path
                $validated['image'] = 'storage/items/' . $imageName;
            }

            $item = Item::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => __('messages.item.created'),
                'data' => new ItemResource($item),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['image']),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => __('messages.error.server_error'),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new ItemResource($item),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, Item $item): JsonResponse
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengubah barang',
            ], 403);
        }

        try {
            $validated = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image) {
                    $oldImagePath = str_replace('storage/', 'public/', $item->image);
                    Storage::delete($oldImagePath);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();

                // Store in public directory using Storage facade
                Storage::disk('public')->put('items/' . $imageName, file_get_contents($image));

                // Save the correct public URL path
                $validated['image'] = 'storage/items/' . $imageName;
            }

            $item->update($validated);

            // Refresh the item to get the updated data
            $item->refresh();

            return response()->json([
                'status' => 'success',
                'message' => __('messages.item.updated'),
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'quantity' => $item->quantity,
                    'image' => $item->image,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'item_id' => $item->id,
                'data' => $request->except(['image']),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => __('messages.error.server_error'),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item): JsonResponse
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menghapus barang',
            ], 403);
        }

        // Check if item has active borrow requests
        $activeRequests = $item->borrowRequests()->where('status', 'approved')->count();
        if ($activeRequests > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak dapat dihapus karena sedang dipinjam',
            ], 400);
        }

        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.item.deleted'),
        ]);
    }

    public function indexView(Request $request)
    {
        $query = Item::query();

        // Pencarian barang
        if ($request->has('search') && !empty($request->search)) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('category', 'like', $search);
            });
        }

        // Filter kategori
        if ($request->has('category_filter') && !empty($request->category_filter)) {
            $query->where('category', $request->category_filter);
        }

        // Urutkan barang
        $query->orderBy('name', 'asc');

        // Pagination
        $items = $query->paginate(10);

        return view('admin.manajemen-barang', compact('items'));
    }

    public function storeView(Request $request)
    {
        try {
            // Check if user is admin
            if (!Auth::guard('web_admin')->check() || Auth::guard('web_admin')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin untuk menambah barang'
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'quantity' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
            ], [
                'name.required' => 'Nama barang wajib diisi',
                'name.max' => 'Nama barang maksimal 255 karakter',
                'category.required' => 'Kategori barang wajib diisi',
                'category.max' => 'Kategori maksimal 255 karakter',
                'quantity.required' => 'Jumlah barang wajib diisi',
                'quantity.integer' => 'Jumlah barang harus berupa angka bulat',
                'quantity.min' => 'Jumlah barang minimal 0',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WebP',
                'image.max' => 'Ukuran gambar maksimal 2MB'
            ]);

            try {
                $item = new Item();
                $item->name = $request->name;
                $item->category = $request->category;
                $item->quantity = $request->quantity;
                $item->status = 'available';

                if ($request->hasFile('image')) {
                    \Log::info('Processing image upload');
                    $image = $request->file('image');

                    // Generate secure filename using hash to prevent overwrite and directory traversal
                    $extension = $image->getClientOriginalExtension();
                    $imageName = hash('sha256', time() . $image->getClientOriginalName() . uniqid()) . '.' . $extension;

                    // Store in public/items directory using Storage facade
                    $path = $image->storeAs('items', $imageName, 'public');

                    // Set proper file permissions (644 = rw-r--r--)
                    $fullPath = storage_path('app/public/items/' . $imageName);
                    if (file_exists($fullPath)) {
                        chmod($fullPath, 0644);
                    }

                    // Save just the filename to the database
                    $item->image = $imageName;
                    \Log::info('Image saved with path: ' . $path . ' and name: ' . $item->image);
                }

                $item->save();
                \Log::info('Item saved successfully', ['item_id' => $item->id]);

                DB::commit();
                \Log::info('Transaction committed successfully');

                return response()->json([
                    'status' => 'success',
                    'message' => __('messages.item.created')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error during item creation', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Error in storeView', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateView(Request $request, $id)
    {
        try {
            // Check if user is admin
            if (!Auth::guard('web_admin')->check() || Auth::guard('web_admin')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin untuk mengubah barang'
                ], 403);
            }

            $item = Item::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'quantity' => 'required|integer|min:0',
                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,webp',
                    'max:2048',
                    // Enhanced MIME type validation
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                            $fileMime = $value->getMimeType();

                            if (!in_array($fileMime, $allowedMimes)) {
                                $fail('File harus berupa gambar valid (JPEG, PNG, WebP).');
                                return;
                            }

                            // Additional check: verify file extension matches MIME type
                            $extension = strtolower($value->getClientOriginalExtension());
                            $validExtensions = ['jpeg', 'jpg', 'png', 'webp'];

                            if (!in_array($extension, $validExtensions)) {
                                $fail('Ekstensi file tidak valid.');
                                return;
                            }
                        }
                    },
                ]
            ], [
                'name.required' => 'Nama barang wajib diisi',
                'name.max' => 'Nama barang maksimal 255 karakter',
                'category.required' => 'Kategori barang wajib diisi',
                'category.max' => 'Kategori maksimal 255 karakter',
                'quantity.required' => 'Jumlah barang wajib diisi',
                'quantity.integer' => 'Jumlah barang harus berupa angka bulat',
                'quantity.min' => 'Jumlah barang minimal 0',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WebP',
                'image.max' => 'Ukuran gambar maksimal 2MB'
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image) {
                    \Log::info('Deleting old image', ['path' => 'public/items/'.$item->image]);
                    Storage::disk('public')->delete('items/'.$item->image);
                }

                $image = $request->file('image');

                // Generate secure filename using hash
                $extension = $image->getClientOriginalExtension();
                $imageName = hash('sha256', time() . $image->getClientOriginalName() . uniqid()) . '.' . $extension;

                // Store in public directory using Storage facade
                $path = $image->storeAs('items', $imageName, 'public');

                // Set proper file permissions
                $fullPath = storage_path('app/public/items/' . $imageName);
                if (file_exists($fullPath)) {
                    chmod($fullPath, 0644);
                }

                // Save just the filename to the database
                $validated['image'] = $imageName;
                \Log::info('Image path saved to DB', ['filename' => $validated['image']]);
            }

            $item->update($validated);

            // Refresh the item to get the updated data
            $item->refresh();

            return response()->json([
                'status' => 'success',
                'message' => __('messages.item.updated'),
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'quantity' => $item->quantity,
                    'image' => $item->image,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating item: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyView($id)
    {
        try {
            // Check if user is admin
            if (!Auth::guard('web_admin')->check() || Auth::guard('web_admin')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin untuk menghapus barang'
                ], 403);
            }

            $item = Item::findOrFail($id);

            // Check if item has active borrow requests
            $activeRequests = $item->borrowRequests()->where('status', 'approved')->count();
            if ($activeRequests > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Barang tidak dapat dihapus karena sedang dipinjam'
                ], 400);
            }

            // Delete image if exists
            if ($item->image) {
                \Log::info('Deleting image on item delete', ['path' => 'public/items/'.$item->image]);
                Storage::disk('public')->delete('items/'.$item->image);
            }

            $item->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('messages.item.deleted')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting item: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function setMaintenance($id)
    {
        try {
            // Check if user is admin
            if (!Auth::guard('web_admin')->check() || Auth::guard('web_admin')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin untuk mengatur status maintenance'
                ], 403);
            }

            $item = Item::findOrFail($id);

            // Cek apakah item sedang dipinjam
            $activeRequests = $item->borrowRequests()->whereIn('status', ['approved', 'pending-return'])->count();
            if ($activeRequests > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Barang sedang dipinjam dan tidak bisa dimasukkan ke maintenance'
                ], 400);
            }

            // Set status ke maintenance
            $item->status = 'maintenance';
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil dimasukkan ke dalam status maintenance',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in setMaintenance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function setAvailable($id)
    {
        try {
            // Check if user is admin
            if (!Auth::guard('web_admin')->check() || Auth::guard('web_admin')->user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin untuk mengubah status barang'
                ], 403);
            }

            $item = Item::findOrFail($id);

            // Set status ke available
            $item->status = 'available';
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil diubah statusnya menjadi tersedia',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in setAvailable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
