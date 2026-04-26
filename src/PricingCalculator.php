<?php

namespace Msc\ChatGPT;

class PricingCalculator
{
    public const DEFAULT_VERSION = 'openai-2026-04';

    public function __construct(
        protected string $version = self::DEFAULT_VERSION
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function estimate(string $model, int $promptTokens, int $completionTokens): float
    {
        $rates = $this->getRatesForModel($model);
        if (!$rates) {
            return 0.0;
        }

        $inputCost = ($promptTokens / 1_000_000) * $rates['input_per_million'];
        $outputCost = ($completionTokens / 1_000_000) * $rates['output_per_million'];

        return round($inputCost + $outputCost, 8);
    }

    protected function getRatesForModel(string $model): ?array
    {
        $modelLower = strtolower($model);

        $pricing = [
            'gpt-5' => ['input_per_million' => 1.25, 'output_per_million' => 10.00],
            'gpt-5-mini' => ['input_per_million' => 0.25, 'output_per_million' => 2.00],
            'gpt-5-nano' => ['input_per_million' => 0.05, 'output_per_million' => 0.40],
            'gpt-4o' => ['input_per_million' => 5.00, 'output_per_million' => 15.00],
            'o1' => ['input_per_million' => 15.00, 'output_per_million' => 60.00],
            'o3' => ['input_per_million' => 10.00, 'output_per_million' => 40.00],
            'o4' => ['input_per_million' => 2.50, 'output_per_million' => 10.00],
            'gpt-4' => ['input_per_million' => 30.00, 'output_per_million' => 60.00],
            'gpt-3.5' => ['input_per_million' => 0.50, 'output_per_million' => 1.50],
        ];

        // Prefix order matters for more specific IDs.
        $orderedPrefixes = ['gpt-5-mini', 'gpt-5-nano', 'gpt-5', 'gpt-4o', 'o1', 'o3', 'o4', 'gpt-4', 'gpt-3.5'];

        foreach ($orderedPrefixes as $prefix) {
            if (str_starts_with($modelLower, $prefix)) {
                return $pricing[$prefix] ?? null;
            }
        }

        return null;
    }
}
