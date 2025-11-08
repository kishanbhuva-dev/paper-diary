<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $resources = Resource::where('resourceTypeId', $request->id)->get();
            $response  = ['status' => true, 'message' => '', 'data' => $resources];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'             => 'required|array',
                'name.*'           => 'required|string',
                'status'           => 'nullable|array',
                'status.*'         => 'nullable|boolean',
                'resourceTypeId'   => 'required|array',
                'resourceTypeId.*' => 'required|exists:resource_types,id',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            foreach ($request->name as $key => $value) {
                $resources                 = new Resource;
                $resources->name           = $value;
                $resources->status         = $request->status[$key] ?? 0;
                $resources->resourceTypeId = $request->resourceTypeId[$key];
                $resources->save();
            }
            $response = ['status' => true, 'message' => 'Resources created successfully', 'data' => []];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name'             => 'required|array',
            'name.*'           => 'required|string',
            'status'           => 'nullable|array',
            'status.*'         => 'nullable|boolean',
            'resourceTypeId'   => 'required|array',
            'resourceTypeId.*' => 'required|exists:resource_types,id',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
            return response()->json($response);
        }
        foreach ($request->name as $key => $value) {
            $resources                 = Resource::where('id', $id)->first();
            $resources->name           = $value;
            $resources->status         = $request->status[$key] ?? 0;
            $resources->resourceTypeId = $request->resourceTypeId[$key];
            $resources->save();
        }
        $response = ['status' => true, 'message' => 'Resources updated successfully', 'data' => []];
        return response()->json($response);
    }
    public function destroy(string $id)
    {
        try {
            $resources = Resource::where('id', $id)->first();
            if (! empty($resources)) {
                if ($resources->delete()) {
                    $response = ['status' => true, 'message' => 'Resource deleted successfully', 'data' => []];
                } else {
                    $response = ['status' => false, 'message' => 'Resource not deleted', 'data' => []];
                }
            } else {
                $response = ['status' => false, 'message' => 'Resource not found', 'data' => []];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
}
