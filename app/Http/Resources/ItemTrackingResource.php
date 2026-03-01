<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->tracking_id,
            'borrow_request_id' => $this->borrow_request_id,
            'borrow_request' => $this->whenLoaded('borrowRequest', function () {
                return [
                    'request_id' => $this->borrowRequest->request_id,
                    'item_name' => $this->borrowRequest->item->name ?? null,
                    'status' => $this->borrowRequest->status,
                ];
            }),
            'photo' => $this->photo,
            'location' => $this->location,
            'notes' => $this->notes,
            'tracked_by' => $this->tracked_by,
            'user' => $this->whenLoaded('reportedBy', function () {
                return [
                    'id' => $this->reportedBy->id,
                    'name' => $this->reportedBy->name,
                ];
            }),
            'tracking_date' => $this->tracking_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
