<?php
namespace Bankiru\MonologLogstash;

use Ekho\Logstash\Lumberjack\Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class LumberjackHandler extends AbstractProcessingHandler
{
    /** @var Client */
    private $client;

    /**
     * @param Client $client
     * @param integer $level The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(Client $client, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return LumberjackHandler
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function write(array $record)
    {
        $this->client->write($record);
    }
}
