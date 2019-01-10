<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class CreateUser extends SymfonyCommand
{

    protected $firebase;
    protected $auth;

    public function __construct($name = null)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/firebase_credentials.json');
        $this->firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $this->auth = $this->firebase->getAuth();
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('create:user');
        $this->addArgument('username', InputArgument::REQUIRED, 'Email as username');
        $this->addArgument('password', InputArgument::REQUIRED, 'Password for user');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Create user command executed!');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $userProperties = [
            'email' => $username,
            'emailVerified' => true,
            //'phoneNumber' => '',
            'password' => $password,
            'displayName' => $username,
            //'photoUrl' => '',
            'disabled' => false,
        ];
        $createdUser = $this->auth->createUser($userProperties);

        $users = $this->auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
        foreach ($users as $user) {
            /** @var \Kreait\Firebase\Auth\UserRecord $user */
            $output->writeln($user->email);
            $output->writeln($user->uid);
        }
    }

}