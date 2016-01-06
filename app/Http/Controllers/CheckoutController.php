<?php

namespace Creuset\Http\Controllers;

use Cart;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Show the checkout page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if (!Cart::count()) {
            return view('shop.cart_empty');
        }

        return view('shop.checkout');
    }

    public function pay()
    {
        if (!\Session::has('order')) {
            dd('no order in session');
        }
        $order = \Session::get('order');

        return view('orders.pay')->with(compact('order'));
    }
}
