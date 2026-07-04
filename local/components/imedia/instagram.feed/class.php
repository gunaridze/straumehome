<?php

namespace Imedia\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Component\ParameterSigner;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
    die();
}

class Instagram extends \CBitrixComponent implements Controllerable, Errorable
{
    /** @var ErrorCollection */
    protected $errorCollection;

    public function configureActions()
    {
        return [
            'getData' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf()
                ]
            ]
        ];
    }

    public function getDataAction()
    {
        try{

            $this->includeComponentLang('class.php');

            if (empty($this->arParams['ACCESS_TOKEN'])) {
                $result = new Result();
                $result->addError( new Error( Loc::getMessage('IMEDIA_INSTAGRAM_ERROR_ACCESS_TOKEN_EMPTY') ) );
                return AjaxJson::createError( $result->getErrorCollection() );
            }

            $cache = Cache::createInstance();

            $cachePath = '/instagram';
            $cacheTtl = ($this->arParams['CACHE_TYPE'] === 'N') ? 0 : $this->arParams['CACHE_TIME'];
            $cacheKey = md5(ParameterSigner::signParameters($this->getName(), $this->arParams));

            if ($cache->initCache($cacheTtl, $cacheKey, $cachePath)){
                return $cache->getVars();
            } elseif ($cache->startDataCache()) {

                $result = $this->getResult();

                if(!($result->isSuccess())){
                    $cache->abortDataCache();
                    return AjaxJson::createError( $result->getErrorCollection() );
                }

                $cache->endDataCache($result->getData());

                return $result->getData();
            }

            return [];

        } catch (\Exception $e){
            $result = new Result();
            $result->addError( new Error( $e->getMessage() ) );
            return AjaxJson::createError( $result->getErrorCollection() );
        }
    }

    protected function listKeysSignedParameters()
    {
        return [
            'CACHE_TYPE',
            'CACHE_TIME',
            'CACHE_GROUPS',
            'ACCESS_TOKEN'
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
        if (empty($arParams['ACCESS_TOKEN'])){
            $this->errorCollection->setError(new Error(Loc::getMessage('IMEDIA_INSTAGRAM_ERROR_ACCESS_TOKEN_EMPTY')));
            return $arParams;
        }

        $this->arParams = $arParams;

        return $this->arParams;
    }

    protected function getResult(): Result
    {
        $result = new Result();

        $options = [
            'redirect' => true,
            'redirectMax' => 5,
            'waitResponse' => true,
            'socketTimeout' => 1,
            'streamTimeout' => 1,
            'version' => HttpClient::HTTP_1_1,
            'proxyHost' => '',
            'proxyPort' => '',
            'proxyUser' => '',
            'proxyPassword' => '',
            'compress' => false,
            'charset' => '',
            'disableSslVerification' => false,
        ];

        $httpClient = new HttpClient($options);
        $httpClient->setHeader('Content-Type', 'application/json');

        $url = new Uri('https://graph.instagram.com/me/media');
        $url->addParams(
            [
                'access_token' => $this->arParams['ACCESS_TOKEN'],
                'fields' => implode(',', [
                    'id',
                    'media_type',
                    'media_url',
                    'timestamp',
                    'thumbnail_url',
                    'permalink'
                ])
            ]
        );

        $isSuccess = $httpClient->get($url->getUri());
        if(!$isSuccess){
            $responseError = $httpClient->getError();
            $result->addError( new Error( '('.$responseError['httpStatusCode'].') ' . $responseError['status'] . ': ' . $responseError['errorMessage'] ) );
            return $result;
        }

        $items = Json::decode($httpClient->getResult())['data'];

        foreach($items as $key => $item){

            if(!$item['permalink']){
                unset($items[$key]);
                continue;
            }

            $items[$key]['thumbnail'] = $item['thumbnail_url'] ?: $item['media_url'];

        }

        usort($items, function($a, $b){
            return $b['timestamp'] <=> $a['timestamp'];
        });

        $result->setData($items);

        return $result;

    }

    public function executeComponent()
    {
        try {
            $this->includeComponentTemplate();
        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }
}