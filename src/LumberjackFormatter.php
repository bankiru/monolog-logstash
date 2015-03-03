<?php
namespace Bankiru\MonologLogstash;

use Monolog\Formatter\LogstashFormatter;

class LumberjackFormatter extends LogstashFormatter
{
    public function format(array $record)
    {
        $message = $this->normalize($record);

        if ($this->version === self::V1) {
            $message = $this->formatV1($message);
        } else {
            $message = $this->formatV0($message);
        }

        $message['@version'] = $this->version;

        if (isset($message['line']) && (isset($message['message']) || isset($message['@message']))) {
            $message['_line'] = $message['line'];
        }

        if (isset($message['message'])) {
            $message['line'] = $message['message'];
        } elseif (isset($message['@message'])) {
            $message['line'] = $message['@message'];
        }

        unset($message['@timestamp']);

        return $message;
    }
}
