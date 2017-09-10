<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Vente;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Vente controller.
 *
 * @Route("vente")
 */
class VenteController extends Controller
{
    /**
     * Lists all vente entities.
     *
     * @Route("/", name="vente_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $itemsPerPage = $this->container->getParameter('per_page');
        $repository = $em->getRepository('StockBundle:Vente');

        $sort = array($request->query->get('sort', ''), $request->query->get('direction', 'asc'));
        $filters = null;
        $dates = null;

        if (isset($_REQUEST['filters'])) {
            $filters = $_REQUEST['filters'];
        }

        if (isset($_REQUEST['dates'])) {
            $dates = $_REQUEST['dates'];
        }

        $ventes = $repository->findVente($sort, $filters, $dates);


        $ventes = $this->get('knp_paginator')->paginate(
            $ventes,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );

        return $this->render('StockBundle:Vente:index.html.twig', array(
            'ventes' => $ventes,
            'currentFilters'    => $filters,
            'dates'             => $dates,
        ));
    }

    /**
     * Finds and displays a vente entity.
     *
     * @Route("/edit/{id}", name="vente_edit")
     */
    public function editAction(Request $request)
    {
        $venteId = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $vente = $em->getRepository('StockBundle:Vente')->find($venteId);

        $articles = $em->getRepository('StockBundle:Article')->findAll();
        if ($request->isMethod('POST'))  {
            $this->updateAchat($vente,$request);
        }
        return $this->render('StockBundle:Vente:edit.html.twig', array(
            'vente' => $vente,
            'articles' =>$articles
        ));
    }
    public function updateAchat($vente,Request $request){
        // update
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;

        $date  = new \DateTime();
        $venteQuantite = $data->get('_ventequantite');
        $ventePrixUnitaire = $data->get('_venteprixunitaire');
        $venteArticle = $data->get('_ventearticle');
        $article = $em->getRepository('StockBundle:Article')->find($venteArticle);
        $venteDate  = $this->convertStringToDate($data->get('_ventedate'));
        $vente->setCreatedAt($venteDate);
        $vente->setPrixUnitaire($ventePrixUnitaire);
        $vente->setPrixUnitaire($ventePrixUnitaire);
        $vente->setQuantite($venteQuantite);
        $vente->setArticle($article);
        $vente->setModifyOn($date);
        $em->persist($vente);
        $em->flush();

    }

    /**
     * Add a vente entity
     * @Route("/add", name="vente_add")
     */
    public function addAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('StockBundle:Article')->findAll();
        if ($request->isMethod('POST'))  {
            $this->saveVente($request);

        }
        return $this->render('StockBundle:Vente:add.html.twig', array(
            'articles' => $articles
        ));
    }

    public function saveVente(Request $request){
        // save
        $em = $this->getDoctrine()->getManager();

        $vente = new Vente();
        $data = $request->request;
        $articleId = $data->get('_ventearticle');
        $article = $em->getRepository('StockBundle:Article')->find($articleId);
        $venteDate = $this->convertStringToDate($data->get('_ventedate'));
        $venteQuantite = $data->get('_ventequantite');
        $ventePrixUnitaire = $data->get('_venteprixunitaire');
        $vente->setArticle($article);
        $vente->setQuantite($venteQuantite);
        $vente->setPrixUnitaire($ventePrixUnitaire);
        $vente->setCreatedAt($venteDate);
        $em->persist($vente);
        $em->flush();

    }
    function convertStringToDate($str){
        $myDateTime = DateTime::createFromFormat('d/m/Y', $str);
        $newDateString = $myDateTime->format('Y-m-d H:i:s');
        return $newDateString;

    }
}
