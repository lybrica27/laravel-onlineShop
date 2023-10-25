<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;

class BrandController extends Controller
{   
    public function index(Request $request){
        $brands = Brand::latest('id');

        if($request->get('keyword')){
            $brands = $brands->where('name','like','%'. $request->keyword .'%');
        }
        $brands = $brands->paginate(10);
        
        return view('admin.brands.list', compact('brands'));
    }

    public function create(){
        return view('admin.brands.create');
    }

    public function store(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:brands',
            ]
        );

        if($validator->passes()){
            $brand = new Brand();
            $brand->name = $request->name;   
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Your Brand is created successfully.',
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id, Request $request){
        $brand = Brand::find($id);
        if(empty($brand)){
            $request->session()->flash('error','Your record is not found');
            return redirect()->route('brands.index');
        }
        $data['brand'] = $brand;
        return view('admin.brands.edit', $data);
    }

    public function update($id, Request $request){
        $brand = Brand::find($id);
        if (empty($brand)) {
            $request->session()->flash('error','Your record not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:brands,slug,'.$brand->id.',id',
            ]
        );

        if ($validator->passes()) {
            //$brand = new Brand();      if you add, you will get new same data when update
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Your record is updated successfully',
            ]);

        }else{
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }
    }

    public function destroy($id, Request $request){
        $brand = Brand::find($id);
        if(empty($brand)){     
            $request->session()->flash('errors','Your deleted record that not found ');
            return response([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $brand->delete();
        $request->session()->flash('success','Your record is deleted successfully');
        return response([
            'status' => true,
            'message' => 'Your record is deleted.',
        ]);
    }
}
 