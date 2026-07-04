<?php
namespace Imedia\Main\Helpers\Iblock\Seo\Handler;

abstract class Base implements HandlerInterface
{
    public function apply(string $string, array $data): string
    {
        if(!$this->canHandle($string)){
            return $string;
        }

        $string = $this->handle($string, $data);

        return $string;
    }

    abstract protected function canHandle(string $string): bool;
    abstract protected function handle(string $string, array $data): string;
}