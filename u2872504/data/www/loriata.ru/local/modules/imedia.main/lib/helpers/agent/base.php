<?php

namespace Imedia\Main\Helpers\Agent;

use Imedia\Main\Helpers\Debug\Logger;

abstract class Base
{
    protected Logger\Logger $logger;

    protected function __construct()
    {
        $this->logger = new Logger\Logger();
        $this->logger->routes->attach(new Logger\Route\File(
            [
                'isEnable' => true,
                'logDir' => 'agent/' .str_replace(__NAMESPACE__, '', static::class)
            ]
        ));
    }

    public static function process(): string
    {
        $process = new static();

        try{
            $process->_process();
        } catch (\Exception $e){
            $process->logger->critical($e->getMessage());
        }

        return '\\' . static::class . '::process();';
    }

    abstract protected function _process();
}