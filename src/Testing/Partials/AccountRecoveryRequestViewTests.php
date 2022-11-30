<?php

namespace ClaudioDekker\LaravelAuthBlade\Testing\Partials;

trait AccountRecoveryRequestViewTests
{
    /** @test */
    public function the_account_recovery_request_page_uses_blade_views(): void
    {
        $response = $this->get(route('recover-account'));

        $response->assertViewIs('auth.recover-account');
    }
}
