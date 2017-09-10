<?php

namespace StockBundle\Repository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\EntityRepository
{
    public function findArticle( $sort, $filters = null) {
        $dql = '
                SELECT art FROM StockBundle:Article art 
            
             ';
        $dqlWhere = '';
        $dqlFilters = "";

        if($filters != null) {

            foreach ($filters as $key => $value) {
                $value = str_replace(['\''], ['\'\''],$value);
                if ($key == 'name') {
                    if (trim($value) != "") {
                        $dqlWhere = 'WHERE ';
                        //$dql .= '';
                        $dqlFilters .= " art." . $key . " LIKE '%" . $value . "%'";
                    }
                }
            }
        }
        $dql = $dql . " " . $dqlWhere . " " . $dqlFilters;


        if ($sort[0] != '' && $sort[1] !=''){
            $dql .= ' ORDER BY art.'.$sort[0].' '.$sort[1];
        }

        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    public function findStock( $sort, $filters = null, $dates = null) {
        $dql = '
                SELECT art FROM StockBundle:Article art 
            
             ';
        $dqlWhere = '';
        $dqlFilters = "";

        if($filters != null) {

            foreach ($filters as $key => $value) {
                $value = str_replace(['\''], ['\'\''],$value);
                if ($key == 'name') {
                    if (trim($value) != "") {
                        $dqlWhere = 'WHERE ';
                        //$dql .= '';
                        $dqlFilters .= " art." . $key . " LIKE '%" . $value . "%'";
                    }
                }
            }
        }
        $dql = $dql . " " . $dqlWhere . " " . $dqlFilters;
        if ($sort[0] != '' && $sort[1] !=''){
            $dql .= ' ORDER BY art.'.$sort[0].' '.$sort[1];
        }
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }
}
