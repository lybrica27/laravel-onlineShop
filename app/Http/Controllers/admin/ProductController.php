<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductImage;
use Image;
use App\Models\TempImage;
use App\Models\SubCategory;
use Illuminate\Support\Facades\File;


class ProductController extends Controller
{
    public function index(Request $request){
        $products = Product::latest('id')->with('product_images');

        if($request->get('keyword')){
            $products = $products->where('title','like', '%'. $request->keyword .'%');
        }
        
        $products = $products->paginate();
        //dd($products);
        $data['products'] = $products;
        return view('admin.products.list', $data);
    }

    public function create(){
        $data = [];
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        
        return view('admin.products.create', $data);
    }

    public function store(Request $request){
        
        // dd($request->image_array);
        // exit();

        $rules = [   
            'title' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes' ) {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if( $validator->passes() ){
            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->shipping_returns = $request->shipping_returns;
            $product->short_description = $request->short_description;
            $product->related_products = (!empty($request->related_products)) ? implode(',' , $request->related_products) : '';
            $product->save();

            //Save Gallery Pics
            if( !empty($request->image_array) ){
                foreach($request->image_array as $temp_image_id){

                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray);   //jpg, pdf, png

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id. '-' .$productImage->id. '-' .time(). '.' .$ext;
                    //product_id => 4; product_image_id => 1
                    //4-1-2342434.jpg
                    $productImage->image = $imageName;
                    $productImage->save();

                    //Generate Product Thumbnails
                    //Large Image
                    $sourcePath = public_path() . '/temp/' . $tempImageInfo->name;
                    $destPath = public_path() . '/uploads/product/large/' . $imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destPath);

                    //Small Image
                    $destPath = public_path() . '/uploads/product/small/' . $imageName;
                    $image = Image::make($sourcePath);
                    $image->fit(300,300);
                    $image->save($destPath);
                }
            }

            $request->session()->flash('success','New record is added successfully');
            return response()->json([
                'status' => true,
                'message' => 'New record is added successfully',
            ]);

        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id, Request $request){
        $products = Product::find($id);
        if (empty($products)){
            //$request->session()->flash('errors','Product not found');
            return redirect()->route('products.index')->with('error','Product not found');
        }

        //Fetch Product Images
        $subCategories = SubCategory::where('category_id',$products->category_id)->get();
        $productImages = ProductImage::where('product_id',$products->id)->get();

        $relatedProducts = [];
        //fetch related products
        if ($products->related_products != '') {
            $productArray = explode(',' , $products->related_products);
            $relatedProducts = Product::whereIn('id',$productArray)->get();
        }
    
        $data = [];
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products; 
        $data['subCategories'] = $subCategories;
        $data['productImages'] = $productImages;
        $data['relatedProducts'] = $relatedProducts;

        return view('admin.products.edit', $data);
    }

    public function update($id, Request $request){
        $products = Product::find($id);

        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$products->id.',id',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,'.$products->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];
        if( !empty($request->track_qty) && $request->track_qty == 'Yes' ){
            $rules['qty'] = 'required|numeric';
        };

        $validator = Validator::make(
            $request->all(), $rules
        );

        if ($validator->passes()) {
            $products->title = $request->title;    
            $products->slug = $request->slug;
            $products->description = $request->description;
            $products->price = $request->price;
            $products->compare_price = $request->compare_price;
            $products->sku = $request->sku;
            $products->barcode = $request->barcode;
            $products->track_qty = $request->track_qty;
            $products->qty = $request->qty;
            $products->status = $request->status;
            $products->category_id = $request->category;
            $products->sub_category_id = $request->sub_category;
            $products->brand_id = $request->brand;
            $products->is_featured = $request->is_featured;
            $products->shipping_returns = $request->shipping_returns;
            $products->short_description = $request->short_description;
            $products->related_products = (!empty($request->related_products)) ? implode(',' , $request->related_products) : '';
            $products->save();

            $request->session()->flash('success','Your product is updated!');
            return response()->json([
                'status' => true,
                'message' => 'Your product is updated',
            ]);

        }else{   
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }

    public function destroy($id, Request $request){
        $products = Product::find($id);

        if(empty($products)){
            $request->session()->flash('error','product not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $productImages = ProductImage::where('product_id',$id)->get();

        if(!empty($productImages)){  
            foreach($productImages as $productImage){
                File::delete(public_path('uploads/product/large/' . $productImage->image));
                File::delete(public_path('uploads/product/small/' . $productImage->image));
            }

            ProductImage::where('product_id', $id)->delete();
        }

        $products->delete();

        $request->session()->flash('success','Products delected successfully!');
        return response()->json([
            'status' => true,
            'message' => 'Products deleted successfully!',
        ]);
        
    }  
     
    public function getProducts(Request $request){

        $tempProduct = [];
        if ($request->term != "") {
            $products = Product::where('title','like','%'. $request->term .'%')->get();

            if ($products != null) {
                foreach($products as $product){
                    $tempProduct[] = array('id' => $product->id, 'text' => $product->title);
                }
            }
        }

        //print_r($tempProduct);
        return response()->json([
            'tags' => $tempProduct,
            'status' => true,
        ]);
    }
}   
