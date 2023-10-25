<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index(Request $request){
        $subCategories = SubCategory::select('sub_categories.*','categories.name as categoryName')
                                    ->latest('sub_categories.id')
                                    ->leftJoin('categories','categories.id','sub_categories.category_id');
        
        if (!empty($request->get('keyword')) ){
            $subCategories = $subCategories->where('sub_categories.name','like', '%' . $request->get('keyword'). '%');
            $subCategories = $subCategories->orWhere('categories.name','like', '%' . $request->get('keyword'). '%');
            //$subCategories = $subCategories->orWhere('sub_categories.slug','like', '%' . $request->get('keyword'). '%');
        }

        $subCategories = $subCategories->paginate(10);

        return view('admin.sub_category.list', compact('subCategories'));
    }

    public function create(){
        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create',$data);
    }

    public function store(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:sub_categories',
                'status' => 'required',
                'category' => 'required',
            ]
        );
        
        if ($validator->passes()) {
            $SubCategory = new SubCategory;
            $SubCategory->name = $request->name;
            $SubCategory->slug = $request->slug;
            $SubCategory->status = $request->status;
            $SubCategory->showHome = $request->showHome;
            $SubCategory->category_id = $request->category;
            $SubCategory->save();

            $request->session()->flash('success','Your subCategories created successfully!');
            
            return response()->json([
                'status' => true,
                'message' => 'Your SubCategories added successfully!',
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id , Request $request){
        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            $request->session()->flash('error','You finding non-exist sub-category');
            return redirect()->route('sub-categories.index');
        }

        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;

        return view('admin.sub_category.edit', $data);
    }    

    public function update($id , Request $request){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('errors', 'You wrong editing non-exist sub-category!');
            //return redirect()->route('sub-categories.index');
            return response()->json([
                'status' => false,
                'notfound' => true,
            ]);
        }

        $validator = Validator::make(     
            $request->all(),
            [
                'name' => 'required',
                //'slug' => 'required|unique:sub_categories',
                'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.' ,id',
                'category' => 'required',
                'status' => 'required',
            ]
        );

        if($validator->passes()){
            //$subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status; 
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub Categories updated successfully!');
            return response()->json([
                'status' => true,
                'message' => 'Sub categories updated successfully!',
            ]);

        }else{
            $request->session()->flash('error','Your editing is something wrongs');
            return response()->json([
                'status' => false,
                'message' => "your editing is something wrongs",
            ]);
        }
    }

    public function destroy($id, Request $request){
        $subCategory = SubCategory::find($id);

        if( empty($subCategory) ){
            $request->session()->flash('error','Your subCategory is not found to delete.');
            return response([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $subCategory->delete();
        $request->session()->flash('success','Your record deleted successfully');
        return response([
            'status' => true,
            'message' => 'Your record is deleted successfully',
        ]);
    }
}
   