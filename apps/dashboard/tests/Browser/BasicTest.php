<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BasicTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            //$browser->press('Login');
            $browser->clickLink('Login');
            $browser->visit('/login');
            $browser->type('email', 'bankerrajendra@upnrunn.com');
            $browser->type('password', 'Raj@Upnrunn2018');
            $browser->press('Login');
            $browser->assertPathIs('/home');
            $browser->clickLink('Shop Local');
            $browser->visit('/');
            $browser->waitFor('.event-primary', 25);
            //$browser->waitForText('Joseph Riviello');
            $browser->clickLink('Joseph Riviello');
            $browser->clickLink('Settings');
            $browser->visit('/settings/stores/27');
            $browser->assertSee('Shop Local #27');
        });
    }
}
