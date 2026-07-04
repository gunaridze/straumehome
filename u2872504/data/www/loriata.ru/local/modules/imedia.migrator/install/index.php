<?

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Imedia\Migrator\Entity;

IncludeModuleLangFile(__FILE__);

if (class_exists("imedia_migrator"))
	return;

class imedia_migrator extends CModule
{
    const MODULE_ID = 'imedia.migrator';

    var $MODULE_ID = 'imedia.migrator';

	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

    static $events = [];
    static $agents = [];
    static $entities = [
        Entity\MigratorTable::class,
    ];

	function __construct()
	{
		$arModuleVersion = array();

		include(dirname(__FILE__)."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = Loc::getMessage("IMEDIA_MIGRATOR_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("IMEDIA_MIGRATOR_INSTALL_DESCRIPTION");

		$this->PARTNER_NAME = Loc::getMessage("IMEDIA_MIGRATOR_PARTNER");
		$this->PARTNER_URI = "https://www.imedia.by";
	}

    public static function addAgents()
    {
        $now = new \Bitrix\Main\Type\DateTime();

        foreach (self::$agents as $agent) {
            $agent['MODULE_ID'] = self::MODULE_ID;
            $agent['NEXT_EXEC'] = $now;

            \CAgent::Add($agent);
        }
    }

	function InstallDB()
	{
        $connection = Application::getInstance()->getConnection();

        if (Loader::includeModule($this->MODULE_ID)) {
            foreach (self::$entities as $entity) {
                if(!$connection->isTableExists($entity::getTableName())) {
                    $entity::getEntity()->createDbTable();
                }
            }
        }

		return true;
	}

	function UnInstallDB()
	{
        $connection = Application::getInstance()->getConnection();
        if (Loader::includeModule($this->MODULE_ID)) {
            foreach (self::$entities as $entity) {
                if($connection->isTableExists($entity::getTableName())) {
                    $connection->dropTable($entity::getTableName());
                }
            }
        }

		return true;
	}

    public function registerEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach (self::$events as $event) {
            $eventManager->registerEventHandler(
                $event['module'],
                $event['event'],
                $this->MODULE_ID,
                $event['class'],
                $event['method'],
                $event['sort']
            );
        }
    }

    public function unRegisterEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach (self::$events as $event) {
            $eventManager->unRegisterEventHandler(
                $event['module'],
                $event['event'],
                $this->MODULE_ID,
                $event['class'],
                $event['method']
            );
        }
    }

	function InstallFiles()
	{
		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->installDB();
        $this->installFiles();
        $this->registerEvents();
        $this->addAgents();
    }

    public function DoUninstall()
    {
        $this->unInstallFiles();
        $this->uninstallDB();
        $this->unRegisterEvents();

        \CAgent::RemoveModuleAgents($this->MODULE_ID);

        ModuleManager::unregisterModule($this->MODULE_ID);
    }
}