<?php
/**
 * Created by PhpStorm.
 * User: felipesanchez
 * Date: 10/01/2019
 * Time: 09:07
 */

namespace App\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class GetQuest extends SymfonyCommand
{
    protected $firebase;
    protected $database;

    public function __construct($name = null)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/firebase_credentials.json');
        $this->firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $this->database = $this->firebase->getDatabase();
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('get:quest');
        $this->addArgument('uid', InputArgument::REQUIRED, 'User uid');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('List user quest executed!');
        $uid = $input->getArgument('uid');
        $reference = $this->database->getReference('usuarios/'.$uid.'/quest');
        $quest = $reference->getValue();
        $output->writeln(print_r($quest,true));
    }

}