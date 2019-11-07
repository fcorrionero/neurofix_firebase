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
use Google\Cloud\Core\Timestamp;
use Morrislaptop\Firestore\Factory as MorrisFactory;

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
        $this->firebase = (new MorrisFactory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $firebase =  (new Factory)
          ->withServiceAccount($serviceAccount)
          ->create();
        $this->auth = $firebase->getAuth();
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('init:user');
        //$this->addArgument('uid', InputArgument::REQUIRED, 'User uid');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Init-user command executed!');
        //$uid = $input->getArgument('uid');
        $users = $this->firestore->collection('usuarios');
        $authUsers = $this->auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
        $data = [];
        $motherfukers = [
          '0Ub1HIt32cMm0D2iWp3PrzmpAM33',
          '1HCSDIVHeSRnAL28RgvBYstVxYf2',
          '1YyX8xH5EKQDkcPoZ0D8WMTEKWi1',
          '3efntxOZeESOYWkfe0oIrO7Gs292',
          '4nzz2QNUubMiuztj7JjV5aULU302',
          '5H2Cala6ZlNcGb4vmCviPpCjlVY2',
          '5LgXsIgwl9P72RB1NTRpNTirGs73',
          '5qARuSqULUXUrAKQ3B4gjeTlNCH2',
          '5taQAMHydhYVufiMF42yGSfSUlr2',
          '6CZX02XuoVUV2gSUeuinCIGlrQn1',
          '707WScsit3Yias7buuSige7qRYy1',
          '7NXtH3VAlVWcUIpXqtsq7klDODk2',
          '8QktGpXsARQpBlsLi6tJzaUDDgH3',
          '8vICldYeBIWTwcgV76P3CfxJy6x2',
          '8yk9J7JIEjbytoHHCdxLfrReWYP2',
          '96SbyeUJffatPha6pklZQhQLCSe2',
          '9MxPK2LQgoTYNbsSq8aGz4piehi2',
          '9cCNMAuYM2Zj5jrjupSNgCf3CRq1',
          '9fYNpkT3WhZUfKWgSXWNC530vMe2',
          'AKiZpNGaU4VLtsj1CqkxUUFkDHL2',
          'AULSNeOG5iZAPp8KucB3XmBIV5J3',
          'AhqbpmUlQ4ZGFOOEKrB9uZgxqey2',
        ];
        foreach ($authUsers as $user) {
            /** @var \Kreait\Firebase\Auth\UserRecord $user */
            $output->writeln($user->uid);
            if(in_array($user->uid,$motherfukers)){
              continue;
            }
            //if($user->uid == $uid){
              $data = [
                'id' => $user->uid,
                'name' => $user->email,
              ];
              //break;
            //}
          $users->document($user->uid)->set($data);
          $this->generateQuestArray($user->uid,$users);
          $output->writeln("QUEST GENERATED.");
        }
      $output->writeln("FINISH.");
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
      # [START fs_batch_write]
      //$batch = $this->firestore->batch();
      foreach($items as $key => $item) {
        $document = $users->document($uid)->collection('quest')->document($key)->set($item);
        //$batch->set($document,$item);
      }
      //$batch->commit();
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
