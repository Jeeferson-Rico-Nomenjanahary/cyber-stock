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
                ->setCellValue('A12', 'Nom')
                ->setCellValue('B12', 'Description')
                ->setCellValue('C12', 'Quantité')
                ->setCellValue('D12', 'Prix d\'achat')
                ->setCellValue('E12', 'Prix de vente')
                ->setCellValue('F12', 'Commentaire');
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

            foreach ($stocks as $stock) {
                $ligne++;
                $nameCell = strval('A'.$ligne);
                $descriptionCell = strval('B'.$ligne);
                $quantiteCell = strval('C'.$ligne);
                $prixAchatCell = strval('D'.$ligne);
                $prixVenteCell = strval('E'.$ligne);
                $commentaireCell = strval('F'.$ligne);
                //$article = $stocks["article"];
                $stock_quantite = 0 ;
                $total_quantite_achat = 0 ;
                $total_quantite_vente = 0 ;
                $total_prix_achat = 0 ;
                $total_prix_vente = 0 ;
                $commentaire = '' ;
                $rupture = 1;
                foreach ($stock->getAchats() as $achat ){

                    $total_quantite_achat = $total_quantite_achat + $achat->getQuantite() ;
                    $total_prix_achat = $total_prix_achat + ($achat->getQuantite() * $achat->getPrixUnitaire()) ;
                }

                foreach ($stock->getVentes() as $vente ){

                    $total_quantite_vente = $total_quantite_vente + $vente->getQuantite() ;
                    $total_prix_vente = $total_prix_vente + ($vente->getQuantite() * $vente->getPrixUnitaire()) ;
                }

                $stock_quantite = $total_quantite_achat -  $total_quantite_vente ;
                if ($stock_quantite <= $rupture ){
                    $commentaire = 'Rupture de stock' ;
                }

                $excelSheet
                    ->setCellValue($nameCell, $stock->getName())
                    ->setCellValue($descriptionCell, $stock->getDescription())
                    ->setCellValue($quantiteCell, $stock_quantite)
                    ->setCellValue($prixAchatCell, $total_prix_achat)
                    ->setCellValue($prixVenteCell, $total_prix_vente)
                    ->setCellValue($commentaireCell, $commentaire);

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
                'stock-list.xls'
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }


        $stocks = $this->get('knp_paginator')->paginate(
            $stocks,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );

        return $this->render('StockBundle:Stock:index.html.twig', array(
            'stocks' => $stocks,
            'currentFilters'    => $filters,
            'rupture'    => 1,
            'dates'             => $dates,
        ));
    }
}
