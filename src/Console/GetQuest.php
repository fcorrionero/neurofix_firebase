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

class GetQuest extends SymfonyCommand
{
    protected $firestore;
    protected $database;

    public function __construct($name = null)
    {
	$this->firestore = new FirestoreClient([
	    'keyFile' => json_decode(file_get_contents(__DIR__.'/firebase_credentials.json'), true)
	]);
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
	$users = $this->firestore->collection('usuarios');
	$documentReference = $users->document($uid);
	$collections = $documentReference->collections();
	foreach ($collections as $collection) {
		$documents = $collection->documents();
		foreach ($documents as $document) {
		    if ($document->exists()) {
		        printf('Document data for document %s:' . PHP_EOL, $document->id());
			//print_r(get_class_methods($document));
		        print_r($document->data());
		        printf(PHP_EOL);
		    } else {
	        	printf('Document %s does not exist!' . PHP_EOL, $snapshot->id());
		    }
		}
    	}
    }

}
