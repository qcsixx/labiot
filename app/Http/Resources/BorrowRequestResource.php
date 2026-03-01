<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'request_id' => $this->request_id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item', function () {
                return [
                    'item_id' => $this->item->item_id,
                    'item_name' => $this->item->item_name,
                ];
            }),
            'quantity' => $this->quantity,
            'borrow_date' => $this->borrow_date,
            'return_deadline' => $this->return_deadline,
            'status' => $this->status,
            'approval_date' => $this->approval_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
