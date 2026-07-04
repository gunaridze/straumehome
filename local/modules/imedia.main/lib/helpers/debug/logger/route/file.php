<?php
namespace Imedia\Main\Helpers\Debug\Logger\Route;

use Bitrix\Main\Web\Json;
use Imedia\Main\Helpers\Debug\Logger\Route as Base;

class File extends Base
{
    public string $logDir;
    public string $template = '{date} {level} {message} {context}';

    protected static ?string $documentRoot = null;

    const LOGS_FOLDER = 'logs/';

    public function log($level, $message, array $context = []): void
    {
        $dir = static::LOGS_FOLDER . date('Y') . '/' . date('m') . '/';

        $dirPath = $this->getDocumentRoot() . '/' . $dir;

        try{
            $this->createLogDirectory($dirPath);

            if($this->logDir){
                $dir .= '/' . strtolower($this->logDir) . '/';
                $dirPath = $this->getDocumentRoot() . '/' . $dir;
                $this->createLogDirectory($dirPath);
            }
        } catch(\Exception $e){
            return;
        }

        $filePath = $dirPath . date('d') . '.log';

        file_put_contents($filePath, trim(strtr($this->template, [
                '{date}' => $this->getDate(),
                '{level}' => $level,
                '{message}' => $message,
                '{context}' => $this->contextStringify($context),
            ])) . PHP_EOL, FILE_APPEND);
    }

    /**
     * @param string $dirPath
     * @throws \Exception
     */
    protected function createLogDirectory(string $dirPath): void
    {
        try{
            $file = null;

            if (
                !mkdir($dirPath, 0775, true) &&
                !is_dir($dirPath)
            ) {
                throw new \Exception('Не удалось создать директорию для файла логов');
            }

            $htaccess = $this->getDocumentRoot() . '/' . static::LOGS_FOLDER . '.htaccess';
            if(!file_exists($htaccess)){
                $content = 'Deny from all';

                if (!$file = fopen($htaccess, 'w')) {
                    throw new \Exception('Не удалось создать .htaccess файл в директории логов');
                }

                if (!fwrite($file, $content)) {
                    throw new \Exception('Не удалось записать в .htaccess файл в директории логов');
                }

                fclose($file);
            }
        }
        catch(\Exception $e){
            if($file){
                fclose($file);
            }

            throw new \Exception($e->getMessage());
        }
    }

    protected function getDocumentRoot(): string
    {
        if(self::$documentRoot === null){
            self::$documentRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        return self::$documentRoot;
    }

    /**
     * @param string $string
     * @return array
     */
    public static function parse(string $string): array
    {
        $pattern = '/(.*)\s(debug|info|notice|warning|error|critical|alert|emergency)\s(.*)\s{(.*)}/';
        preg_match_all($pattern, $string, $matches);
        return [
            'date' => new \Bitrix\Main\Type\DateTime($matches[1][0], \DateTime::RFC2822),
            'level' => $matches[2][0],
            'message' => $matches[3][0],
            'context' => ($matches[4][0]) ? Json::decode('{'.$matches[4][0].'}') : null
        ];
    }
}