<?php

namespace UtilisateurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\FOSUserEvents;
use UtilisateurBundle\Entity\Utilisateur;
use UtilisateurBundle\Helper\RoleService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * Utilisateur controller.
 *
 * @Route("user")
 */
class UtilisateurController extends Controller
{
    /**
     * @Route("/list", name="utilisateur_list")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            //throw $this->createAccessDeniedException();
            return $this->redirectToRoute('stock_stock_index');

        }
        $em = $this->getDoctrine()->getManager();

        $itemsPerPage = $this->container->getParameter('per_page');
        $repository = $em->getRepository('UtilisateurBundle:Utilisateur');
        $sort = array($request->query->get('sort', ''), $request->query->get('direction', 'asc'));
        $filters = null;
        $dates = null;

        if (isset($_REQUEST['filters'])) {
            $filters = $_REQUEST['filters'];
        }

        $users = $repository->findUtilisateur($filters,$sort );


        $users = $this->get('knp_paginator')->paginate(
            $users,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        return $this->render('UtilisateurBundle:User:list.html.twig', array(
            'users' => $users,
            'currentFilters'    => $filters,
            'dates'             => $dates,
        ));
    }

    /**
     * @Route("/{id}/setstate", name="utilisateur_setstate", requirements={"id": "\d+"})
     */
    public function setstateAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('UtilisateurBundle:Utilisateur');
        $em = $this->getDoctrine()->getManager();
        try {
            $user = $repository->find($request->get('id'));
            if ($user->isEnabled()) {
                $state = 0;
                $user->setEnabled($state);
            } else {
                $state = 1;
                $user->setEnabled($state);
            }

            $em->persist($user);
            $em->flush();

        } catch (\Exception $o) {
        }



        return $this->redirectToRoute('utilisateur_list');

    }

    /**
     * Delete a user entity.
     *
     * @Route("/delete/{id}", name="user_delete")
     */

    public function deleteAction(Request $request)
    {

        $userId = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UtilisateurBundle:Utilisateur')->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('No user found');
        }


        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('utilisateur_list');
    }

    /**
     * Promote or demote user
     *
     * @Route("/set-role/{id}", name="user_set_role")
     */
    public function setRoleAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $userId = $request->get('id');

        // Use findUserby, findUserByUsername() findUserByEmail() findUserByUsernameOrEmail, findUserByConfirmationToken($token) or findUsers()
        $user = $userManager->findUserBy(['id' => $userId]);
        if($user->hasRole('ROLE_ADMIN')){
            // Add the role that you want !
            $user->addRole("ROLE_USER");
        }else {
            // Add the role that you want !
            $user->addRole("ROLE_ADMIN");
        }


        // Update user roles
        $userManager->updateUser($user);
        return $this->redirectToRoute('utilisateur_list');
    }

}
