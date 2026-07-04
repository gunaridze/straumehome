<?php
namespace Imedia\Main\Helpers\Iblock\Seo;

class Processor
{
    protected array $handlers;

    public function __construct()
    {
        $this->handlers = [];
    }

    public function addHandler(Handler\HandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    public function process(string $string, array $data): string
    {
        foreach($this->handlers as $handler){
            $string = $handler->apply($string, $data);
        }

        return $string;
    }
}