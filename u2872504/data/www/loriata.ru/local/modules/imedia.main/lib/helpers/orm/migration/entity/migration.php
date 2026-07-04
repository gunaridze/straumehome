<?php
namespace Imedia\Main\Helpers\Orm\Migration\Entity;


class Migration
{
    protected $connection;
    protected array $up;
    protected array $down;

    public function __construct($connection, array $up = [], array $down = [])
    {
        $this->connection = $connection;
        $this->up = $up;
        $this->down = $down;
    }

    public function up(): void
    {
        $this->migrate($this->up);
    }

    public function down(): void
    {
        $this->migrate($this->down);
    }

    protected function migrate(array $queryList): void
    {
        foreach($queryList as $query){
            $this->connection->query($query);
        }
    }
}