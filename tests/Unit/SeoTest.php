<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Seo;
use PHPUnit\Framework\TestCase;

final class SeoTest extends TestCase
{
    public function testTagsContainTitleAndOpenGraph(): void
    {
        $html = Seo::tags([
            'title'       => 'Massage californien — ZenSpace',
            'description' => 'Un massage enveloppant et relaxant.',
        ]);

        $this->assertStringContainsString('<title>Massage californien — ZenSpace</title>', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('name="twitter:card"', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
    }

    public function testTagsEscapeHtmlInTitle(): void
    {
        $html = Seo::tags(['title' => '<script>alert(1)</script>']);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testServiceBlockIncludesAggregateRatingWhenProvided(): void
    {
        $block = Seo::service(
            ['title' => 'Spa', 'description' => 'Accès spa', 'price' => '49.90'],
            4.5,
            12
        );
        $this->assertSame('Service', $block['@type']);
        $this->assertSame(4.5, $block['aggregateRating']['ratingValue']);
        $this->assertSame(12, $block['aggregateRating']['reviewCount']);
    }

    public function testServiceBlockOmitsRatingWhenNone(): void
    {
        $block = Seo::service(['title' => 'Spa', 'description' => 'x', 'price' => '49.90'], null, 0);
        $this->assertArrayNotHasKey('aggregateRating', $block);
    }
}
