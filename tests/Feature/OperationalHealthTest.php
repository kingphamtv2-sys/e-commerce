<?php

namespace Tests\Feature;

use Tests\TestCase;

class OperationalHealthTest extends TestCase
{
    public function test_public_health_check_returns_only_a_minimal_status(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }
}
