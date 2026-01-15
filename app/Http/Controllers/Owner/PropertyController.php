<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Resource;
use App\Models\ResourceType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;
use Validator;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $property = Property::selectRaw('id, ownerId, propertyName, address,address2, latitude, longitude, email, country, county, city, postcode, phone, telephone, arrivalTime, departureTime, status, isIcal')->where('ownerId', Auth::id());
            if ($request->search) {
                $property->where(function ($query) use ($request) {
                    $query->where('email', 'like', '%' . $request->search . '%')
                        ->orWhere('propertyName', 'like', '%' . $request->search . '%')
                        ->orWhere('country', 'like', '%' . $request->search . '%')
                        ->orWhere('county', 'like', '%' . $request->search . '%')
                        ->orWhere('city', 'like', '%' . $request->search . '%')
                        ->orWhere('postcode', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('address', 'like', '%' . $request->search . '%')
                        ->orWhere('address2', 'like', '%' . $request->search . '%')
                        ->orWhere('telephone', 'like', '%' . $request->search . '%')
                        ->orWhere('arrivalTime', 'like', '%' . $request->search . '%')
                        ->orWhere('departureTime', 'like', '%' . $request->search . '%');
                });
            }
            $pagination = $request->pagination ?? 10;
            $orderBy = $request->orderBy ?? 'id';
            $orderDirection = $request->orderDirection ?? 'asc';
            $property = $property->orderBy($orderBy, $orderDirection)->paginate($pagination);
            $response = ['status' => true, 'message' => '', 'data' => $property];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function propertyDropdown(): JsonResponse
    {
        try {
            $property = Property::selectRaw('id, ownerId, propertyName')->where('status', 1)->where('ownerId', Auth::id());
            $property = $property->get()->transform(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->propertyName,
                ];
            });
            $response = ['status' => true, 'message' => '', 'data' => $property];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'propertyName'    => 'required',
                    'address'         => 'required',
                    'latitude'        => 'required',
                    'longitude'       => 'required',
                    'email'           => 'nullable|email',
                    'country'         => 'nullable|string',
                    'county'          => 'nullable|string',
                    'city'            => 'nullable|string',
                    'postcode'        => 'nullable|string',
                    'phone'           => 'nullable|string',
                    'telephone'       => 'nullable|string',
                    'arrivalTime'     => 'nullable|date',
                    'departureTime'   => 'nullable|date',
                    'status'          => 'nullable|boolean',
                    'isIcal'          => 'nullable|boolean',
                    'description'     => 'nullable',
                    'slug'            => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:property,slug'],
                    'stripePublicKey' => 'nullable|string',
                    'stripeSecretKey' => 'nullable|string',
                ],
            );
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->messages()->first(),
                    'data'    => '',
                ], 422);
            }
            $property = new Property;
            $property->ownerId = Auth::id();
            $property->propertyName = $request->propertyName;
            $property->email = $request->email;
            $property->address = $request->address;
            $property->address2 = $request->address2;
            $property->slug = $request->slug;
            $property->country = $request->country;
            $property->description = $request->description;
            $property->county = $request->county;
            $property->city = $request->city;
            $property->postcode = $request->postcode;
            $property->phone = $request->phone;
            $property->telephone = $request->telephone;
            $property->latitude = $request->latitude;
            $property->longitude = $request->longitude;
            $property->arrivalTime = $request->arrivalTime ? date('H:i', strtotime($request->arrivalTime)) : null;
            $property->departureTime = $request->departureTime ? date('H:i', strtotime($request->departureTime)) : null;
            $property->status = $request->status ?? 0;
            $property->isIcal = $request->isIcal ?? 0;
            $property->slug = $request->slug ?: Str::slug($request->name);
            $property->stripePublicKey = $request->stripePublicKey ?? null;
            $property->stripeSecretKey = $request->stripeSecretKey ?? null;
            if ($property->save()) {
                $response = ['status' => true, 'message' => 'Property added successfully', 'data' => $property->id, 201];
            } else {
                $response = ['status' => false, 'message' => 'Property addition failed', 'data' => '', 500];
            }

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => ''], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $property = Property::where('id', $id)->with('facilities')->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => ''], 404);
            }
            $response = ['status' => true, 'message' => '', 'data' => $property];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'propertyName'    => 'required',
                    'address'         => 'required',
                    'latitude'        => 'required',
                    'longitude'       => 'required',
                    'email'           => 'nullable|email',
                    'country'         => 'nullable|string',
                    'county'          => 'nullable|string',
                    'city'            => 'nullable|string',
                    'postcode'        => 'nullable|string',
                    'phone'           => 'nullable|string',
                    'telephone'       => 'nullable|string',
                    'arrivalTime'     => 'nullable|time',
                    'description'     => 'nullable',
                    'departureTime'   => 'nullable|time',
                    'status'          => 'nullable|boolean',
                    'isIcal'          => 'nullable|boolean',
                    'slug'            => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
                    'stripePublicKey' => 'nullable|string',
                    'stripeSecretKey' => 'nullable|string',
                ],
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->first(), 'data' => '']);
            }
            $property = Property::where('id', $id)->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => ''], 404);
            }
            if ($property->propertyName != $request->propertyName) {
                $property->propertyName = $request->propertyName;
            }
            $property->email = $request->email;
            $property->address = $request->address;
            $property->address2 = $request->address2;
            $property->country = $request->country;
            $property->county = $request->county;
            $property->city = $request->city;
            $property->postcode = $request->postcode;
            $property->phone = $request->phone;
            $property->telephone = $request->telephone;
            $property->latitude = $request->latitude;
            $property->longitude = $request->longitude;
            $property->arrivalTime = $request->arrivalTime ? date('H:i', strtotime($request->arrivalTime)) : null;
            $property->departureTime = $request->departureTime ? date('H:i', strtotime($request->departureTime)) : null;
            $property->status = $request->status ?? 0;
            $property->description = $request->description;
            $property->isIcal = $request->isIcal ?? 0;
            $property->slug = $request->slug ?: Str::slug($request->name);
            $property->stripePublicKey = $request->stripePublicKey ?? null;
            $property->stripeSecretKey = $request->stripeSecretKey ?? null;
            if ($property->save()) {
                $response = ['status' => true, 'message' => 'Property updated successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property addition failed', 'data' => ''];
            }

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $property = Property::where('id', $id)->where('ownerId', Auth::id())->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => ''], 404);
            }
            $resourceTypes = ResourceType::where('propertyId', $id)->pluck('id');
            $resources = Resource::whereIn('resourceTypeId', $resourceTypes)->delete();
            $resourceTypes = ResourceType::where('propertyId', $id)->delete();
            if ($property->delete()) {
                $response = ['status' => true, 'message' => 'Property deleted successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property deletion failed', 'data' => ''];
            }

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function addMultipleImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'propertyId' => 'required',
                    'images'     => 'required|array',
                ],
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->first(), 'data' => '']);
            }
            $property = Property::where('id', $request->propertyId)->where('ownerId', Auth::id())->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => ''], 404);
            }
            $images = $request->images;
            $imagePosition = PropertyImage::where('propertyId', $property->id)->count() + 1;
            $failedImages = [];
            foreach ($images as $image) {
                if (! $image->isValid()) {
                    $failedImages[] = $image->getClientOriginalName();

                    continue;
                }
                $imageName = time() . rand(1, 1000) . '.' . $image->getClientOriginalExtension();

                try {
                    $image->move(public_path('storage/property/images'), $imageName);
                    $propertyImage = new PropertyImage;
                    $propertyImage->propertyId = $property->id;
                    $propertyImage->position = $imagePosition++;
                    $propertyImage->image = $imageName;
                    $propertyImage->save();
                } catch (Exception $e) {
                    $failedImages[] = $image->getClientOriginalName();
                }
            }
            if (count($failedImages) > 0) {
                $response = [
                    'status'  => false,
                    'message' => 'Some images failed to upload: ' . implode(', ', $failedImages),
                    'data'    => '',
                ];
            } else {
                $response = ['status' => true, 'message' => 'Property images added successfully', 'data' => ''];
            }

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function updateImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'imageId' => 'required',
                    'image'   => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->first(), 'data' => '']);
            }
            $propertyImage = PropertyImage::where('id', $request->imageId)->first();
            if (empty($propertyImage)) {
                return response()->json(['status' => false, 'message' => 'Property image not found', 'data' => ''], 404);
            }
            $oldPath = public_path('storage/property/images/' . $propertyImage->image);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $image = $request->image;
            $imageName = time() . rand(1, 1000) . '.' . $image->getClientOriginalExtension();

            try {
                $image->move(public_path('storage/property/images'), $imageName);
                $propertyImage->image = $imageName;
                $propertyImage->save();
                $response = ['status' => true, 'message' => 'Property image updated successfully', 'data' => ''];
            } catch (Exception $e) {
                $response = ['status' => false, 'message' => 'Property image update failed', 'data' => ''];
            }

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => ''], 500);
        }
    }

    public function deleteImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                ],
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->first(), 'data' => '']);
            }
            $propertyImage = PropertyImage::where('id', $request->id)->first();
            if (empty($propertyImage)) {
                return response()->json(['status' => false, 'message' => 'Property image not found', 'data' => ''], 404);
            }
            $oldPath = public_path('storage/property/images/' . $propertyImage->image);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            if ($propertyImage->delete()) {
                $response = ['status' => true, 'message' => 'Property image deleted successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property image delete failed', 'data' => ''];
            }

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => ''], 500);
        }
    }

    public function deleteMultipleImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'ids' => 'required|array',
                ],
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->first(), 'data' => '']);
            }
            $ids = $request->ids;
            $propertyImages = PropertyImage::whereIn('id', $ids)->get();
            if ($propertyImages->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'Property images not found', 'data' => ''], 404);
            }
            foreach ($propertyImages as $propertyImage) {
                $oldPath = public_path('storage/property/images/' . $propertyImage->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $propertyImage->delete();
            }
            $response = ['status' => true, 'message' => 'Property images deleted successfully', 'data' => ''];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => ''], 500);
        }
    }

    public function changeImagePosition(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'ids' => 'required|array',
                ],
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->first(), 'data' => '']);
            }
            $ids = $request->ids;
            foreach ($ids as $key => $id) {
                $propertyImage = PropertyImage::where('id', $id)->first();
                if (empty($propertyImage)) {
                    return response()->json(['status' => false, 'message' => 'Property image not found', 'data' => ''], 404);
                }
                $propertyImage->position = $key + 1;
                $propertyImage->save();
            }
            $response = ['status' => true, 'message' => '', 'data' => ''];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => ''], 500);
        }
    }

    public function propertyWiseImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'propertyId' => 'required',
                ],
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->first(), 'data' => '']);
            }
            $propertyImages = PropertyImage::where('propertyId', $request->propertyId)->orderBy('position', 'asc')->get();
            if ($propertyImages->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'Property images not found', 'data' => ''], 404);
            }
            $response = ['status' => true, 'message' => '', 'data' => $propertyImages];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => ''], 500);
        }
    }
}
