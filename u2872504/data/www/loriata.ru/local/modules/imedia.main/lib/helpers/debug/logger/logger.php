<?php
namespace Imedia\Main\Helpers\Debug\Logger;

use SplObjectStorage;
use Psr\Log;

class Logger extends Log\AbstractLogger implements Log\LoggerInterface
{
    public SplObjectStorage $routes;

    public function __construct()
    {
        $this->routes = new SplObjectStorage();
    }

    public function log($level, $message, array $context = []): void
    {
        foreach ($this->routes as $route){
            if (
                !$route instanceof Route
                || !$route->isEnable
            ){
                continue;
            }

            $route->log($level, $message, $context);
        }
    }
}