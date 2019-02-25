<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class GenerateQuest extends SymfonyCommand
{

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
        $this->setName('generate:quest');
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
    $this->generateQuestArray($uid,$users);
  }

  protected function generateQuestArray($uid,$users)
  {
    $timestamp = new Timestamp(new \DateTime('2003-02-05 11:15:02.421827Z'));
    $dates = $this->generateDates();
    $i = 1;
    $items = [];
    foreach($dates as $date) {
      $item = [
        'index' => $i,
        'date'  => new Timestamp($date),
        'created_at' => new Timestamp(new \DateTime()),
        'updated_at' => '',
        'done' => 0,
      ];
      $items[$i] = $item;
      $i++;
    }
    foreach($items as $key => $item) {
      $users->document($uid)->collection('quest')->document($key)->set($item);
    }
  }

  protected function generateDates()
  {
    $dates = [];
    $now = new \DateTime();
    $hour = $now->format('H');
    $minute = $now->format('i');
    if($hour < 9){
      $date = new \DateTime();
      $date->setTime(9,0,0);
      $dates[] = $date;
      $date = new \DateTime();
      $date->setTime(16,0,0);
      $dates[] = $date;
      $date = new \DateTime();
      $date->setTime(23,59,0);
      $dates[] = $date;
    }else if($hour < 16){
      $date = new \DateTime();
      $date->setTime(16,0,0);
      $dates[] = $date;
      $date = new \DateTime();
      $date->setTime(23,59,0);
      $dates[] = $date;
    }else if($hour <= 23 && $minute <= 59){
      $date = new \DateTime();
      $date->setTime(23,59,0);
      $dates[] = $date;
    }

    for($i=1;$i<=270;$i++){
      $date = new \DateTime();
      $date->add(new \DateInterval('P'.$i.'D'));
      $date->setTime(9,0,0);
      $dates[] = $date;

      if($i == 89 || $i == 179 || $i == 269) {
        $date = new \DateTime();
        $date->add(new \DateInterval('P'.$i.'D'));
        $date->setTime(12,0,0);
        $dates[] = $date;
      }

      $date = new \DateTime();
      $date->add(new \DateInterval('P'.$i.'D'));
      $date->setTime(16,0,0);
      $dates[] = $date;
      $date = new \DateTime();
      $date->add(new \DateInterval('P'.$i.'D'));
      $date->setTime(23,59,0);
      $dates[] = $date;
    }

    return $dates;
  }

}