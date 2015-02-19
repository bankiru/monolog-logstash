<?php
namespace Bankiru\MonologLogstash;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Log\InvalidArgumentException;

class ZMQHandler extends AbstractProcessingHandler
{
    protected $dsn;
    protected $persistent;
    protected $options = array();
    protected $socketType;
    protected $socketOptions = array();
    /** @var \ZMQSocket */
    private $socket;

    /**
     * @param int $dsn
     * @param bool $persistent
     * @param array $options
     * @param $socketType
     * @param array $socketOptions
     * @param integer $level The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function __construct($dsn, $persistent = true, $options = array(), $socketType = \ZMQ::SOCKET_PUSH, $socketOptions = array(),
                         $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        if (!is_string($dsn) || !trim($dsn) || parse_url($dsn) === false) {
            throw new InvalidArgumentException('dsn is invalid');
        }

        if (parse_url($dsn, PHP_URL_SCHEME) != 'tcp') {
            throw new InvalidArgumentException('dsn has invalid schema');
        }

        if (!parse_url($dsn, PHP_URL_PORT)) {
            throw new InvalidArgumentException('no port specified in the dsn');
        }

        if (!preg_match('@^(/)?$@', parse_url($dsn, PHP_URL_PATH))
            || parse_url($dsn, PHP_URL_QUERY) != ''
            || parse_url($dsn, PHP_URL_FRAGMENT) != ''
        ) {
            throw new InvalidArgumentException("dsn '{$dsn}' has unexpected path or query");
        }

        if (!in_array($socketType, array(\ZMQ::SOCKET_PUSH, \ZMQ::SOCKET_REQ))) {
            throw new InvalidArgumentException('socketType is invalid');
        }

        $this->dsn = $dsn;
        $this->persistent = $persistent;
        $this->options = $options;
        $this->socketType = $socketType;
        $this->socketOptions = $socketOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->socket instanceof \ZMQSocket && !$this->socket->isPersistent()) {
            $this->socket->disconnect($this->dsn);
        }
        $this->socket = null;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function write(array $record)
    {
        $socket = $this->getSocket();
        if ($socket) {
            try {
                $socket->send((string)$record['formatted'], \ZMQ::MODE_DONTWAIT);
            } catch (\ZMQSocketException $e) {
                throw new \RuntimeException(sprintf('Could not write logs to logstash through ZMQ: %s', (string)$e));
            }
        }
    }

    /**
     * @return \ZMQSocket
     * @throws \RuntimeException
     */
    protected function getSocket()
    {
        if ($this->socket === null) {
            $exception = null;

            $socketOptions = $this->socketOptions;
            $dsn = $this->dsn;

            $this->socket = $this->getContext()->getSocket(
                $this->socketType,
                $this->persistent ? get_class($this) : null,
                function (\ZMQSocket $socket) use (&$exception, $socketOptions, $dsn) {
                    foreach ($socketOptions as $optKey => $optValue) {
                        $socket->setSockOpt($optKey, $optValue);
                    }

                    try {
                        $socket->connect($dsn);
                    } catch (\ZMQSocketException $e) {
                        $exception = $e;
                        $socket->disconnect($dsn);
                    }
                }
            );

            if ($exception) {
                $this->socket = null;
                throw new \RuntimeException(sprintf('Could not connect to logstash through ZMQ: %s', (string)$exception));
            }
        }

        return $this->socket;
    }

    /**
     * @return \ZMQContext
     */
    protected function getContext() {
        $context = new \ZMQContext();
        if ($this->options && method_exists($context, 'setOpt')) {
            foreach ($this->options as $optKey => $optValue) {
                $context->setOpt($optKey, $optValue);
            }
        }

        return $context;
    }
}