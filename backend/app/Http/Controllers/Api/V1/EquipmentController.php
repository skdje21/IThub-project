<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\EquipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EquipmentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $equipment = Equipment::with('assignedUser')->paginate(15);

        return EquipmentResource::collection($equipment);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Equipment::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'unique:equipment'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $equipment = Equipment::create([
            ...$data,
            'status' => !empty($data['assigned_to']) ? EquipmentStatus::Assigned : EquipmentStatus::Available,
        ]);

        return (new EquipmentResource($equipment->load('assignedUser')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        return new EquipmentResource($equipment->load('assignedUser'));
    }

    public function update(Request $request, Equipment $equipment): EquipmentResource
    {
        $this->authorize('update', $equipment);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'string', "unique:equipment,serial_number,{$equipment->id}"],
            'status' => ['sometimes', \Illuminate\Validation\Rule::enum(EquipmentStatus::class)],
        ]);

        $equipment->update($data);

        return new EquipmentResource($equipment->load('assignedUser'));
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        $this->authorize('delete', $equipment);

        $equipment->delete();

        return response()->json(null, 204);
    }

    public function assign(Request $request, Equipment $equipment): EquipmentResource
    {
        $this->authorize('assign', $equipment);

        $data = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $equipment->update([
            'assigned_to' => $data['assigned_to'],
            'status' => $data['assigned_to'] ? EquipmentStatus::Assigned : EquipmentStatus::Available,
        ]);

        return new EquipmentResource($equipment->load('assignedUser'));
    }
}
