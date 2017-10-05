<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Achat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Achat controller.
 *
 * @Route("achat")
 */
class AchatController extends Controller
{
    /**
     * Lists all achat entities.
     *
     * @Route("/", name="achat_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $itemsPerPage = $this->container->getParameter('per_page');
        $repository = $em->getRepository('StockBundle:Achat');

        $sort = array($request->query->get('sort', ''), $request->query->get('direction', 'asc'));
        $filters = null;
        $dates = null;

        if (isset($_REQUEST['filters'])) {
            $filters = $_REQUEST['filters'];
        }

        if (isset($_REQUEST['dates'])) {
            $dates = $_REQUEST['dates'];
        }

        $achats = $repository->findAchat($sort, $filters, $dates);


        $achats = $this->get('knp_paginator')->paginate(
            $achats,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );

        return $this->render('StockBundle:Achat:index.html.twig', array(
            'achats' => $achats,
            'currentFilters'    => $filters,
            'dates'             => $dates,
        ));
    }

    /**
     * Finds and displays a achat entity.
     *
     * @Route("/edit/{id}", name="achat_edit")
     */
    public function editAction(Request $request)
    {
        $achatId = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $achat = $em->getRepository('StockBundle:Achat')->find($achatId);

        $articles = $em->getRepository('StockBundle:Article')->findAll();
        if ($request->isMethod('POST'))  {
            $this->updateAchat($achat,$request);
        }
        return $this->render('StockBundle:Achat:edit.html.twig', array(
            'achat' => $achat,
            'articles' =>$articles
        ));
    }
    public function updateAchat($achat,Request $request){
        // update
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;

        try{
            $date  = new \DateTime();
            $achatQuantite = $data->get('_achatquantite');
            $achatPrixUnitaire = $data->get('_achatprixunitaire');
            $achatArticle = $data->get('_achatarticle');
            $article = $em->getRepository('StockBundle:Article')->find($achatArticle);
            $achatDate  = $this->convertStringToDate($data->get('_achatdate'));
            $achat->setCreatedAt($achatDate);
            $achat->setPrixUnitaire($achatPrixUnitaire);
            $achat->setPrixUnitaire($achatPrixUnitaire);
            $achat->setQuantite($achatQuantite);
            $achat->setArticle($article);
            $achat->setModifyOn($date);
            $em->persist($achat);
            $em->flush();
            $flashAlert = 'success';
            $flashMessage = 'Modifier avec succes';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }catch (\Exception $e){
            $flashAlert = 'error';
            $flashMessage = 'Une erreur c\'est produite lors de la modification';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }



    }

    /**
     * Add a achat entity
     * @Route("/add", name="achat_add")
     */
    public function addAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        $articles = $em->getRepository('StockBundle:Article')->findAll();
        if ($request->isMethod('POST'))  {
            $this->saveAchat($request);

        }
        return $this->render('StockBundle:Achat:add.html.twig', array(
            'articles' => $articles
        ));
    }

    public function saveAchat(Request $request){
        // save
        $em = $this->getDoctrine()->getManager();

        try{

            $achat = new Achat();
            $data = $request->request;
            $articleId = $data->get('_achatarticle');
            $article = $em->getRepository('StockBundle:Article')->find($articleId);
            $achatDate = $this->convertStringToDate($data->get('_achatdate'));
            //$achatDate  = new \DateTime();
            $achatQuantite = $data->get('_achatquantite');
            $achatPrixUnitaire = $data->get('_achatprixunitaire');
            $achat->setArticle($article);
            $achat->setQuantite($achatQuantite);
            $achat->setPrixUnitaire($achatPrixUnitaire);
            $achat->setCreatedAt($achatDate);
            $em->persist($achat);
            $em->flush();
            $flashAlert = 'success';
            $flashMessage = 'Enregistrer avec succes';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }catch (\Exception $e){
            $flashAlert = 'error';
            $flashMessage = 'Une erreur c\'est produite lors de l\'enregistrement';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }


    }
    function convertStringToDate($str){
        $myDateTime = DateTime::createFromFormat('d/m/Y', $str);
        $newDateString = $myDateTime->format('Y-m-d H:i:s');
        return $newDateString;

    }

    /**
     * Delete a achat entity.
     *
     * @Route("/delete/{id}", name="achat_delete")
     */

    public function deleteAction(Request $request)
    {

        try{
            $achatId = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $achat = $em->getRepository('StockBundle:Achat')->find($achatId);
            if (!$achat) {
                throw $this->createNotFoundException('No guest found');
            }


            $em->remove($achat);
            $em->flush();

            $flashAlert = 'success';
            $flashMessage = 'Suprimer avec succes';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );

        }catch (\Exception $e){
            $flashAlert = 'error';
            $flashMessage = 'Une erreur c\'est produite lors de la suppression';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }


        return $this->redirectToRoute('achat_index');
    }
}
