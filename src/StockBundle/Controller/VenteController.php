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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


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
        if(isset($_REQUEST['export']) && $_REQUEST['export']=="doExport"){
            // ask the service for a Excel5
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();


            $phpExcelObject->getProperties()->setCreator("E3 Services Informatique")
                ->setLastModifiedBy("E3 Services Informatique")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Liste des Ventes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A12', 'Date')
                ->setCellValue('B12', 'Article')
                ->setCellValue('C12', 'Description')
                ->setCellValue('D12', 'Quantité')
                ->setCellValue('E12', 'Prix de vente')
                ->setCellValue('F12', 'Prix de vente total');
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

            foreach ($ventes as $vente) {
                $ligne++;
                $dateCell = strval('A'.$ligne);
                $articleCell = strval('B'.$ligne);
                $descriptionCell = strval('C'.$ligne);
                $quantiteCell = strval('D'.$ligne);
                $prixachatCell = strval('E'.$ligne);
                $totalCell = strval('F'.$ligne);
                //$article = $ventes["article"];
                $excelSheet
                    ->setCellValue($dateCell, $vente->getcreatedAt()->format('d/m/Y'))
                    ->setCellValue($articleCell, $vente->getArticle()->getName())
                    ->setCellValue($descriptionCell, $vente->getArticle()->getDescription())
                    ->setCellValue($quantiteCell, $vente->getQuantite())
                    ->setCellValue($prixachatCell, $vente->getPrixUnitaire())
                    ->setCellValue($totalCell, $vente->getQuantite() * $vente->getPrixUnitaire());

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
                'vente-list.xls'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }


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
        try {
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
            $flashAlert = 'success';
            $flashMessage = 'Modifier avec succes';

            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        } catch (\Exception $e){
            $flashAlert = 'error';
            $flashMessage = 'Une erreur c\'est produite lors de la modification';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }


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
        try {
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
     * Delete a vente entity.
     *
     * @Route("/delete/{id}", name="vente_delete")
     */

    public function deleteAction(Request $request)
    {

        try {
            $venteId = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $vente = $em->getRepository('StockBundle:Vente')->find($venteId);
            if (!$vente) {
                throw $this->createNotFoundException('No guest found');
            }


            $em->remove($vente);
            $em->flush();
            $flashAlert = 'success';
            $flashMessage = 'Suprimer avec succes';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );

        } catch (\Exception $e){
            $flashAlert = 'error';
            $flashMessage = 'Une erreur c\'est produite lors de la suppression';
            $this->addFlash(
                $flashAlert,
                $flashMessage
            );
        }


        return $this->redirectToRoute('vente_index');
    }
}
