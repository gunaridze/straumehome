<?php
namespace Imedia\Main\Helpers\Debug;

use Bitrix\Main\Diag\Helper;
use Bitrix\Main\Config\Option;

class Debug
{
    public static function printr($arr, bool $getFile = false): string
    {
        $result = '';

        $currentUser = \Bitrix\Main\Engine\CurrentUser::get();

        if(
            $currentUser->isAdmin()
            || (Option::get('imedia.main', 'debug_view_all') === 'Y')
        ){
            $arStyle = [
                'pre' =>"
                    font-size: 12px; 
                    font-family: 'Consolas', Arial, sans-serif;
                    background: #293134;
                    padding: 10px 20px;
                    color: #d0c900;
                    overflow: scroll;
                    text-align: left;
                ",
                'file' =>"
                    background: #d0c900;
                    color: #293134;
                    margin: -10px -20px 10px -20px;
                    padding: 5px 20px;
                "
            ];

            $result .= '<pre style="'.$arStyle["pre"].'">';

            if($getFile){
                $backTrace = Helper::getBackTrace(1);
                $backTrace = $backTrace[0];
                $backTrace["file"] = str_replace($_SERVER["DOCUMENT_ROOT"], "", $backTrace["file"]);

                $result .= '<div style="'.$arStyle["file"].'">File: '.$backTrace["file"].' ('.$backTrace["line"].')</div>';
            }

            ob_start();
            print_r($arr);
            $result .= ob_get_clean();

            $result .= '</pre>';
        }

        return $result;
    }
}