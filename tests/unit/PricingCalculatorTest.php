<?php

namespace Msc\ChatGPT\Tests\Unit;

use Msc\ChatGPT\PricingCalculator;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    public function testEstimateForKnownModel(): void
    {
        $calculator = new PricingCalculator();
        $estimated = $calculator->estimate('gpt-5-mini-2026-02-01', 500000, 200000);

        $this->assertSame(0.525, $estimated);
    }

    public function testEstimateForUnknownModelReturnsZero(): void
    {
        $calculator = new PricingCalculator();
        $estimated = $calculator->estimate('unknown-model', 100000, 100000);

        $this->assertSame(0.0, $estimated);
    }
}
