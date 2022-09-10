<?php
declare(strict_types=1);

namespace Szemul\MonologLoggingContext\Formatter;

use Monolog\Formatter\JsonFormatter;
use Szemul\LoggingErrorHandlingContext\ContextInterface;

class ContextAwareJsonFormatter extends JsonFormatter
{
    public function __construct(
        protected ContextInterface $context,
        int $batchMode = self::BATCH_MODE_JSON,
        bool $appendNewline = true,
        protected string $jsonDateFormat = 'Y-m-d\TH:i:s.uP',
    ) {
        parent::__construct($batchMode, $appendNewline);
    }

    /**
     * @param array<string,mixed> $record
     *
     * @return array<string,mixed>
     */
    protected function getReformattedRecord(array $record): array
    {
        $additionalExtras = $this->context->getLogValues();

        $record['extra'] = array_merge($record['extra'] ?? [], $additionalExtras);

        return array_merge(
            $record['context'],
            [
                'msg'        => $record['message'],
                'channel'    => $record['channel'],
                'level'      => $record['level'],
                'level_name' => $record['level_name'],
                'time'       => $record['datetime']->format($this->jsonDateFormat),
                'extra'      => $record['extra'],
                'v'          => 0,
            ],
        );
    }

    /** @param array<string,mixed> $record */
    public function format(array $record): string
    {
        return parent::format($this->getReformattedRecord($record));
    }

    /** @param array<array<string,mixed>> $records */
    protected function formatBatchJson(array $records): string
    {
        return parent::formatBatchJson(array_map(fn (array $record) => $this->getReformattedRecord($record), $records));
    }
}
