<?php
use Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Main\Config,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Entity\Base,
	Bitrix\Main\ModuleManager,
    Imedia\Main\Sms\Events,
    Imedia\Main\Handlers,
	Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

Class imedia_main extends CModule
{
	function __construct()
	{
		$arModuleVersion = Array();
		include (__DIR__."/version.php");

		$this->MODULE_ID = "imedia.main";
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("IMEDIA_MAIN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("IMEDIA_MAIN_MODULE_DESCRIPTION");

		$this->PARTNER_NAME = Loc::getMessage("IMEDIA_MAIN_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("IMEDIA_MAIN_PARTNER_URI");

		$this->MODULE_SORT = 1;
		$this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
		$this->MODULE_GROUP_RIGHTS = 'Y';
	}

	public function isVersionD7()
	{
		return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
	}

	public function isModuleInstalled($module_id)
	{
		return ModuleManager::isModuleInstalled($module_id);
	}

	public function GetPath($notDocumentRoot = false)
	{
		if($notDocumentRoot)
		{
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
		}
		else
		{
			return dirname(__DIR__);
		}
	}

	function InstallDB()
	{
        Loader::includeModule($this->MODULE_ID);

        $connection = Application::getConnection();

        foreach($this->getModels() as $model){
            $tableName = $model::getTableName();
            if (!$connection->isTableExists($tableName)) {
                $model::getEntity()->createDbTable();
            }
        }
	}

	function UnInstallDB()
	{
        Loader::includeModule($this->MODULE_ID);

        $connection = Application::getConnection();

        foreach($this->getModels() as $model){
            $tableName = $model::getTableName();
            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }

		Option::delete($this->MODULE_ID);
	}

	function InstallEvents()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandler(
			'messageservice',
			'onGetSmsSenders',
			$this->MODULE_ID,
			Events::class,
			'registerProvider'
		);
        $eventManager->registerEventHandler(
			'main',
			'OnProlog',
			$this->MODULE_ID,
			Handlers\Main::class,
			'onProlog'
		);
	}

	function UnInstallEvents()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->unRegisterEventHandler(
			'messageservice',
			'onGetSmsSenders',
			$this->MODULE_ID,
			Events::class,
			'registerProvider'
		);
        $eventManager->unRegisterEventHandler(
            'main',
            'OnProlog',
            $this->MODULE_ID,
            Handlers\Main::class,
            'onProlog'
        );
	}

	function InstallFiles($arParams = Array())
	{
		if(Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath()."/install/components"))
		{
			CopyDirFiles($this->GetPath()."/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}
		else
		{
			throw new Bitrix\Main\IO\InvalidPathException($path);
		}

        if (Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . "/admin")) {
            CopyDirFiles($this->GetPath() . "/install/admin",
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);

            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles)) {
                        continue;
                    }

                    file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $this->MODULE_ID . "_" . $item,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->GetPath(true) . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }
        }

		return true;
	}

	function UnInstallFiles()
	{
		Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/imedia/");

        if (Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . "/admin")) {
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . $this->GetPath() . "/install/admin",
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles)) {
                        continue;
                    }

                    Bitrix\Main\IO\File::deleteFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $this->MODULE_ID . "_" . $item);
                }
                closedir($dir);
            }
        }

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		if($this->isVersionD7())
		{
			ModuleManager::registerModule($this->MODULE_ID);

			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();
		}
		else
		{
			$APPLICATION->ThrowException(Loc::getMessage("IMEDIA_MAIN_INSTALL_ERROR_VERSION"));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage("IMEDIA_MAIN_INSTALL_TITLE"), $this->GetPath()."/install/step.php");
	}

	function DoUninstall()
	{
		global $APPLICATION;

		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();

		if($request["step"] < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IMEDIA_MAIN_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep1.php");
		}
		elseif($request["step"] == 2)
		{
			$this->UnInstallEvents();
			$this->UnInstallFiles();

			if($request["savedata"] != "Y")
			{
				$this->UnInstallDB();
			}

			ModuleManager::unRegisterModule($this->MODULE_ID);

			$APPLICATION->IncludeAdminFile(Loc::getMessage("IMEDIA_MAIN_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep2.php");
		}
	}

    protected function getModels(): array
    {
        return [
            'DefferedProduct',
            'VerifyByPhone',
            'CouponEmail',
            'CatalogUpdate',
            'OrderAutoCancel'
        ];
    }
}