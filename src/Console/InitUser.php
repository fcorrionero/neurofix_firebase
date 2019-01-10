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
use Google\Cloud\Firestore\FirestoreClient;

class InitUser extends SymfonyCommand
{
    protected $firestore;
    protected $database;
    protected $firebase;
    protected $auth;

    public function __construct($name = null)
    {
	$this->firestore = new FirestoreClient([
	    'keyFile' => json_decode(file_get_contents(__DIR__.'/firebase_credentials.json'), true)
	]);
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
        $this->setName('init:user');
        $this->addArgument('uid', InputArgument::REQUIRED, 'User uid');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Init-user command executed!');
        $uid = $input->getArgument('uid');
        $users = $this->firestore->collection('usuarios');
	$authUsers = $this->auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
	$data = [];
        foreach ($authUsers as $user) {
            /** @var \Kreait\Firebase\Auth\UserRecord $user */
            $output->writeln($user->uid);
	    if($user->uid == $uid){
		$data = [
			'id' => $user->uid,
			'name' => $user->email,
		];
		break;
	    }
        }
	$users->document($uid)->set($data);
	$users->document($uid)->collection('quest')->document('1')->set(['text'=>'Hello Wordl!']);
    }

}
