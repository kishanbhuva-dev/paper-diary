<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->search;
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'asc';
            $totalRevenueQuery = '(SELECT COALESCE(SUM(price), 0) FROM `booking_orders` WHERE propertyId = property.id AND status = "confirm")';
            $lostAmountQuery = '(SELECT COALESCE(SUM(price), 0) FROM `booking_orders` WHERE propertyId = property.id AND status != "confirm")';
            $query = Property::join('users', 'property.ownerId', '=', 'users.id')
                ->selectRaw("
                property.id,
                property.ownerId,
                property.propertyName,
                property.status,
                CONCAT(users.firstName, ' ', users.lastName) as ownerName,
                users.email as ownerEmail,
                users.phone as ownerPhone,
                users.telephone as ownerTelephone,
                {$totalRevenueQuery} as totalRevenue,
                {$lostAmountQuery} as lostAmount
            ");
            if ($search) {
                $query->where(function ($q) use ($search, $totalRevenueQuery, $lostAmountQuery) {
                    $q->where('users.firstName', 'LIKE', "%{$search}%")
                        ->orWhere('users.lastName', 'LIKE', "%{$search}%")
                        ->orWhere('users.email', 'LIKE', "%{$search}%")
                        ->orWhere('users.phone', 'LIKE', "%{$search}%")
                        ->orWhere('users.telephone', 'LIKE', "%{$search}%")
                        ->orWhere('property.propertyName', 'LIKE', "%{$search}%")
                        ->orWhereRaw("{$totalRevenueQuery} LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("{$lostAmountQuery} LIKE ?", ["%{$search}%"]);
                });
            }

            if ($sortBy === 'ownerName') {
                $query->orderByRaw("CONCAT(users.firstName, ' ', users.lastName) {$sortOrder}");
            } elseif (in_array($sortBy, ['ownerEmail', 'ownerPhone', 'ownerTelephone'])) {
                $columnMap = [
                    'ownerEmail'     => 'users.email',
                    'ownerPhone'     => 'users.phone',
                    'ownerTelephone' => 'users.telephone',
                ];
                $query->orderBy($columnMap[$sortBy], $sortOrder);
            } elseif (in_array($sortBy, ['totalRevenue', 'lostAmount'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy("property.{$sortBy}", $sortOrder);
            }

            $property = $query->paginate($perPage);

            return response()->json([
                'status'  => true,
                'message' => '',
                'data'    => $property,
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage(),
                'data'    => [],
            ]);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id'     => 'required|exists:property,id',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }
            $property = Property::where('id', $id)->first();
            $property->status = $request->status;
            $property->save();

            return response()->json(['status' => true, 'message' => 'Property status changed successfully', 'data' => $property]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
}
