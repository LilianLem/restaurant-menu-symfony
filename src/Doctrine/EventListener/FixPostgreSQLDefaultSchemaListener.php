<?php

namespace App\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

/**
 * Problem: https://github.com/doctrine/dbal/issues/1110
 * Fix: https://gist.github.com/vudaltsov/ec01012d3fe27c9eed59aa7fd9089cf7
 */
#[AsDoctrineListener(event: ToolEvents::postGenerateSchema, connection: "default")]
final class FixPostgreSQLDefaultSchemaListener
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schemaManager = $args
            ->getEntityManager()
            ->getConnection()
            ->createSchemaManager();

        if (!$schemaManager instanceof PostgreSQLSchemaManager) {
            return;
        }

        $schema = $args->getSchema();

        foreach ($schemaManager->listSchemaNames() as $namespace) {
            if (!$schema->hasNamespace($namespace)) {
                $schema->createNamespace($namespace);
            }
        }
    }
}