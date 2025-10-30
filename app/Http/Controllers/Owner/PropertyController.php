<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Property;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $property = Property::where('ownerId',Auth::id())->get();
            $response = ['status'=>true,'message'=>'','data'=>$property];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>[]]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyName'  =>'required',
                    'address'=>'required',
                    'latitude'=>'required',
                    'longitude'=>'required'
                ]
            );
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $property = new Property();
            $property->propertyName = $request->propertyName;
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
            $property->arrivalDate = $request->arrivalDate;
            $property->departureDate = $request->departureDate;
            $property->status = $request->status;
            $property->propertyType = $request->propertyType;
            $property->notes = $request->notes;
            $property->specialInfo = $request->specialInfo;
            $property->description = $request->description;
            $property->visitorCount = $request->visitorCount;
            $property->closeStartDate = $request->closeStartDate;
            $property->closeEndDate = $request->closeEndDate;
            $property->openAllYear = $request->openAllYear;
            $property->openingStart = $request->openingStart;
            $property->openingEnd = $request->openingEnd;
            $property->facebookLink = $request->facebookLink;
            $property->instagramLink = $request->instagramLink;
            $property->youtubeLink = $request->youtubeLink;
            $property->ownerId = Auth::id();
            if($property->save()){
                $response = ['status'=>true,'message'=>'Property added successfully','data'=>''];
            }else{
                $response = ['status'=>false,'message'=>'Property addition failed','data'=>''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>'']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyName'  =>'required',
                    'address'=>'required',
                    'latitude'=>'required',
                    'longitude'=>'required'
                ]
            );
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }
            $property = Property::where('id',$id)->first();
            if (empty($property)) {
                return response()->json(['status'=>false,'message'=>'Property not found','data'=>'']);
            }
            $property->propertyName = $request->propertyName;
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
            $property->arrivalDate = $request->arrivalDate;
            $property->departureDate = $request->departureDate;
            $property->status = $request->status;
            $property->propertyType = $request->propertyType;
            $property->notes = $request->notes;
            $property->specialInfo = $request->specialInfo;
            $property->description = $request->description;
            $property->visitorCount = $request->visitorCount;
            $property->closeStartDate = $request->closeStartDate;
            $property->closeEndDate = $request->closeEndDate;
            $property->openAllYear = $request->openAllYear;
            $property->openingStart = $request->openingStart;
            $property->openingEnd = $request->openingEnd;
            $property->facebookLink = $request->facebookLink;
            $property->instagramLink = $request->instagramLink;
            $property->youtubeLink = $request->youtubeLink;
            $property->ownerId = Auth::id();
            if($property->save()){
                $response = ['status'=>true,'message'=>'Property added successfully','data'=>''];
            }else{
                $response = ['status'=>false,'message'=>'Property addition failed','data'=>''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>'']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $property = Property::where('id',$id)->first();
            if (empty($property)) {
                return response()->json(['status'=>false,'message'=>'Property not found','data'=>'']);
            }
            if($property->delete()){
                $response = ['status'=>true,'message'=>'Property deleted successfully','data'=>''];
            }else{
                $response = ['status'=>false,'message'=>'Property deletion failed','data'=>''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>'']);
        }
    }
}
