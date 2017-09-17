<?php

namespace UtilisateurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\FOSUserEvents;
use UtilisateurBundle\Entity\Utilisateur;
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

}
