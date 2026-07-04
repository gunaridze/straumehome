<?php
namespace Imedia\Main\Helpers\Debug\Logger;

use \Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Psr\Log;

abstract class Route extends Log\AbstractLogger implements Log\LoggerInterface
{
    public bool $isEnable = true;
    public string $dateFormat = \DateTime::RFC2822;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $attribute => $value){
            if (property_exists($this, $attribute)){
                $this->{$attribute} = $value;
            }
        }
    }

    public function getDate()
    {
        return (new DateTime())->format($this->dateFormat);
    }

    public function contextStringify(array $context = [])
    {
        return !empty($context) ? Json::encode($context) : null;
    }
}