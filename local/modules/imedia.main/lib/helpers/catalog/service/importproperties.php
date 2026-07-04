<?php
namespace Imedia\Main\Helpers\Catalog\Service;

use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Imedia\Main\Models\CatalogUpdate;

class ImportProperties
{
    protected CatalogUpdate\CatalogUpdates $collection;
    protected int $counter;
    protected int $total;

    protected const FLUSH_LIMIT = 100;

    protected function __construct()
    {
        $this->collection = new CatalogUpdate\CatalogUpdates();
        $this->counter = 0;
        $this->total = 0;
    }

    public static function process(string $filepath, string $delimiter): Result
    {
        $result = new Result();
        $process = new static();

        try {

            $fp = fopen($filepath, 'r');
            if($fp === false){
                throw new \Exception('File open failed');
            }

            $currentItem = null;

            while($data = fgetcsv($fp, 0, $delimiter)){

                [$internalCode, $property, $value] = $data;

                if(!$internalCode || !$property){
                    continue;
                }

                if(
                    ($currentItem['INTERNAL_CODE'] !== $internalCode)
                    || ($currentItem['PROPERTY'] !== $property)
                ){

                    if($currentItem['INTERNAL_CODE']){
                        $process->push($currentItem);
                    }

                    $currentItem['INTERNAL_CODE'] = $internalCode;
                    $currentItem['PROPERTY'] = $property;
                    $currentItem['VALUE'] = null;

                }

                $currentItem['VALUE'][] = $value;

            }

            if($currentItem['VALUE']){
                $process->push($currentItem);
            }

            if($process->counter > 0){
                $process->flush();
            }

        } catch (\Exception $e){
            $result->addError(new Error($e->getMessage(), $e->getCode()));
        }

        $result->setData(
            [
                'TOTAL' => $process->total
            ]
        );

        return $result;
    }

    protected function push(array $arItem): void
    {
        if(count($arItem['VALUE']) < 2){
            $arItem['VALUE'] = current($arItem['VALUE']);
        }

        $obj = new CatalogUpdate\CatalogUpdate();
        $obj->setInternalCode($arItem['INTERNAL_CODE']);
        $obj->setData(
            [
                'PROPERTY' => $arItem['PROPERTY'],
                'VALUE' => $arItem['VALUE']
            ]
        );

        $this->collection[] = $obj;
        $this->counter++;

        if($this->counter >= static::FLUSH_LIMIT){
            $this->flush();
        }

    }

    protected function flush(): void
    {
        $result = $this->collection->save(true);
        if(!($result->isSuccess())){
            throw new \Exception(implode(', ', $result->getErrorMessages()));
        }

        $this->collection = new CatalogUpdate\CatalogUpdates();
        $this->total += $this->counter;
        $this->counter = 0;
    }
}