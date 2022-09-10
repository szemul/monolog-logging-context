<?php
declare(strict_types=1);

namespace Szemul\MonologLoggingContext\Formatter;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;
use Szemul\LoggingErrorHandlingContext\ContextInterface;

class ContextAwareJsonFormatter extends JsonFormatter
{
    public function __construct(
        protected ContextInterface $context,
        int $batchMode = self::BATCH_MODE_JSON,
        bool $appendNewline = true,
        bool $ignoreEmptyContextAndExtra = false,
        bool $includeStacktraces = false,
        protected string $jsonDateFormat = 'Y-m-d\TH:i:s.uP',
        protected int $version = 0,
    ) {
        parent::__construct($batchMode, $appendNewline, $ignoreEmptyContextAndExtra, $includeStacktraces);
    }

    protected function getReformattedRecord(LogRecord $record): LogRecord
    {
        $additionalExtras = $this->context->getLogValues();

        $extras = array_merge($record->extra, $additionalExtras);

        return new LogRecord(
            $record->datetime,
            $record->channel,
            $record->level,
            $record->message,
            array_merge(
                $record->context,
                [
                    'msg'        => $record->message,
                    'channel'    => $record->channel,
                    'level'      => $record->level->value,
                    'level_name' => $record->level->getName(),
                    'time'       => $record->datetime->format($this->jsonDateFormat),
                    'extra'      => array_merge($record->extra, $additionalExtras),
                    'v'          => $this->version,
                ],
            ),
            $extras,
            $record->formatted,
        );
    }

    public function format(LogRecord $record): string
    {
        return parent::format($this->getReformattedRecord($record));
    }

    protected function formatBatchJson(array $records): string
    {
        return parent::formatBatchJson(
            array_map(fn (LogRecord $record) => $this->getReformattedRecord($record), $records),
        );
    }
}
