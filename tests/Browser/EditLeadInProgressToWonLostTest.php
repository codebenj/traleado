<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Browser\Pages\Login;
use Tests\Browser\Pages\AdminLeadToInProgress;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Customer;
use App\Lead;
use App\LeadEscalation;
use App\Organisation;

class EditLeadInProgressToWonLostTest extends DuskTestCase
{
    use DatabaseMigrations, WithFaker;

    protected $clearCookiesBetweenTests = true;

    protected $organisation;

    protected $lead;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed');
        $this->artisan('lada-cache:flush');

        factory(Customer::class, 1)
            ->create()
            ->each(function ($customer){
                $this->organisation = Organisation::inRandomOrder()->first();

                $this->lead = factory(Lead::class)->create([
                    'customer_id' => $customer->id,
                ]);

                factory(LeadEscalation::class)->create([
                    'lead_id' => $this->lead->id,
                    'organisation_id' => $this->organisation->id,
                ]);
            }
        );
    }

    /** @test */
    public function edit_lead_inprogress_to_won_success(){
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->submit('admin@traleado.com', 'traleado.admin')
                ->pause(1000)
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            $browser->visit(new AdminLeadToInProgress)
                ->pause(5000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Awaiting Response', 'is_active' => 1]);

            $browser->waitFor('.swal2-actions')
                ->click('.swal2-confirm')
                ->pause(500);

            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

                //click td tr.
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click edit lead
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if lead for is in the browser
            $browser->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select level
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[4]->click();

            //next button
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //create botton
            $browser->click('@leadForm-update')
                ->pause(1000);

            $browser->waitFor('.swal2-actions')
                ->click('.swal2-confirm')
                ->pause(4000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Won', 'is_active' => 1]);

            $browser->screenshot('edit_lead_inprogress_to_won_success');
        });
    }

    /** @test */
    public function edit_lead_inprogress_to_lost_success(){
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

            $browser->visit(new AdminLeadToInProgress)
                ->pause(5000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Awaiting Response', 'is_active' => 1]);

            $browser->waitFor('.swal2-actions')
                ->click('.swal2-confirm')
                ->pause(500);

            $browser->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->waitUntilVue('pageTitle', 'Leads Dashboard', '@dashboardPage')
                ->maximize();

                //click td tr.
            $browser->waitFor('.sc-table')
                ->waitFor('.el-table__body-wrapper')
                ->waitFor('.el-table__body')
                ->elements('.el-table__row')[0]->click();

            //click edit lead
            $browser->waitFor('.el-drawer__body')
                ->waitFor('.el-tabs__nav-scroll')
                ->waitFor('.el-tabs__nav')
                ->click('@edit-lead');

            //check if lead for is in the browser
            $browser->waitFor('.lead-form')
                ->waitForText('Edit Lead Information');

            //select level
            $browser->click('.escalation_level .el-input > .el-input__inner')
                ->waitFor('.escalation_level_popper')
                ->elements('.escalation_level_popper > .el-scrollbar > .el-select-dropdown__wrap > .el-select-dropdown__list > .el-select-dropdown__item')[5]->click();

            //next button
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //next botton
            $browser->click('@leadForm-next');

            //create botton
            $browser->click('@leadForm-update')
                ->pause(1000);

            $browser->waitFor('.swal2-actions')
                ->click('.swal2-confirm')
                ->pause(4000);

            $this->assertDatabaseHas('lead_escalations', ['escalation_status' => 'Lost', 'is_active' => 1]);

            $browser->screenshot('edit_lead_inprogress_to_lost_success');
        });
    }
}
