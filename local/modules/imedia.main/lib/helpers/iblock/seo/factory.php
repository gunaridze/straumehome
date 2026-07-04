<?php
namespace Imedia\Main\Helpers\Iblock\Seo;

class Factory
{
    public static function create(string $type): Processor
    {
        switch($type){
            case 'element':

                return (new Processor())
                    ->addHandler(
                        new Handler\Element\MinPrice()
                    )
                    ;

            case 'section':

                return (new Processor())
                    ->addHandler(
                        new Handler\Section\MinPrice()
                    )
                    ;

            default:
                throw new \OutOfRangeException('Type out of range');
        }
    }
}