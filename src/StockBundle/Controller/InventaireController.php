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
        if(isset($_REQUEST['export']) && $_REQUEST['export']=="doExport"){
            // ask the service for a Excel5
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();


            $phpExcelObject->getProperties()->setCreator("E3 Services Informatique")
                ->setLastModifiedBy("E3 Services Informatique")
                ->setTitle("Office 2005 XLSX Test Document")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Liste des Inventaires.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("Test result file");
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A12', 'Action')
                ->setCellValue('B12', 'Date')
                ->setCellValue('C12', 'Article')
                ->setCellValue('D12', 'Description')
                ->setCellValue('E12', 'Quantité')
                ->setCellValue('F12', 'Prix Unitaire')
                ->setCellValue('G12', 'Prix d\'achat')
                ->setCellValue('H12', 'Prix de vente');
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

            $excelSheet->getStyle('A12:H12')->applyFromArray( $style_header );

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

            foreach ($inventaires as $inventaire) {
                $ligne++;
                $actionCell = strval('A'.$ligne);
                $dateCell = strval('B'.$ligne);
                $articleCell = strval('C'.$ligne);
                $descriptionCell = strval('D'.$ligne);
                $quantiteCell = strval('E'.$ligne);
                $prixUnitaireCell = strval('F'.$ligne);
                $prixAchatCell = strval('G'.$ligne);
                $prixVenteCell = strval('H'.$ligne);
                if ($inventaire['type'] == 'achat') {
                    $prix_vente = '' ;
                    $prix_achat = $inventaire['quantite'] * $inventaire['prix_unitaire'] ;
                }
                if ($inventaire['type'] == 'vente') {
                    $prix_achat = '' ;
                    $prix_vente = $inventaire['quantite'] * $inventaire['prix_unitaire'] ;
                }


                $excelSheet
                    ->setCellValue($actionCell, $inventaire['type'])
                    ->setCellValue($dateCell, $inventaire['date'])
                    ->setCellValue($articleCell, $inventaire['article'])
                    ->setCellValue($descriptionCell, $inventaire['description'])
                    ->setCellValue($quantiteCell, $inventaire['quantite'])
                    ->setCellValue($prixUnitaireCell, $inventaire['prix_unitaire'])
                    ->setCellValue($prixAchatCell, $prix_achat)
                    ->setCellValue($prixVenteCell, $prix_vente);

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
                'inventaire-list.xls'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }


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
