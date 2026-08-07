<?php

namespace App\Http\Resources;

use App\Models\Cabinet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cabinet
 */
class CabinetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'specialization' => $this->specialization,
            'wilaya' => [
                'code' => $this->wilaya_code,
                'name' => $this->wilaya_name,
            ],
        ];
    }
}
