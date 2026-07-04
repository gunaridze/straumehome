<?php
namespace Imedia\Main\Helpers\Debug;

use Bitrix\Main\Diag;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;

class ExceptionHandlerLog extends Diag\ExceptionHandlerLog
{
    protected $dir;
    protected $logFile;
    protected $logFileHistory;
    protected $maxLogSize;

    const MAX_LOG_SIZE = 1000000;
    const DEFAULT_LOG_FILE = '/var/log/php/exceptions.log';

    public function initialize($options)
    {
        $this->logFile = static::DEFAULT_LOG_FILE;

        if(
            isset($options['dir'])
            && !empty($options['dir'])
        ){
            $this->dir = $options['dir'];
            $this->logFile = $this->dir . date('Y') . '/' . date('m') . '/' . date('d') . '.log';
        }

        $this->logFileHistory = $this->logFile . '.old';

        $this->maxLogSize = static::MAX_LOG_SIZE;
        if (
            isset($options['log_size'])
            && ($options['log_size'] > 0)
        ){
            $this->maxLogSize = $options['log_size'];
        }
    }

    public function write($exception, $logType)
    {
        $variable = Diag\ExceptionHandlerFormatter::format($exception, false, $this->level);

        static::writeToFile($variable, $logType);
    }

    protected function writeToFile($variable, $logType)
    {
        $title = date('Y-m-d H:i:s').' - Host: '.$_SERVER['HTTP_HOST'].' - '.static::logTypeToString($logType);
        $dirPath = $this->dir . date('Y') . '/' . date('m') . '/';

        try{
            static::createLogDirectory($dirPath);
        } catch(\Exception $e){
            return;
        }

        $text = $title . PHP_EOL . $variable . PHP_EOL;

        $userId = (CurrentUser::get()) ? CurrentUser::get()->getId() : '-';
        $text .= PHP_EOL . 'User id: ' . $userId;
        $text .= PHP_EOL . 'Site id: ' . SITE_ID;
        $text .= PHP_EOL . 'IP: ' . $_SERVER['REMOTE_ADDR'];

        $this->writeToLog($text);
    }

    protected function createLogDirectory($dirPath)
    {
        if (!mkdir($dirPath, 0775, true) && !is_dir($dirPath)) {
            throw new \Exception(Loc::getMessage('IMEDIA_MAIN_DEBUG_ERROR_DIR_CREATE'));
        }
    }

    protected function writeToLog($text)
    {
        if (empty($text)){
            return;
        }

        $logFile = $this->logFile;
        $logFileHistory = $this->logFileHistory;

        $oldAbortStatus = ignore_user_abort(true);

        if ($fp = @fopen($logFile, 'ab')){
            if (@flock($fp, LOCK_EX)){
                $logSize = @filesize($logFile);
                $logSize = intval($logSize);

                if ($logSize > $this->maxLogSize){
                    @copy($logFile, $logFileHistory);
                    ftruncate($fp, 0);
                }

                @fwrite($fp, $text);
                @fflush($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
            }
        }

        ignore_user_abort($oldAbortStatus);
    }
}