<?php
declare(strict_types=1);

namespace Szemul\MonologLoggingContext\Test\Formatter;

use Mockery;
use Mockery\MockInterface;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Szemul\LoggingErrorHandlingContext\ContextInterface;
use Szemul\MonologLoggingContext\Formatter\ContextAwareJsonFormatter;

class ContextAwareJsonFormatterTest extends TestCase
{
    private const DATE       = '2022-01-02T03:04:05.000000+00:00';
    private const CHANNEL    = 'testChannel';
    private const MESSAGE    = 'testMessage';
    private const CONTEXT    = ['context1' => 'contextValue'];
    private const EXTRAS     = ['extra1' => 'extraValue'];
    private const LOG_VALUES = ['log1' => 'logValue'];

    public function testFormat(): void
    {
        $record = $this->getLogRecord();
        $result = $this->getSut()->format($record);

        $this->assertJson($result);
        $this->assertJsonMatches(json_decode($result, true));
    }

    public function testFormatBatchJson(): void
    {
        $record = $this->getLogRecord();
        $result = $this->getSut()->formatBatch([$record, $record]);

        $this->assertJson($result);

        $decoded = json_decode($result, true);

        $this->assertCount(2, $decoded);
        $this->assertArrayHasKey(0, $decoded);
        $this->assertArrayHasKey(1, $decoded);
        $this->assertJsonMatches($decoded[0]);
        $this->assertJsonMatches($decoded[1]);
    }

    /**
     * @param array<string, mixed> $actual
     */
    private function assertJsonMatches(array $actual): void
    {
        $this->assertArrayHasKey('channel', $actual);
        $this->assertArrayHasKey('context', $actual);
        $this->assertArrayHasKey('extra', $actual);
        $this->assertArrayHasKey('message', $actual);
        $this->assertSame(self::MESSAGE, $actual['message']);
        $this->assertEquals($this->getExpectedContext(), $actual['context']);
        $this->assertEquals($this->getExpectedExtras(), $actual['extra']);
    }

    private function getContext(): ContextInterface&MockInterface
    {
        /** @var MockInterface&ContextInterface $context */
        $context = Mockery::mock(ContextInterface::class);

        // @phpstan-ignore-next-line
        $context->shouldReceive('getLogValues')
            ->withNoArgs()
            ->andReturn(self::LOG_VALUES);

        return $context;
    }

    private function getSut(): ContextAwareJsonFormatter
    {
        return new ContextAwareJsonFormatter($this->getContext(), version: 1);
    }

    private function getLogRecord(): LogRecord
    {
        return new LogRecord(
            \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', self::DATE),
            self::CHANNEL,
            Level::Info,
            self::MESSAGE,
            self::CONTEXT,
            self::EXTRAS,
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function getExpectedContext(): array
    {
        return array_merge(self::CONTEXT, [
            'channel'    => self::CHANNEL,
            'extra'      => $this->getExpectedExtras(),
            'level'      => 200,
            'level_name' => 'INFO',
            'msg'        => self::MESSAGE,
            'time'       => self::DATE,
            'v'          => 1,
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function getExpectedExtras(): array
    {
        return array_merge(self::EXTRAS, self::LOG_VALUES);
    }
}
