<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class InventaireController extends Controller
{
    /**
     * @Route("/inventaire")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $itemsPerPage = $this->container->getParameter('per_page');
        $repository = $em->getRepository('StockBundle:Article');

        $sort = array($request->query->get('sort', ''), $request->query->get('direction', 'asc'));
        $filters = null;
        $dates = null;

        if (isset($_REQUEST['filters'])) {
            $filters = $_REQUEST['filters'];
        }

        if (isset($_REQUEST['dates'])) {
            $dates = $_REQUEST['dates'];
        }

        //$inventaires = $repository->findInventaire($sort, $filters, $dates);
        $entityManager = $this->get('doctrine.dbal.default_connection');
        $inventaires = $repository->findInventaire($sort,$filters,$entityManager);


        $inventaires = $this->get('knp_paginator')->paginate(
            $inventaires,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        return $this->render('StockBundle:Inventaire:index.html.twig', array(
            'inventaires' => $inventaires,
            'currentFilters'    => $filters,
        ));
    }

}
