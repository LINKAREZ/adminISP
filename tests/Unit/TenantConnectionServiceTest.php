<?php

namespace Tests\Unit;

use App\Core\Services\TenantConnectionService;
use Tests\TestCase;

class TenantConnectionServiceTest extends TestCase
{
    public function test_connection_name_for_id_uses_prefix(): void
    {
        $name = TenantConnectionService::connectionNameForId(1);
        $this->assertStringContainsString('1', $name);
        $this->assertNotEmpty($name);
    }

    public function test_connection_name_for_id_is_consistent(): void
    {
        $a = TenantConnectionService::connectionNameForId(5);
        $b = TenantConnectionService::connectionNameForId(5);
        $this->assertSame($a, $b);
    }
}
