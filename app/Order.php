<?php

namespace Creuset;

use Cart;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $table = 'orders';

    public $fillable = ['amount', 'status', 'user_id', 'billing_address_id', 'shipping_address_id', 'payment_id'];


    public static function createFromCart(User $user, array $attributes)
    {
        $order = self::create([
            'user_id' => $user->id,
            'amount' => Cart::total(),
            'status' => 'pending',
            'billing_address_id' => $attributes['billing_address_id'],
            'shipping_address_id' => $attributes['shipping_address_id'],
            ]);

        $order_items = Cart::content()->map(function($row) use ($order){
            $item = new OrderItem([
                'order_id' => $order->id,
                'quantity' => $row->qty,
                'description' => $row->product->name,
                'price_paid' => $row->product->getPrice(),
                ]);
            $item->orderable()->associate($row->product);
            $item->save();
            return $item;
        });

        return $order;

    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->user();
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items()
    {
        return $this->order_items();
    }

    public function billing_address()
    {
        return $this->hasOne(Address::class, 'id', 'billing_address_id')->withTrashed();
    }

    public function shipping_address()
    {
        return $this->hasOne(Address::class, 'id', 'shipping_address_id')->withTrashed();
    }
}
