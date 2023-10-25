<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\models\Category;
use Illuminate\Support\Facades\File;
use App\models\TempImage;
use Image;   //for intervention image


class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::latest();

            //for search bar
            if(!empty($request->get('keyword'))){
                $categories = $categories->where('name','like', '%'. $request->get('keyword') .'%');
            }

        $categories = $categories->paginate(10);
        return view('admin.category.list',compact('categories'));
    }

    public function create(){
        return view('admin.category.create');
    }

    public function store(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:categories',
            ]
        );

        if($validator->passes()){
            
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            //save image here
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id); 
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '.' . $ext;
                //$newImage = $category->name . '.' . $ext;    //for disply image_name in folder
                $sPath = public_path() . '/temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($sPath, $dPath);

                    //Generate Image Thumbnail
                    $dPath = public_path() . '/uploads/category/thumb/' . $newImageName;      //
                    //$img = Image::make($sPath)->resize(450, 600)->save($dPath);
                    //$img = Image::make('$newImageName')->getRealPath();                     //
                    //$img->resize(450, 600);                                                 //  
                    //$img->save($dPath);                                                     //
                    $img = Image::make($sPath);  //->resize(450, 600)->save($dPath);
                    $img->fit(450, 600, function ($constraint) {
                        $constraint->upsize();  
                    });
                    $img->save($dPath); 

                $category->image = $newImageName;
                $category->save();
            }



            $request->session()->flash('success','Category added successfully');
              
            return response()->json([ 
                'status' => true,
                'message' => "Category added successfully.",
            ]);

        }else{  
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($categoryID, Request $request){
        //dd($categoryID);
        $category = Category::find($categoryID);
        if(empty($category)){
            return redirect()-> route('categories.index');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update($categoryID, Request $request){

        $category = Category::find($categoryID);
        if( empty($category) ){
            $request->session()->flash('error','category not found!');
            return response()->json([
                'status' => false,
                'notfound' => true,
                'message' => "category not found in database."
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'slug' => 'required|unique:categories,slug,'.$category->id.',id',
            ]
        );

        if( $validator->passes() ){

            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            $oldImage = $category->image;

            if( !empty($request->image_id) ){
                $tempImage = TempImage::find($request->image_id);
                $extensionArray = explode('.', $tempImage->name);
                $extension = last($extensionArray);

                $newImageName = $category->id . '-'. time() . '.' . $extension;
                $sPath = public_path() . '/temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($sPath, $dPath);

                    //Generate Image Thumbnail
                    $dPath = public_path() . '/uploads/category/thumb/' . $newImageName;
                    $img = Image::make($sPath);  //->resize(450, 600)->->save($dPath);
                    $img->fit(450, 600, function ($constraint) {
                        $constraint->upsize();
                    });
                    $img->save($dPath);   
                        

                $category->image = $newImageName;
                $category->save();

                //for delete old images here
                File::delete(public_path() . '/uploads/category/thumb/' . $oldImage);
                File::delete(public_path() . '/uploads/category/' . $oldImage);

            }

            $request->session()->flash('success','You updated successfully!');
            return response()->json([
                'status' => true,
                'message' => 'Updated Success'
            ]); 

        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
        
    }

    public function destroy($categoryID, Request $request){
        $category = Category::find($categoryID);
        if( empty($category) ){
            //return redirect()->route('categories.index');
            $request->session()->flash('error','category not found!');
            return response()->json([ 
                'status' => true,
                'message' => "category not found!",
            ]);
        }

        File::delete(public_path() . '/uploads/category/thumb/' . $category->image);
        File::delete(public_path() . '/uploads/category/' . $category->image);

        $category -> delete();

        $request->session()->flash('success','Category delected successfully!');
        return response()->json([
            'status' => true,
            'message' => 'Category delected successfully!',
        ]);

    }
}
      