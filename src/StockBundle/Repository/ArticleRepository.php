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
        } else {
            $dql .= ' ORDER BY art.name ASC ';
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
        }else{
            $dql .= ' ORDER BY art.name ASC ';
        }
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }


    public function findInventaire($sort, $filters,$entityManager) {

        $clauseWhere = '';
        $typesql = 'union';
        if($filters != null) {

            foreach ($filters as $key => $value) {
                $value = str_replace(['\''], ['\'\''],$value);
                $firtsClause = false;
                if ($key == 'name') {
                    if (trim($value) != "") {
                        $clauseWhere .= ' WHERE ';
                        $clauseWhere .= " article." . $key . " LIKE '%" . $value . "%'";
                    }
                }
                if ($key == 'type'){
                    if (trim($value) != "") {
                        if($value == 'achat'){
                            $typesql = 'achat';

                        } elseif ($value == 'vente'){
                            $typesql = 'vente';
                        }

                    } else {
                        $typesql = 'union';
                    }

                }
            }
        }

        if ($typesql == 'achat'){
            $sql = "SELECT achat.id as id ,achat.quantite as quantite , achat.prix_unitaire as prix_unitaire ,article.name as article ,article.description as description , 'achat' as type ,achat.created_at as date 
                FROM cyber_achat achat JOIN cyber_article article ON achat.article_id = article.id ".$clauseWhere;

        } elseif ($typesql == 'vente'){
            $sql = "SELECT vente.id as id ,vente.quantite as quantite , vente.prix_unitaire as prix_unitaire ,article.name as article ,article.description as description , 'vente' as type ,vente.created_at as date 
                FROM cyber_vente vente JOIN cyber_article article ON vente.article_id = article.id " .$clauseWhere ;

        }elseif ($typesql == 'union'){
            $sql = "SELECT achat.id as id ,achat.quantite as quantite , achat.prix_unitaire as prix_unitaire ,article.name as article ,article.description as description , 'achat' as type ,achat.created_at as date 
                FROM cyber_achat achat JOIN cyber_article article ON achat.article_id = article.id  ".$clauseWhere. " 
                UNION 
                SELECT vente.id as id ,vente.quantite as quantite , vente.prix_unitaire as prix_unitaire ,article.name as article ,article.description as description , 'vente' as type ,vente.created_at as date 
                FROM cyber_vente vente JOIN cyber_article article ON vente.article_id = article.id " .$clauseWhere;

        }
        if ($sort[0] != '' && $sort[1] !=''){
            $sql .= ' ORDER BY '.$sort[0].' '.$sort[1];
        }




        $reportings = $entityManager->fetchAll($sql);

        return $reportings;


    }
}
