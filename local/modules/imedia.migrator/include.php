<?
\Bitrix\Main\Loader::registerAutoLoadClasses('imedia.migrator', [
    'Imedia\Migrator\Entity\MigratorTable' => 'lib/entity/migrator.php',
    'Imedia\Migrator\Commands\MigrateCommand' => 'lib/commands/migratecommand.php'
]);

