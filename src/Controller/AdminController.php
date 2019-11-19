<?php
/**
 * Created by PhpStorm.
 * User: felipe
 * Date: 19/11/19
 * Time: 11:01
 */

namespace App\Controller;


use App\Service\FirebaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{

    public function index(Request $request, FirebaseService $firebaseService)
    {
        $key = $request->get('key');
        $email = $request->get('email');

        if(getenv('SECRET_KEY') == $key && !empty($email)) {
            $result = $firebaseService->generateQuest($email);
            if(false == $result){
                exit('KO');
            }
            exit('OK');
        }

        return $this->render('admin/index.html.twig');
    }

}