<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Resource;
use App\Models\ResourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $property = Property::selectRaw('id, ownerId, propertyName, address,address2, latitude, longitude, email, country, county, city, postcode, phone, telephone, arrivalTime, departureTime, status, isIcal')->where('ownerId', Auth::id())->with('facilities');
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
            $pagination     = $request->pagination ?? 10;
            $orderBy        = $request->orderBy ?? 'id';
            $orderDirection = $request->orderDirection ?? 'asc';
            $property       = $property->orderBy($orderBy, $orderDirection)->paginate($pagination);
            $property->each(function ($item) {
                $item->facility=$item->facilities->pluck('id')->toArray();
                unset($item->facilities);
                return $item;
            });
            $response       = ['status' => true, 'message' => '', 'data' => $property];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
    public function propertyDropdown()
    {
        try {
            $property = Property::selectRaw('id, ownerId, propertyName')->where('ownerId', Auth::id());
            $property = $property->get()->transform(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->propertyName,
                ];
            });
            $response = ['status' => true, 'message' => '', 'data' => $property];

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyName'  => 'required',
                    'address'       => 'required',
                    'latitude'      => 'required',
                    'longitude'     => 'required',
                    'email'         => 'nullable|email',
                    'country'       => 'nullable|string',
                    'county'        => 'nullable|string',
                    'city'          => 'nullable|string',
                    'postcode'      => 'nullable|string',
                    'phone'         => 'nullable|string',
                    'telephone'     => 'nullable|string',
                    'arrivalTime'   => 'nullable|date',
                    'departureTime' => 'nullable|date',
                    'status'        => 'nullable|boolean',
                    'type'          => 'nullable|in:room,tour,activity',
                    'isIcal'        => 'nullable|boolean',
                    'slug'          => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:property,slug'],
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            $property                = new Property();
            $property->ownerId       = Auth::id();
            $property->propertyName  = $request->propertyName;
            $property->email         = $request->email;
            $property->address       = $request->address;
            $property->address2      = $request->address2;
            $property->slug          = $request->slug;
            $property->country       = $request->country;
            $property->county        = $request->county;
            $property->city          = $request->city;
            $property->postcode      = $request->postcode;
            $property->phone         = $request->phone;
            $property->telephone     = $request->telephone;
            $property->latitude      = $request->latitude;
            $property->longitude     = $request->longitude;
            $property->arrivalTime   = $request->arrivalTime ? date('H:i', strtotime($request->arrivalTime)) : null;
            $property->departureTime = $request->departureTime ? date('H:i', strtotime($request->departureTime)) : null;
            $property->status        = $request->status ?? 0;
            if (isset($request->type)) {
                $property->type      = $request->type;
            }
            $property->isIcal        = $request->isIcal ?? 0;
            $property->slug          = $request->slug ?: Str::slug($request->name);
            if ($property->save()) {
                $response = ['status' => true, 'message' => 'Property added successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property addition failed', 'data' => ''];
            }

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function show(string $slug)
    {
        //
        try {
            $property = Property::where('slug', $slug)->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $response = ['status' => true, 'message' => '', 'data' => $property];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyName'  => 'required',
                    'address'       => 'required',
                    'latitude'      => 'required',
                    'longitude'     => 'required',
                    'email'         => 'nullable|email',
                    'country'       => 'nullable|string',
                    'county'        => 'nullable|string',
                    'city'          => 'nullable|string',
                    'postcode'      => 'nullable|string',
                    'phone'         => 'nullable|string',
                    'telephone'     => 'nullable|string',
                    'arrivalTime'   => 'nullable|time',
                    'departureTime' => 'nullable|time',
                    'status'        => 'nullable|boolean',
                    'isIcal'        => 'nullable|boolean',
                    'type'          => 'nullable|in:room,tour,activity',
                    'slug'          => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:property,slug'],
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            $property = Property::where('id', $id)->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $property->propertyName  = $request->propertyName;
            $property->email         = $request->email;
            $property->address       = $request->address;
            $property->address2      = $request->address2;
            $property->country       = $request->country;
            $property->county        = $request->county;
            $property->city          = $request->city;
            $property->postcode      = $request->postcode;
            $property->phone         = $request->phone;
            $property->telephone     = $request->telephone;
            $property->latitude      = $request->latitude;
            $property->longitude     = $request->longitude;
            $property->arrivalTime   = $request->arrivalTime ? date('H:i', strtotime($request->arrivalTime)) : null;
            $property->departureTime = $request->departureTime ? date('H:i', strtotime($request->departureTime)) : null;
            $property->status        = $request->status ?? 0;
            if (isset($request->type)) {
                $property->type      = $request->type;
            }
            $property->isIcal        = $request->isIcal ?? 0;
            $property->slug          = $request->slug ?: Str::slug($request->name);

            if ($property->save()) {
                $response = ['status' => true, 'message' => 'Property updated successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property addition failed', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function destroy(string $id)
    {
        try {
            $property = Property::where('id', $id)->where('ownerId', Auth::id())->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $propertyImages = PropertyImage::where('propertyId', $id)->pluck('image');
            foreach ($propertyImages as $image) {
                $oldImagePath = public_path('storage/property/images/' . $image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $propertyImages = PropertyImage::where('propertyId', $id)->delete();
            $resourceTypes  = ResourceType::where('propertyId', $id)->pluck('id');
            $resources      = Resource::whereIn('resourceTypeId', $resourceTypes)->delete();
            $resourceTypes  = ResourceType::where('propertyId', $id)->delete();
            if ($property->delete()) {
                $response = ['status' => true, 'message' => 'Property deleted successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property deletion failed', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function addMultipleImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyId' => 'required|exists:property,id',
                    'images'     => 'required|array',
                    'images.*'   => 'required|image|mimes:jpeg,png,jpg|max:5000',
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            $property = Property::where('id', $request->propertyId)->where('ownerId', Auth::id())->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $images   = $request->images;
            $position = PropertyImage::where('propertyId', $property->id)->max('position') + 1 ?? 1;
            foreach ($images as $image) {
                $path = public_path('storage/property/images/');
                if (! file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $imageName = time() . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                $image->move($path, $imageName);
                $image             = new PropertyImage;
                $image->propertyId = $property->id;
                $image->image      = $imageName;
                $image->position   = $position++;
                $image->save();
            }
            $response = ['status' => true, 'message' => 'Property images added successfully', 'data' => ''];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function updateImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'images'  => 'required|image|mimes:jpeg,png,jpg|max:5000',
                    'imageId' => 'required|exists:property_image,id',
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            $newImageFile = $request->file('images');
            $imageRecord  = PropertyImage::where('id', $request->imageId)->first();
            if (empty($imageRecord)) {
                return response()->json(['status' => false, 'message' => 'Image not found', 'data' => '']);
            }
            $path = public_path('storage/property/images/');
            if (! file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $oldImagePath = $path . $imageRecord->image;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            $newImageName = time() . rand(1000, 9999) . '.' . $newImageFile->getClientOriginalExtension();
            $newImageFile->move($path, $newImageName);
            $imageRecord->image = $newImageName;
            $imageRecord->save();
            $response = ['status' => true, 'message' => 'Property images updated successfully', 'data' => ''];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function deleteImage(Request $request)
    {
        // Delete single image
        $validator = Validator::make($request->all(),
            [
                'id'         => 'required|string',
                'propertyId' => 'required|exists:property,id',
            ]
        );
        if ($validator->fails()) {
            return $response = ['status' => false, 'message' => $validator->messages()->first()];
        }
        $image = PropertyImage::where('id', $request->id)->where('propertyId', $request->propertyId)->first();
        if (empty($image)) {
            return response()->json(['status' => false, 'message' => 'Image not found', 'data' => '']);
        }
        // Delete old image file
        $oldImagePath = public_path('storage/property/images/' . $image->image);
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
        $image->delete();
        $response = ['status' => true, 'message' => 'Property image deleted successfully', 'data' => ''];
        return response()->json($response);

    }
    public function deleteMultipleImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'ids'   => 'required|array',
                    'ids.*' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            // $property = Property::where('id', $request->propertyId)->where('ownerId', Auth::id())->first();
            // if (empty($property)) {
            //     return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            // }
            $ids = $request->ids;
            foreach ($ids as $id) {
                $image = PropertyImage::where('id', $id)->first();
                if (empty($image)) {
                    continue;
                } else {
                    $oldImagePath = public_path('storage/property/images/' . $image->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $image->delete();
            }
            $response = ['status' => true, 'message' => 'Property images deleted successfully', 'data' => ''];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function changeImagePosition(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyId' => 'required|exists:property,id',
                    'ids'        => 'required|array',
                    'ids.*'      => 'required|exists:property_image,id',
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            $property = Property::where('id', $request->propertyId)->where('ownerId', Auth::id())->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $ids      = $request->ids;
            $position = 1;
            foreach ($ids as $id) {
                $image = PropertyImage::where('id', $id)->where('propertyId', $property->id)->first();
                if (empty($image)) {
                    continue;
                } else {
                    $image->position = $position++;
                    $image->save();
                }
            }
            $response = ['status' => true, 'message' => 'Property images position changed successfully', 'data' => ''];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function propertyWiseImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyId' => 'required|exists:property,id',
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            $property = Property::where('id', $request->propertyId)->where('ownerId', Auth::id())->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $images = PropertyImage::selectRaw('id, propertyId, image, position')->where('propertyId', $property->id)->orderBy('position', 'asc')->get()->transform(function ($item) {
                $item->image = asset('storage/property/images/' . $item->image);
                return $item;
            });
            $response = ['status' => true, 'message' => 'Property images fetched successfully', 'data' => $images];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
}
