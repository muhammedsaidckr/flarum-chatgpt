<?php

namespace Msc\ChatGPT\Tests\Unit;

use Msc\ChatGPT\ModelCatalog;
use PHPUnit\Framework\TestCase;

class ModelCatalogTest extends TestCase
{
    public function testRecommendationPriority(): void
    {
        $catalog = new ModelCatalog();
        $models = [
            ['id' => 'gpt-4o-2026-01-01'],
            ['id' => 'gpt-5-mini-2026-02-01'],
            ['id' => 'gpt-5-2026-02-01'],
        ];

        $recommended = $catalog->recommend($models, 'gpt-4o-2026-01-01');

        $this->assertSame('gpt-5-mini-2026-02-01', $recommended);
    }

    public function testBuildMetadataIncludesPresetAndFlags(): void
    {
        $catalog = new ModelCatalog();
        $metadata = $catalog->buildMetadata([
            ['id' => 'gpt-5-2026-02-01'],
            ['id' => 'gpt-4o-2026-01-01'],
        ]);

        $this->assertArrayHasKey('gpt-5-2026-02-01', $metadata);
        $this->assertTrue($metadata['gpt-5-2026-02-01']['is_reasoning_model']);
        $this->assertSame('responses', $metadata['gpt-5-2026-02-01']['api_mode']);
        $this->assertArrayHasKey('max_tokens', $metadata['gpt-5-2026-02-01']['preset']);
        $this->assertFalse($metadata['gpt-4o-2026-01-01']['is_reasoning_model']);
    }
}
