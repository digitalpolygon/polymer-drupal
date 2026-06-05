<?php

namespace DigitalPolygon\PolymerDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_drupal\Plugin\Common\RandomString;
use PHPUnit\Framework\TestCase;

class RandomStringTest extends TestCase
{
    public function testStringDefaultsToEightPrintableAsciiCharacters(): void
    {
        $value = RandomString::string();
        $this->assertSame(8, strlen($value));
        $this->assertMatchesRegularExpression('/^[\x20-\x7e]+$/', $value);
    }

    public function testStringHonorsRequestedLength(): void
    {
        $this->assertSame(32, strlen(RandomString::string(32)));
    }

    public function testStringRestrictsToProvidedCharacterPool(): void
    {
        $value = RandomString::string(64, false, null, 'abc');
        $this->assertMatchesRegularExpression('/^[abc]+$/', $value);
    }

    public function testStringConsultsValidator(): void
    {
        $seen = [];
        $value = RandomString::string(8, false, function (string $candidate) use (&$seen): bool {
            $seen[] = $candidate;
            // Reject the first candidate to force one regeneration.
            return count($seen) > 1;
        });
        $this->assertCount(2, $seen);
        $this->assertSame(end($seen), $value);
    }

    public function testStringThrowsWhenValidatorNeverAccepts(): void
    {
        $this->expectException(\RuntimeException::class);
        RandomString::string(8, false, static fn (): bool => false);
    }
}
