<?php

namespace App\Http\Controllers;

use App\Bill;
use App\BillDetail;
use App\Customer;
use App\Slide;
use App\Product;
use App\ProductType;
use App\Cart;
use Session;
use Illuminate\Http\Request;

class PageController extends Controller
{
  public function getIndex()
  {
    $slide = Slide::all();
    //return view('page.trangchu',['slide'=>$slide]);
    $new_product = Product::where('new', 1)->paginate(4);
    $sanpham_khuyenmai = Product::where('promotion_price', '<>', 0)->paginate(8);
    //dd($new_product);
    return view('page.trangchu', compact('slide', 'new_product', 'sanpham_khuyenmai'));

  }

  public function getLoaiSP($type)
  {
    $sp_theoloai = Product::where('id_type', $type)->get();
    $sp_khac = Product::where('id_type', '<>', $type)->paginate(3);
    $loai = ProductType::all();
    $loai_sp = ProductType::where('id', $type)->first();
    return view('page.loai_sanpham', compact('sp_theoloai', 'sp_khac', 'loai', 'loai_sp'));
  }

  public function getChitiet($detail)
  {
    $sanpham = Product::where('id', $detail)->first();
    $sp_tuongtu = Product::where('id_type',$sanpham->id_type)->paginate(6);
    return view('page.chitiet_sanpham', compact('sanpham','sp_tuongtu'));
  }

  public function getLienHe()
  {
    return view('page.lienhe');
  }

  public function getGioiThieu()
  {
    return view('page.gioithieu');
  }

  public function getAddtoCart(Request $req, $id){
    $product = Product::find($id);
    $oldCart = Session('cart')?Session::get('cart'):null;
    $cart = New Cart($oldCart);
    $cart->add($product, $id);
    $req->session()->put('cart', $cart);
    return redirect()->back();
  }

  public function getDelItemCart($id){
    $oldCart = Session('cart')?Session::get('cart'):null;
    $cart = New Cart($oldCart);
    $cart->removeItem($id);
    Session::put('cart', $cart);
    return redirect()->back();
  }

  public function getCheckout(){
    return view('page.dat_hang');
  }

  public function postCheckout(Request $req){
    $cart = Session::get('cart');

    $customer = New Customer();
    $customer->name = $req->name;
    $customer->gender = $req->gender;
    $customer->email = $req->email;
    $customer->address = $req->address;
    $customer->phone_number = $req->phone;
    $customer->note = $req->note;
    $customer->save();

    $bill = new Bill();
    $bill->id_customer = $customer->id;
    $bill->date_order = date('Y-m-d');
    $bill->total = $cart->totalPrice;
    $bill->payment_method = $req->payment;
    $bill->note = $req->note;
    $bill->save();

    foreach ($cart['items'] as $key => $value ) {
      $bill_detail = new BillDetail();
      $bill_detail->id_bill = $bill->id;
      $bill_detail->id_product = $key;
      $bill_detail->quantity = $value['qty'];
      $bill_detail->unit_price = $value['price']/$value['qty'];
      $bill_detail->save();
    }
    Session::foget('cart');
    return redirect()->back()->with('thongbao','Đặt hàng thành công');
  }
}
