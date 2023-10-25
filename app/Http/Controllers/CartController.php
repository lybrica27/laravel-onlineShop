<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;

class CartController extends Controller
{
    public function addToCart(Request $request){
        $product = Product::with('product_images')->find($request->id);

        if ($product == null){
            return response()->json([
                'status' => false,
                'message' => 'Product not found',     
            ]);
        }

        if (Cart::count() > 0){
            //echo "Product already in cart";
            // Products found in Cart
            // Check if this product already in the cart
            // Return as message that product already added in your cart
            // if product not found in the cart, then add product in cart

            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item){
                if ($item->id == $product->id){
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false){
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '' ]);
                
                $status = true;
                $message = '<strong>' . $product->title . '</strong> added in your cart successfully!';
                session()->flash('success', $message);

            } else {
                $status = false;
                $message = $product->title.' already added in cart';
            }

        }else{
            //echo "Cart is empty now adding a product in cart";
            //Cart is empty
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product_image)) ? $product->product_images->first() : '' ]);
            $status = true;
            $message = '<strong>' . $product->title . '</strong> added in your cart successfully!';
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
        //Cart::add('293ad', 'Product 1', 1, 9.99);
    }

    public function cart(){
        //dd(Cart::content());
        $cartContent = Cart::content();
        //dd($cartContent);
        $data['cartContent'] = $cartContent;
        return view('front.cart', $data );
    }

    public function updateCart(Request $request){
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);
        //Check qty available in stock
        if ($product->track_qty == 'Yes') {

            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
            }else{
                $message = 'Request qty('. $qty .') not available in stock.';
                $status = false;
            }
        }else{
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('error', $message);
        }

        session()->flash('error', $message);
        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function deleteItem(Request $request){
        $itemInfo = Cart::get($request->rowId);

        if($itemInfo == null){
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);

            return response()->json([
            'status' => false,
            'message' => $errorMessage,
            ]); 
        } 

        Cart::remove($request->rowId);
  
        $message = 'Item removed form cart successfully.';
        session()->flash('success', $message);

            return response()->json([
            'status' => true,
            'message' => $message,
            ]);
    }

    public function checkout(){
        //if cart is empty, redirect to cart page
        if (Cart::count() == 0 ) {
            return redirect()->route('front.cart');
        }

        //if user is not logged in then redirect to login page
        if (Auth::check() == false) {

            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }

            return redirect()->route('account.login');
        }
        session()->forget('url.intended');

        $countries = Country::orderBy('name','ASC')->get();

        return view('front.checkout',[
            'countries' => $countries
        ]);
    }

    
}
   