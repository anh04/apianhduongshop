<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;
use App\Models\City;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Models\OrderView;
use Illuminate\View\View;


use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\CommonController;


class OrderController extends Controller
{
    /*
      * Validate shipment
      */
    public function ValidateShipment(){
        return [
            'shipment_city' => 'required',
            'shipment_district' => 'required',
            'shipment_ward' => 'required',
            'shipment_address' => 'required',
            'user_id' => 'required',
            'full_name' => 'required',
        ];
    }
    /*
     * Validate order product
     */
    public function ValidateOrderProduct(){
        return [
            'order_order_id' => 'required',
            'product_product_id' => 'required',
            'order_products_qty' => 'required',
            'order_products_price' => 'required',
            'order_products_total' => 'required'
        ];
    }
    /*
     * Validate order product
     */
    public function ValidateOrder(){
        return [
//            'shipment_city' => 'required',
//            'shipment_district' => 'required',
//            'shipment_ward' => 'required',
//            'shipment_address' => 'required',
//            'user_id' => 'required',
//            'full_name' => 'required',
            'data_post'=> 'required',
            'yourCart'=>'required'
        ];
    }

    /*
     *Create Order
     */
    public function createOrder(Request $request){
        //$validated = $request->validate($this->ValidateOrder());
        //print_r($test); die();
        $validator= Validator::make($request->all(), [
            'data_post.shipment_phone' => 'required',
            'data_post.shipment_email' => 'required',
            'data_post.shipment_city' => 'required',
            'data_post.shipment_state' => 'required',
            'data_post.shipment_zipcode' => 'required',
            'data_post.shipment_address' => 'required',
            'method' => 'required',
            'yourCart.*.required' => 'required|string',
            'yourCart.*.' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        //thieu veriry discount code
        if ($validator->passes()) {
            $input = $request->all();
            $dataShipment = $input['data_post'];
            //check email
            $common = new CommonController();
            $id = $common->checkAnhCreateEmail($dataShipment);
            //$users = DB::table('users')->find(\DB::table('users')->max('id'));
            //$users->id

            $carts = json_decode($input['yourCart'], true);
            $temps = array();
            $order_total = 0;
            foreach($carts as $item){
                $list = array();
                $list['order_products_id'] = $item['prd_id'];
                $list['order_product_price'] = $item['prd_price'];
                $list['order_product_qty'] = $item['amount'];
                $list['order_product_name'] = $item['prd_name'];
                $list['order_product_regular_price'] = $item['prd_regular_price'];
                $list['order_product_color'] = $item['prd_color'];
                $list['order_product_size'] = $item['prd_size'];
                $list['order_product_sex'] = $item['prd_sex'];
                $list['order_product_total'] = $item['prd_price'] * $item['amount'];
                $temps[]=$list;
                $order_total += $list['order_product_total'];
            }
            ///////////////
            $dataShipment['user_id'] = $id; //Auth::user()->id;

            $shipment = Shipment::create($dataShipment);

            $order_status ='Checking';
            $order_code = date('Y').''. DB::table('orders')->max('order_id') +1;
            $shipment = $shipment->shipment_id;
            $method = $input['method'];
            $order_discount_code = $input['discount_code'];
            $order_discount_total = $input['discount_value'];

            if(!is_numeric($order_discount_total)) $order_discount_total =0;
            $order_total = $order_total - $order_discount_total;
            $order = Order::create([
                'order_total'=>$order_total,
                'order_status'=>$order_status,
                'order_code'=>$order_code,
                'shipment'=>$shipment,
                'order_discount_code'=>$order_discount_code,
                'order_discount_total'=>$order_discount_total,
                'user_id'=>$id,  //Auth::user()->id,
                'order_payment_method'=>$method
            ]);

            $order_id = $order->order_id;
            $payment = Payment::create([
                'order_id'=>$order->order_id,
                'payment_amount'=>$order_total,
                'payment_method'=>$method
            ]);
            $payment_id = $payment->payment_id;
            $products=array();
            foreach($temps as $item){
                $item['order_order_id']= $order->order_id;
                $products[] = $item;
            }
            //print_r($products); die();
            $ord_prd = OrderProduct::insert($products);

            if($ord_prd && $payment_id && $order_id && $shipment){
                return response()->json(['success' =>$order_code,'order_id'=>$order_id], 200);
            }else{
                return response()->json(['error' =>'Problem happen' ], 400);
            }
        }
    }
    /*
     *  Order detail
     */
    public function orderDetail($order_id){
        $temp = OrderView::select('order_shipment_view.*')->where('order_id','=',$order_id)
            ->get()->toArray();
        $data = array();
        if(count($temp) > 0){
            $data = $temp[0];
            $data['prds'] =  OrderProduct::select('order_products.*')->where('order_order_id','=',$order_id)
                ->get()->toArray();
            $data['date']= Carbon::parse($data['created_at'])->format('d/m/Y');
            $data['hour']= Carbon::parse($data['created_at'])->format('h:m');
        }

        //print_r($data); die();  {{ \Carbon\Carbon::parse($products['created_at'])->format('d/m/Y') }}
        return view('order')->with(['products'=>$data]);
    }
    /*
     *  Order list
     */
    public function orderList(){
        $user_id = Auth::user()->id;
        $temp = OrderView::select('order_shipment_view.*')->where('user_id','=',$user_id)
            ->get()->toArray();
        $data = array();
        if(count($temp) > 0){
            foreach($temp as $item){
                $item['prds']=  OrderProduct::select('order_products.*')->where('order_order_id','=',$item["order_id"])->get()->toArray();
                $item['date']= Carbon::parse($item['created_at'])->format('d/m/Y');
                $item['hour']= Carbon::parse($item['created_at'])->format('h:m');
                $data[] = $item;
            }
        }
        // print_r($data); die();
        return view('orders')->with(['data'=>$data]);
    }
}
