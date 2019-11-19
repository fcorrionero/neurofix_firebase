<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 19/11/19
 * Time: 11:48
 */

namespace App\Service;


use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Google\Cloud\Core\Timestamp;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class FirebaseService
{

    /** @var FirestoreClient */
    protected $firestore;

    protected $auth;

    public function __construct()
    {
        $this->firestore = new FirestoreClient([
            'keyFile' => json_decode(file_get_contents(__DIR__.'/../Console/firebase_credentials.json'), true)
        ]);

        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/../Console/firebase_credentials.json');
        $this->firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $this->auth = $this->firebase->getAuth();
    }

    public function getUserId($email)
    {
        try{
            return $this->auth->getUserByEmail($email)->uid;
        }catch (UserNotFound $e){
            return false;
        }
    }

    public function generateQuest($email)
    {
        $uid = $this->getUserId($email);
        if(empty($uid)) {
           return false;
        }
        $data = [
            'id' => $uid,
            'name' => $email,
        ];
        $this->generateQuestArray($data);
        return true;
    }

    protected function generateQuestArray($data)
    {
        $uid = $data['id'];
        $users = $this->firestore->collection('usuarios');
        $users->document($uid)->set($data);
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