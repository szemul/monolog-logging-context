<?php
declare(strict_types=1);

namespace Szemul\MonologLoggingContext\Test;

use PHPUnit\Framework\TestCase;

class NoopTest extends TestCase
{
    public function testNothing(): void
    {
        $this->assertTrue(true);
    }
}
