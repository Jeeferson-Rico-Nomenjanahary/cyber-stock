<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Article controller.
 *
 * @Route("article")
 */
class ArticleController extends Controller
{
    /**
     * Lists all article entities.
     *
     * @Route("/", name="article_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $itemsPerPage = $this->container->getParameter('per_page');
        $repository = $em->getRepository('StockBundle:Article');

        $sort = array($request->query->get('sort', ''), $request->query->get('direction', 'asc'));
        $filters = null;
        if (isset($_REQUEST['filters'])) {
            $filters = $_REQUEST['filters'];
        }

        $articles = $repository->findArticle($sort, $filters);

        $articles = $this->get('knp_paginator')->paginate(
            $articles,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );

        return $this->render('StockBundle:Article:index.html.twig', array(
            'articles' => $articles,
            'currentFilters'    => $filters,
        ));
    }

    /**
     * Finds and displays a article entity.
     *
     * @Route("/edit/{id}", name="article_edit")
     */
    public function editAction(Request $request)
    {

        $articleId = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('StockBundle:Article')->find($articleId);
        if ($request->isMethod('POST'))  {
            $this->updateArticle($article,$request);
        }
        return $this->render('StockBundle:Article:edit.html.twig', array(
            'article' => $article
        ));
    }


    /**
     * Add a article entity
     * @Route("/add", name="article_add")
     */
    public function addAction(Request $request){

        if ($request->isMethod('POST'))  {
            $this->saveArticle($request);

        }
        return $this->render('StockBundle:Article:add.html.twig', array(
        ));
    }

    public function saveArticle(Request $request){
        // save
        $em = $this->getDoctrine()->getManager();
        $article = new Article();
        $data = $request->request;
        $artcleName = trim($data->get('_artclename'));
        $artcleDescription = trim($data->get('_articledescription'));
        $date  = new \DateTime();
        $article->setName($artcleName);
        $article->setDescription($artcleDescription);
        $article->setCreatedAt($date);
        $em->persist($article);
        $em->flush();

    }

    public function updateArticle($article,Request $request){
        // update
        $em = $this->getDoctrine()->getManager();
        $data = $request->request;
        $artcleName = trim($data->get('_artclename'));
        $artcleDescription = trim($data->get('_articledescription'));
        $date  = new \DateTime();
        $article->setName($artcleName);
        $article->setDescription($artcleDescription);
        $article->setModifyOn($date);
        $em->persist($article);
        $em->flush();

    }

    /**
     * Delete a article entity.
     *
     * @Route("/delete/{id}", name="article_delete")
     */

    public function deleteAction(Request $request)
    {

        $articleId = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('StockBundle:Article')->find($articleId);
        if (!$article) {
            throw $this->createNotFoundException('No guest found');
        }


        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute('article_index');
    }
}
