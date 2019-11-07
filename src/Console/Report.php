<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 25/05/19
 * Time: 9:40
 */

namespace App\Console;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Google\Cloud\Firestore\FirestoreClient;
use Morrislaptop\Firestore\Factory as MorrisFactory;


class Report extends SymfonyCommand
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

    public function configure() {
        $this->setName('generate:report');
        $this->addArgument('month', InputArgument::REQUIRED, 'Report Month');
        $this->addArgument('year', InputArgument::REQUIRED, 'Report Year');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $year = $input->getArgument('year');
        $month = $input->getArgument('month');
        $dateStart = \DateTime::createFromFormat('Y-m-d',$year.'-'.$month.'-01');
        $dateEnd = \DateTime::createFromFormat('Y-m-d', $dateStart->format('Y-m-t'));
        $users = $this->firestore->collection('usuarios');
        $authUsers = $this->auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
        $informeUsers = [
            'informe1@neurofix.com',
            'informe2@neurofix.com',
            'informe3@neurofix.com',
            'informe4@neurofix.com',
            'informe5@neurofix.com',
        ];
        $reportUsers = $this->filterUsers($authUsers, $informeUsers);
        $rows        = $this->getUserRows($reportUsers, $users, $dateStart, $dateEnd);
        $dates       = $this->getDates($rows);
        $newRows     = $this->getReportRows($rows, $dates);

        $header = 'USUARIO;CENTRO';
        foreach($dates as $day => $hours){
            foreach($hours as $hour){
                $header .= ';'.$day;
            }
        }
        $output->writeln($header);
        $subheader = ';';
        foreach($dates as $day => $hours){
            foreach ($hours as $hour) {
                $subheader .= ';'.$hour;
            }
        }
        $output->writeln($subheader);
        foreach($newRows as $user => $rows){
            $text = $user.';';
            $i=0;
            foreach($rows as $line) {
               if(0==$i){
                   $i++;
                   continue;
               }
               foreach($line as $day => $hours) {
                       $text .= ';'.$hours;
               }
               $i++;
            }
            $output->writeln($text);
        }

    }

    /**
     * @param $row
     * @return array
     */
    protected function getDates($rows): array
    {
        $dates = [];
        foreach ($rows as $email => $line) {
            foreach($line as $row){
                if (!in_array($row['date'], $dates)) {
                    $dates[] = $row['date'];
                }
            }
        }
        $subdates = [];
        foreach($dates as $row){
            $date = \DateTime::createFromFormat('Y-m-d H:i', $row);
            $subdates[$date->format('Y-m-d')][] = $date->format('H:i');
        }
        return $subdates;
    }

    /**
     * @param $reportUsers
     * @param $users
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    protected function getUserRows($reportUsers, $users, $dateStart, $dateEnd): array
    {
        $rows = [];
        foreach ($reportUsers as $user) {
            $collection = $users->document($user->uid)->collection('quest');
            $collectionQuery = $collection
                ->where('date', '>=', $dateStart)
                ->where('date', '<=', $dateEnd)
                ->orderBy('date');
            foreach ($collectionQuery->documents() as $document) {
                $date = $document->get('date')->get();
                $rows[$user->email][] = [
                    'date' => $date->format('Y-m-d H:i'),
                    'done' => $document->get('done')
                ];
            }
        }
        return $rows;
    }

    /**
     * @param $rows
     * @param $dates
     * @return array
     */
    protected function getReportRows($rows, $dates): array
    {
        $newRows = [];
        foreach ($rows as $email => $row) {
            $newRows[$email]['email'] = $email;
            foreach ($dates as $day => $date) {
                foreach($date as $hour){
                    $time = \DateTime::createFromFormat('Y-m-d H:i',$day.' '.$hour);
                    $line = array_filter($row, function ($r) use ($time) {
                        return $r['date'] == $time->format('Y-m-d H:i');
                    });
                    $line = array_shift($line);
                    $newRows[$email][$day][$hour] = $line['done'];
                }
            }
        }
        return $newRows;
    }

    /**
     * @param $authUsers
     * @param $informeUsers
     * @return array
     */
    protected function filterUsers($authUsers, $informeUsers): array
    {
        $reportUsers = [];
        foreach ($authUsers as $user) {
            if (in_array($user->email, $informeUsers)) {
                $reportUsers[] = $user;
            }
        }
        return $reportUsers;
    }
}