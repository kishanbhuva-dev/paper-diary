<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Validator;

class FacilityController extends Controller
{

    public function index(Request $request)
    {
        try {
            // $facilities = Facility::where(function ($query) use ($request) {
            //     $query->where('name', 'like', '%' . $request->search . '%')->orWhere('description', 'like', '%' . $request->search . '%')
            //         ->orWhere('description', 'like', '%' . $request->search . '%');

            // });
            $facility = Facility::query();
            if ($request->search) {
                $searchTerm = $request->search;
                $facility->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('description', 'like', '%' . $searchTerm . '%');

                    if (strtolower($searchTerm) === 'active') {
                        $query->orWhere('status', true);
                    } elseif (strtolower($searchTerm) === 'inactive') {
                        $query->orWhere('status', false);
                    }
                });
            }
            $order      = $request->orderBy ?? 'id';
            $sort       = $request->sort ?? 'asc';
            $pagination = $request->pagination ?? 10;
            $facilities = $facility->orderBy($order, $sort)->paginate($pagination);

            if (! empty($facilities)) {
                $response = ['status' => true, 'message' => '', 'data' => $facilities];
            } else {
                $response = ['status' => false, 'message' => 'No facilities found', 'data' => []];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'        => 'required|string|max:255',
                'icon'        => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'status'      => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
            }
            $facility              = new Facility;
            $facility->name        = $request->name;
            $facility->icon        = $request->icon;
            $facility->description = $request->description;
            if (isset($request->status)) {
                $facility->status = true;
            }
            $facility->save();
            $response = ['status' => true, 'message' => 'Facility created successfully', 'data' => $facility];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    public function show(string $id)
    {
        try {
            $facility = Facility::where('id', $id)->first();
            if (! $facility) {
                return response()->json(['status' => false, 'message' => 'Facility not found'], 404);
            }
            $response = ['status' => true, 'message' => 'Facility retrieved successfully', 'data' => $facility];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $th->getMessage()], 500);
        }
    }
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'        => 'required|string|max:255',
                'icon'        => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'status'      => 'nullable|in:0,1',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
            }
            $facility = Facility::find($id);
            if (! $facility) {
                return response()->json(['status' => false, 'message' => 'Facility not found'], 404);
            }
            $facility->name        = $request->name;
            $facility->icon        = $request->icon;
            $facility->description = $request->description;

            if (isset($request->status)) {
                $facility->status = $request->status;
            }
            if ($facility->save()) {
                $response = ['status' => true, 'message' => 'Facility updated successfully', 'data' => $facility];
            } else {
                $response = ['status' => false, 'message' => 'Facility update failed', 'data' => []];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    public function destroy(string $id)
    {
        try {
            $facility = Facility::where('id', $id)->first();
            if (! $facility) {
                return response()->json(['status' => false, 'message' => 'Facility not found'], 404);
            }
            if ($facility->delete()) {
                $response = ['status' => true, 'message' => 'Facility deleted successfully', 'data' => []];
            } else {
                $response = ['status' => false, 'message' => 'Facility delete failed', 'data' => []];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
