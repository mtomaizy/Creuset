<?php

namespace Integration;

use Creuset\User;
use TestCase;

class OrderTest extends TestCase
{
    use \UsesCart, \CreatesOrders;

    /** @test **/
    public function it_redirects_to_login_if_email_is_recognised()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('secret'),
            ]);
        $product = $this->putProductInCart();

        $this->visit('checkout')
             ->type($user->email, 'email')
             ->press('Proceed to Payment')
             ->seePageIs(sprintf('login?email=%s', urlencode($user->email)))
             ->type('secret', 'password')
             ->press('Login')
             ->seePageIs('checkout');
    }

    /** @test **/
    public function it_redirects_back_to_checkout_if_user_logs_in_at_checkout()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('secret'),
            ]);
        $product = $this->putProductInCart();

        $this->visit('checkout')
             ->click('Login')
             ->seePageIs('login')
             ->type($user->email, 'email')
             ->type('secret', 'password')
             ->press('Login')
             ->seePageIs('checkout');
    }

    /** @test **/
    public function it_auto_creates_a_user_for_the_order_when_not_logged_in()
    {
        $product = $this->putProductInCart();

        $this->visit('checkout')
        ->type('booboo@tempuser.com', 'email')
        ->fillAddress()
        ->press('Proceed to Payment')
        ->seePageIs('checkout/pay');

        $this->seeInDatabase('orders', ['amount' => $product->getPrice(), 'status' => 'pending']);
        $this->assertTrue(User::where('email', 'booboo@tempuser.com')->first()->autoCreated());
        $this->seeInDatabase('addresses', ['city' => 'London']);
    }

    /** @test **/
    public function it_creates_an_order_from_a_logged_in_user()
    {
        $user = $this->loginWithUser([], 'customer');
        $product = $this->putProductInCart();
        $address = factory(\Creuset\Address::class)->create([
                                                            'user_id' => $user->id,
                                                            ]);
        $current_stock = $product->stock_qty;

        $this->visit('checkout')
        ->select($address->id, 'billing_address_id')
        ->select($address->id, 'shipping_address_id')
        ->press('Proceed to Payment')
        ->seePageIs('checkout/pay');

        $this->seeInDatabase('orders', ['amount' => $product->getPrice(), 'status' => 'pending']);

        $order = \Creuset\Order::where('user_id', $user->id)->where('amount', $product->getPrice())->first();

        $this->assertEquals($address->id, $order->billing_address_id);
        $this->assertEquals($address->id, $order->shipping_address_id);

        //$this->assertEquals($current_stock - 1, $product->fresh()->stock_qty);
    }

    /** @test **/
    public function it_creates_a_user_for_the_order_when_they_select_to_make_new_account()
    {
        $product = $this->putProductInCart();

        $this->visit('checkout')
        ->type('booboo@tempuser.com', 'email')
        ->fillAddress()
        ->check('create_account')
        ->type('smoomoo', 'password')
        ->type('smoomoo', 'password_confirmation')
        ->press('Proceed to Payment')
        ->seePageIs('checkout/pay');

        $this->seeInDatabase('orders', ['amount' => $product->getPrice(), 'status' => \Creuset\Order::PENDING]);
        $this->assertFalse(User::where('email', 'booboo@tempuser.com')->first()->autoCreated());
        $this->seeInDatabase('addresses', ['city' => 'London']);
    }

    /** @test **/
    public function it_prompts_login_if_user_exists_but_is_signed_out()
    {
        $product = $this->putProductInCart();
        $user = factory(User::class)->create();

        $this->visit('checkout')
        ->type($user->email, 'email')
        ->fillAddress()
        ->press('Proceed to Payment');

        $this->seePageIs(route('auth.login', ['email' => $user->email]))
        ->see('This email has an account here')
        ->type($user->email, 'email')
        ->type('password', 'password')
        ->press('Login')
        ->seePageIs('checkout');
    }

    /** @test **/
    public function it_validates_invalid_user_input()
    {
        $product = $this->putProductInCart();

        $this->visit('checkout')
        ->type('tempuser.com', 'email')
        ->fillAddress()
        ->check('create_account')
        ->press('Proceed to Payment')
        ->seePageIs('checkout');
    }

    /** @test **/
    public function it_views_an_order_summary()
    {
        $this->createOrder();

        $this->be($this->customer);
        $order = $this->order;
        $this->visit("account/orders/{$order->id}")
        ->see($order->amount);
    }

    /** @test **/
    public function it_does_not_allow_viewing_another_users_order_summary()
    {
        $this->createOrder();

        // Login with a different user
        $this->loginWithUser();

        $this->call('GET', "account/orders/{$this->order->id}");
        $this->assertResponseStatus(403);
    }

    protected function fillAddress($type = 'billing')
    {
        return $this
        ->type('Joe', "{$type}_address[name]")
        ->type('10 Downing Street', "{$type}_address[line_1]")
        ->type('London', "{$type}_address[city]")
        ->select('GB', "{$type}_address[country]")
        ->type('SW1A 2AA', "{$type}_address[postcode]")
        ->type('01234567891', "{$type}_address[phone]");
    }
}