<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\ProductType;

class ProductController extends Controller
{
    /***********************************/
    public function aothun($id){
        $product_detail = Product::select('products.*','product_types.prd_type_name')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')->find($id);

        $current_date = date('Y-m-d h:m');
        $discount = Discount::where('start_date', '<=', "{$current_date}")
            ->where('end_date', '>=', "{$current_date}")
            ->where('active', '=', true)
            ->get();

        $rsl = [];
        $rsl['product']=$product_detail;
        $rsl['discounts']=$discount;
        return response()->json($rsl);
    }
    /***********************************/
    public function fashionProductList(Request $request){
        $page = $request->input('page');
        $limit =$request->input('limit');
        $offset = ($page -1)*$limit;

        $products = Product::select('products.*','product_types.prd_type_name','product_types.prd_type_brand',
            'brands.brand_name',
            DB::raw('SUM(order_products.order_product_qty) AS amount_of_sold'))
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')
            ->leftJoin('brands','brands.brand_id','=','product_types.prd_type_brand')
            ->where('prd_type_name', '=', "polo shirt")
            ->orwhere('prd_type_name', '=', "No neck T-shirt")
            ->orwhere('prd_type_name', '=', "Clothing fashion")
            ->groupBy('products.prd_id')
            ->groupBy('product_types.prd_type_name')
            ->orderBy('prd_id','asc')
            ->offset($offset)->limit($limit)->get();
        $rowCount = Product::select('products.*')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')
            ->leftJoin('brands','brands.brand_id','=','product_types.prd_type_brand')
            ->where('prd_type_name', '=', "polo shirt")
            ->orwhere('prd_type_name', '=', "No neck T-shirt")
            ->orwhere('prd_type_name', '=', "Clothing fashion")
            ->count();

        $last_page=0;
        if($rowCount >0 && $limit>0){
            $remains =  ($rowCount%$limit >0)? 1:0;
            $last_page = $remains + ($rowCount - $rowCount%$limit)/$limit;
        }

        $rsl = [];
        $rsl['data']=$products;
        $rsl['last_page']=$last_page;
        $rsl['total']=$rowCount;
        return response()->json($rsl);
    }

    /**************************************/
    public function fashionID(Request $request)
    {
        $id = $request->input('id');
        //print_r($id); die();
        $product_detail = Product::select('products.*','product_types.prd_type_name')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')->find($id);
        //print_r($product_detail); die();
        //return redirect('product')->with(['product'=>$product_detail]); //xem view redirect with data
        //return view('products.product')->with(['product'=>$product_detail]);
        return response()->json($product_detail);

    }
    /**************************************/
    public function productID(Request $request)
    {
        $id = $request->input('id');
        //print_r($id); die();
        $product_detail = Product::select('products.*','product_types.prd_type_name')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')->find($id);
        return response()->json($product_detail);

    }

    /**************************************/
    public function productList(Request $request)
    {
        $page = $request->input('page');
        $limit =$request->input('limit');
        $offset = ($page -1)*$limit;

        $products = Product::select('products.*','product_types.prd_type_name')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')
            ->orderBy('prd_id','asc')
            ->offset($offset)->limit($limit)->get();
        $rowCount = Product::select('products.*','product_types.prd_type_name')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')
            ->count();
        //$products =$query->limit($limit)->offset($offset)->get();
        // $rowCount = $query->count();
        $last_page=0;
        if($rowCount >0 && $limit>0){
            $remains =  ($rowCount%$limit >0)? 1:0;
            $last_page = $remains + ($rowCount - $rowCount%$limit)/$limit;
        }

        $rsl = [];
        $rsl['data']=$products;
        $rsl['last_page']=$last_page;
        $rsl['total']=$rowCount;
        return response()->json($rsl);
    }

    /**************************************/
    public function edit(Request $request)
    {
        $id = $request->input('id');
        //print_r($id); die();
        $product_detail = Product::select('products.*','product_types.prd_type_name')
            ->leftJoin('product_types','product_types.prd_type_id','=','products.prd_type')->find($id);
        //print_r($product_detail); die();
        //return redirect('product')->with(['product'=>$product_detail]); //xem view redirect with data
        //return view('products.product')->with(['product'=>$product_detail]);
        return response()->json($product_detail);

    }

    /*****************************************/
    public function saveProduct1(Request $request)
    {
        $validated = $request->validate([
            'prd_type' => 'required',
            'prd_name' => 'required',
            'prd_quantity' => 'required',
            'prd_price' => 'required',
        ]);

        $input = $request->all();
        unset($input['_token']);

        /*if ($request->file('prd_img') == null) {
            $path = "";
        }else{
            $image = $request->file('prd_img');
            //$ext = $image->extension();
            $image_name = $image->getClientOriginalName();
            //$path = $request->file('prd_img')->store('products');
            $path = $request->file('prd_img')->storeAs('products', $image_name, 'local');
        }*/
        //die($path);
        $data= array();
        $multi_sexes ='';
        $multi_sizes='';
        foreach($input as $key=>$value){
            if($value !=''){
                if($key =='prd_male'){
                    $multi_sexes =($multi_sexes=='')?$value:$multi_sexes.','.$value;
                }elseif($key =='prd_female'){
                    $multi_sexes =($multi_sexes=='')?$value:$multi_sexes.','.$value;
                }elseif($key =='prd_unknown'){
                    $multi_sexes =($multi_sexes=='')?$value:$multi_sexes.','.$value;
                }elseif($key =='prd_small'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_medium'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_x'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_xl'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_price'){
                    $data[$key] = preg_replace('/\$|\s+|\,+|\.00$/', '', $value);
                }elseif($key =='prd_regular_price'){
                    $data[$key] = preg_replace('/\$|\s+|\,+|\.00$/', '', $value);
                }else{
                    $data[$key] = $value;
                }
            }
        }
        if($multi_sexes !=''){
            $data['multi_sexes'] = $multi_sexes;
        }
        if($multi_sizes !=''){
            $data['multi_sizes'] = $multi_sizes;
        }

        // print_r($data);
        // die();

        Product::create($data);
        return response()->json(['message' => 'Product added successfully'], 201);
    }

    /*****************************************/
    public function newOrUpdateProduct(Request $request)
    {
        $validated = $request->validate([
            'prd_type' => 'required',
            'prd_name' => 'required',
            'prd_quantity' => 'required',
        ]);

        $input = $request->all();
        unset($input['_token']);

        //die($path);
        $data= array();
        $multi_sexes ='';
        $multi_sizes='';
        $prod_id ='';
        foreach($input as $key=>$value){
            if($value !=''){
                if($key =='prod_attr'){
                    $data[$key] = $value;
                }elseif($key=='prod_id'){
                    $prod_id = $value;
                } else{
                   $data[$key] = $value;
                }
            }
        }
        // print_r($data);
        // die();
        if($prod_id !=''){
            Product::where('prd_id', $prod_id)->update($data);
            return response()->json(['message' => 'Update successfully'], 201);
        }else{
            $newProd = Product::insert($data);
            if($newProd->prd_id){
                return response()->json(['message' => 'Product added successfully'], 201);
            }else{
                return response()->json(['message' => 'Problem happen'], 201);
            }

        }


       // Product::create($data);

    }
    /*****************************************/
    public function updateproduct(Request $request,$id)
    {
        $validated = $request->validate([
            'prd_type' => 'required',
            'prd_name' => 'required',
            'prd_quantity' => 'required',
        ]);

        //$product = Product::findOrFail($id);

        $input = $request->all();
        unset($input['_token']);

        $id = request()->route('prd_id');
        $data= array();
        foreach($input as $key=>$value){
            if($value !=''){
                if($key !='prod_attr'){
                    $data[$key] = $value;
                }else{
                    $data[$key] = $value;
                }
            }
        }

        Product::where('prd_id', $id)->update($data);
        return response()->json(['message' => 'Product update successfully'], 200);
    }
    /*****************************************/
    public function updateproduct1(Request $request,$id)
    {
        $validated = $request->validate([
            'prd_type' => 'required',
            'prd_name' => 'required',
            'prd_quantity' => 'required',
            'prd_price' => 'required',
        ]);

        //$product = Product::findOrFail($id);

        $input = $request->all();
        unset($input['_token']);
        // $product->update($input);
        $data= array();
        $multi_sexes ='';
        $multi_sizes='';
        foreach($input as $key=>$value){
            if($value !=''){
                if($key =='prd_male'){
                    $multi_sexes =($multi_sexes=='')?$value:$multi_sexes.','.$value;
                }elseif($key =='prd_female'){
                    $multi_sexes =($multi_sexes=='')?$value:$multi_sexes.','.$value;
                }elseif($key =='prd_unknown'){
                    $multi_sexes =($multi_sexes=='')?$value:$multi_sexes.','.$value;
                }elseif($key =='prd_small'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_medium'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_x'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_xl'){
                    $multi_sizes =($multi_sizes=='')?$value:$multi_sizes.','.$value;
                }elseif($key =='prd_price'){
                    $data[$key] = preg_replace('/\$|\s+|\,+|\.00$/', '', $value);
                }elseif($key =='prd_regular_price'){
                    $data[$key] = preg_replace('/\$|\s+|\,+|\.00$/', '', $value);
                }else{
                    $data[$key] = $value;
                }
            }
        }
        if($multi_sexes !=''){
            $data['multi_sexes'] = $multi_sexes;
        }
        if($multi_sizes !=''){
            $data['multi_sizes'] = $multi_sizes;
        }

//        print_r($prd_id);
//        die();
        $prd_id = request()->route('prd_id');
        Product::where('prd_id', $id)->update($data);
        return response()->json(['message' => 'Product update successfully'], 200);
    }

    /*****************getProductType***********/
    public function getProductType(){
        $product_type = ProductType::select('*')->get();
        return response()->json($product_type);
    }

    /*****************************************/
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete the cover image if it exists
        if ($product->cover_image) {
            Storage::delete('public/images/' . $product->prd_img);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
