<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Achat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        return $this->render('achat/index.html.twig', array(
            'achats' => $achats,
        ));
    }

    /**
     * Finds and displays a achat entity.
     *
     * @Route("/{id}", name="achat_show")
     * @Method("GET")
     */
    public function showAction(Achat $achat)
    {

        return $this->render('achat/show.html.twig', array(
            'achat' => $achat,
        ));
    }
}
