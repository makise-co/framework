<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Log\Formatter;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

class JsonFormatter extends BaseJsonFormatter
{
    /**
     * JsonFormatter constructor.
     * @param int $batchMode
     * @param bool $appendNewline
     */
    public function __construct($batchMode = BaseJsonFormatter::BATCH_MODE_JSON, $appendNewline = true)
    {
        parent::__construct($batchMode, $appendNewline);

        $this->includeStacktraces();
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {
        return $this->toJson($this->customNormalize($record), true) . ($this->appendNewline ? "\n" : '');
    }

    /**
     * @param array $data
     * @param int $depth
     * @return array
     * @throws \Exception
     */
    protected function customNormalize(array $data, int $depth = 0): array
    {
        /** @var \DateTime $date */
        $date = isset($data['datetime']) && $data['datetime'] instanceof \DateTime
            ? $data['datetime']
            : new \DateTime();

        $normalizedData = $this->normalize($data, $depth);
        $level = \strtolower($data['level_name'] ?? 'info');
        unset(
            $normalizedData['datetime'],
            $normalizedData['level'],
            $normalizedData['level_name'],
            $normalizedData['channel']
        );

        return \array_merge(
            [
                'level' => $level,
                'ts' => $date->format('U.u'),
            ],
            $normalizedData
        );
    }
}
