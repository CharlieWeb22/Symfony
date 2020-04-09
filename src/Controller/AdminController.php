<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\EditUserType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    /**
     * Liste des utilisateurs du site
     * @Route("/utilisateurs",name="utilisateurs")
     */
    public function usersList(UserRepository $users){
        return $this->render("admin/users.html.twig",[
            'users' => $users->findAll()
        ]);

    }
    /**
     * Modifier un utilisateur
     * @Route("/utilisateur/modifier/{id}",name="modifier_utilisateur")
     */
    public function editUser(User $users, Request $request){
        $form = $this->createForm(EditUserType::class, $users);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($users);
            $entityManager->flush();

            $this->addFlash('message', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_utilisateurs');
        }
        return $this->render('admin/edituser.html.twig', [
            'userForm' => $form->createView()
        ]);
    }
}
