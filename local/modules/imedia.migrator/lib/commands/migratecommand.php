<?php

namespace Imedia\Migrator\Commands;

use Arrilot\BitrixMigrations\Migrator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Arrilot\BitrixMigrations\Commands\AbstractCommand;

class MigrateCommand extends AbstractCommand
{
    /**
     * Migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    protected static $defaultName = 'migrate';
    /**
     * Constructor.
     *
     * @param Migrator    $migrator
     * @param string|null $name
     */
    public function __construct(Migrator $migrator, $name = null)
    {
        $this->migrator = $migrator;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Run all outstanding migrations');
    }

    /**
     * Execute the console command.
     *
     * @return null|int
     */
    protected function fire()
    {
        $toRun = $this->migrator->getMigrationsToRun();

        if (!empty($toRun)) {
            foreach ($toRun as $migration) {
                $this->migrator->runMigration($migration);
                $this->message("<info>Migrated:</info> {$migration}.php");
            }
        } else {
            $this->info('Nothing to migrate');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        try {
            return $this->fire();
        } catch (\DomainException $e) {
            return 1;
        } catch (\Exception $e) {
            if ($this->getApplication()->areExceptionsCaught()) {
                $this->error($e->getMessage());
                $this->error('Abort!');

                return $e->getCode();
            } else {
                throw $e;
            }
        }
    }
}
