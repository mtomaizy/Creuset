<?php

namespace Integration;

use Creuset\Order;
use Creuset\ShippingMethod;
use TestCase;

class ShippingMethodsTest extends TestCase
{
    /** @test **/
    public function it_shows_a_list_of_shipping_methods()
    {
        $this->logInAsAdmin();

        $shipping_method = factory(ShippingMethod::class)->create();

        $this->visit('admin/shipping-methods')
             ->see($shipping_method->description);

    }

    /** @test **/
    public function it_creates_a_new_shipping_method()
    {
        $this->logInAsAdmin();

        $this->visit('admin/shipping-methods')
             ->type('Express Shipping', 'description')
             ->type('5.40', 'base_rate')
             ->press('submit')
             ->seePageIs('admin/shipping-methods')
             ->see('Shipping Method Saved')
             ->see(config('shop.currency_symbol') . '5.40');
    }

    /** @test **/
    public function it_can_delete_a_shipping_method()
    {
       $this->logInAsAdmin();

       $shipping_method = factory(ShippingMethod::class)->create();

       $this->call('DELETE', "admin/shipping-methods/{$shipping_method->id}");

       $this->assertRedirectedTo('admin/shipping-methods');

       $this->notSeeInDatabase('shipping_methods', ['description' => $shipping_method->description]);
    }
}