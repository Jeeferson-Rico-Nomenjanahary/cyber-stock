<?php

namespace StockBundle\Controller;

use StockBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


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
        if(isset($_REQUEST['export']) && $_REQUEST['export']=="doExport"){
            // ask the service for a Excel5
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();


            $phpExcelObject->getProperties()->setCreator("E3 Services Informatique")
                ->setLastModifiedBy("E3 Services Informatique")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Liste des Achats.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A12', 'Nom')
                ->setCellValue('B12', 'Description');
            $excelSheet =$phpExcelObject->getActiveSheet();
            $default_border = array(
                'style' => \PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb'=>'E7FEFF')
            );
            $style_header = array(
                'borders' => array(
                    'bottom' => $default_border,
                    'left' => $default_border,
                    'top' => $default_border,
                    'right' => $default_border,
                ),
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb'=>'545e7f'),
                ),
                'font' => array(
                    'bold' => true,
                    'color'=>array('rgb'=>'FFFFFF'),
                    'alignment'=>array('horizontal'=>\PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                    'name'=>'Arial',
                )
            );

            $excelSheet->getStyle('A12:B12')->applyFromArray( $style_header );

            //$phpExcelObject->getActiveSheet()->getStyle('A12')->getFill()->getStartColor()->setRGB('2A52BE');

            $drawingobject = $this->get('phpexcel')->createPHPExcelWorksheetDrawing();
            $drawingobject->setName('Image name');
            $drawingobject->setDescription('Image description');
            $drawingobject->setPath('img/logo.jpg');
            $drawingobject->setHeight(200);
            $drawingobject->setOffsetY(245);
            $drawingobject->setCoordinates('A1');
            $drawingobject->setWorksheet($excelSheet);

            $ligne = 12 ;

            foreach ($articles as $article) {
                $ligne++;
                $nomCell = strval('A'.$ligne);
                $descriptionCell = strval('B'.$ligne);
                $excelSheet
                    ->setCellValue($nomCell, $article->getName())
                    ->setCellValue($descriptionCell, $article->getDescription());

            }


            $phpExcelObject->getActiveSheet()->setTitle('E3 Services Informatique');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);

            // create the writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            // create the response
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // adding headers
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'articles-list.xls'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }

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
        try{
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
            $flashAlert = 'success';
            $flashMessage = 'Enregistrer avec succes';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        } catch (\Exception $e){
            $flashAlert = 'error';
            $flashMessage = 'Une erreur c\'est produite lors de l\'enregistrement';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }


    }

    public function updateArticle($article,Request $request){
        // update
        try {
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
     * Delete a article entity.
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/delete/{id}", name="article_delete")
     */

    public function deleteAction(Request $request)
    {

        try{
            $articleId = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $article = $em->getRepository('StockBundle:Article')->find($articleId);
            if (!$article) {
                throw $this->createNotFoundException('No guest found');
            }


            $em->remove($article);
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


        return $this->redirectToRoute('article_index');
    }
}
