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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


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
                ->setCellValue('A12', 'Date')
                ->setCellValue('B12', 'Article')
                ->setCellValue('C12', 'Description')
                ->setCellValue('D12', 'Quantité')
                ->setCellValue('E12', 'Prix d\'achat')
                ->setCellValue('F12', 'Prix d\'achat total');
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

            $excelSheet->getStyle('A12:F12')->applyFromArray( $style_header );

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

            foreach ($achats as $achat) {
                $ligne++;
                $dateCell = strval('A'.$ligne);
                $articleCell = strval('B'.$ligne);
                $descriptionCell = strval('C'.$ligne);
                $quantiteCell = strval('D'.$ligne);
                $prixachatCell = strval('E'.$ligne);
                $totalCell = strval('F'.$ligne);
                //$article = $achats["article"];
                $excelSheet
                    ->setCellValue($dateCell, $achat->getcreatedAt()->format('d/m/Y'))
                    ->setCellValue($articleCell, $achat->getArticle()->getName())
                    ->setCellValue($descriptionCell, $achat->getArticle()->getDescription())
                    ->setCellValue($quantiteCell, $achat->getQuantite())
                    ->setCellValue($prixachatCell, $achat->getPrixUnitaire())
                    ->setCellValue($totalCell, $achat->getQuantite() * $achat->getPrixUnitaire());

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
                'achat-list.xls'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }


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
