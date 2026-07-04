<?php
namespace Imedia\Main\Helpers\Iblock\Seo\Handler;

interface HandlerInterface
{
    public function apply(string $string, array $data): string;
}