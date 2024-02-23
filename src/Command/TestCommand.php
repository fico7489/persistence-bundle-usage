<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Fico7489\PersistenceBundle\Event\UpdatedEntity;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[AsCommand(name: 'app:test')]
class TestCommand extends Command
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->entityManager;

        // updating a schema in sqlite database
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);

        /** @var EventDispatcher $eventDispatcher */
        $this->eventDispatcher->addListener(UpdatedEntity::class, function ($event) : void {
            dump('Event is here!');
        });

        // create, persist and flush entity
        $user = new User();
        $user->setName('test');
        $entityManager->persist($user);
        $entityManager->flush();

        return Command::SUCCESS;
    }
}