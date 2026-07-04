<?php
namespace Imedia\Main\Helpers\Agent\Catalog\DefferedProduct;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use Imedia\Main\Helpers\Agent\Base;
use Imedia\Main\Models\DefferedProduct\DefferedProductTable;

class RemoveOld extends Base
{
    protected function _process()
    {
        $keepDays = (int) Option::get('imedia.main', 'deffered_products_keep_days');

        if($keepDays > 0){

            $date = new Type\DateTime();
            $date->add('- ' . $keepDays . ' days');

            $query = DefferedProductTable::getList([
                'filter' => [
                    '<=DATE' => $date
                ]
            ]);

            while($defferedProduct = $query->fetchObject()){
                $defferedProduct->delete();
            }

        }
    }
}