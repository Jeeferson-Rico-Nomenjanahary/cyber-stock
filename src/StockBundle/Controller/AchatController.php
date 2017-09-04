<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Achat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $achats = $em->getRepository('StockBundle:Achat')->findAll();

        return $this->render('StockBundle:Achat:index.html.twig', array(
            'achats' => $achats,
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
        $achat = $em->getRepository('StockBundle:Article')->find($achatId);
        if ($request->isMethod('POST'))  {
            $this->updateAchat($achat,$request);
        }
        return $this->render('StockBundle:Achat:edit.html.twig', array(
            'achat' => $achat,
        ));
    }
    public function updateAchat($achat,Request $request){

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

        $achat = new Achat();
        $data = $request->request;
        $articleId = $data->get('_achatarticle');
        $article = $em->getRepository('StockBundle:Article')->find($articleId);
        $achatDate = $data->get('_achatdate');
        /**
         * @Todo format date for insert in database
         */

        $achatDate  = new \DateTime();
        $achatQuantite = $data->get('_achatquantite');
        $achatPrixUnitaire = $data->get('_achatprixunitaire');
        $achat->setArticle($article);
        $achat->setQuantite($achatQuantite);
        $achat->setPrixUnitaire($achatPrixUnitaire);
        $achat->setCreatedAt($achatDate);
        $em->persist($achat);
        $em->flush();

    }
}
