<?php
namespace Bankiru\MonologLogstash;

use Ekho\Logstash\Lumberjack;
use Monolog\Handler\AbstractProcessingHandler;

class LumberjackHandler extends AbstractProcessingHandler
{
    const DEFAULT_WINDOW_SIZE = 5000;

    /** @var Lumberjack\Client */
    private $client;

    private $enabled = true;

    /**
     * @param string $host IP or hostname
     * @param int $port Port where Logstash listen connections with input lumberjack
     * @param string $cafile path to certificate file
     * @param array $options various lumberjack options (@see Lumberjack\Client)
     * @return LumberjackHandler
     */
    public function init($host, $port, $cafile, array $options = array())
    {
        $windowSize = self::DEFAULT_WINDOW_SIZE;

        if (array_key_exists('windows_size', $options)) {
            $windowSize = $options['window_size'] ?: self::DEFAULT_WINDOW_SIZE;
            unset($options['window_size']);
        }

        try {
            $this->client = new Lumberjack\Client(
                new Lumberjack\SecureSocket(
                    $host,
                    $port,
                    array('ssl_cafile' => $cafile) + $options
                ),
                new Lumberjack\Encoder(),
                $windowSize
            );
        } catch (Lumberjack\Exception $ex) {
            $this->enabled = false;
            error_log($ex->getMessage());
        }

        return $this;
    }

    /**
     * @return Lumberjack\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Lumberjack\Client $client
     * @return LumberjackHandler
     */
    public function setClient(Lumberjack\Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $this->enabled && parent::isHandling($record);
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function write(array $record)
    {
        if (isset($record['formatted']) && is_array($record['formatted'])) {
            $record = $record['formatted'];
        } else {
            unset($record['formatted']);
        }

        try {
            $this->client->write($record);
        } catch (Lumberjack\Exception $ex) {
            $this->enabled = false;
            error_log($ex->getMessage());
        }
    }
}
