<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class StockController extends Controller
{
    /**
     * @Route("/")
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

        $stocks = $repository->findStock($sort, $filters, $dates);


        /*$stocks = $this->get('knp_paginator')->paginate(
            $stocks,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );*/

        return $this->render('StockBundle:Stock:index.html.twig', array(
            'stocks' => $stocks,
            'currentFilters'    => $filters,
            'dates'             => $dates,
        ));
    }
}
