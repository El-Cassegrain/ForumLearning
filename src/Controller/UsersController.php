<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EditProfileType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
* @Route("/profil")
*/
class UsersController extends AbstractController
{
    /**
    * @Route ("/" , name="profil")
    */
    public function index(): Response
    {
        return $this->render('users/index.html.twig');
    }
    
    /**
    * @Route ("/edit" , name="profil_edit")
    */
    public function edit(Request $request)
    {
        $user= $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('profil');
        }

        return $this->render('users/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
    * @Route ("/editPassword" , name="password_edit")
    */
    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();
            $old_pwd = $request->get('old_password'); 
            $new_pwd = $request->get('new_password'); 
            $new_pwd_confirm = $request->get('new_password_confirm');
            $user = $this->getUser();
            $checkPass = $passwordEncoder->isPasswordValid($user, $old_pwd);
            if($checkPass === true) {
                if($new_pwd == $new_pwd_confirm){
                    $new_pwd_encoded = $passwordEncoder->encodePassword($user, $new_pwd_confirm); 
                    $user->setPassword($new_pwd_encoded);
                    $em->flush();
                    $this->addFlash('message', 'Mot de passe mis à jour');

                    return $this->redirectToRoute('profil');
                }
                else $this->addFlash('error', 'Les deux mots de passe sont différents');
            } 
            else {
                $this->addFlash('error', 'Ancien mot de passe incorrect');
            }
        }
        return $this->render('users/editPassword.html.twig');
    }
}
