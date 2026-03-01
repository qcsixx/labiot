<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'borrow_request' => $this->whenLoaded('borrowRequest', function () {
                return [
                    'request_id' => $this->borrowRequest->request_id,
                    'item_name' => $this->borrowRequest->item->name ?? null,
                    'status' => $this->borrowRequest->status,
                ];
            }),
            'message' => $this->message,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
