<?php

namespace StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Achat
 *
 * @ORM\Table(name="cyber_achat")
 * @ORM\Entity(repositoryClass="StockBundle\Repository\AchatRepository")
 */
class Achat
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \Article
     *
     * @ORM\ManyToOne(targetEntity="StockBundle\Entity\Article", inversedBy="achats")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="article_id", referencedColumnName="id",unique=false,onDelete="cascade")
     * })
     */
    private $article;

    /**
     * @var int
     *
     * @ORM\Column(name="quantite", type="integer", options={"default" = 0})
     */
    private $quantite;

    /**
     * @var float
     *
     * @ORM\Column(name="prix_unitaire", type="float", options={"default" = 0})
     */
    private $prixUnitaire;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modify_on", type="datetime", nullable=true)
     */
    private $modifyOn;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set quantite
     *
     * @param integer $quantite
     *
     * @return Achat
     */
    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * Get quantite
     *
     * @return integer
     */
    public function getQuantite()
    {
        return $this->quantite;
    }

    /**
     * Set prixUnitaire
     *
     * @param float $prixUnitaire
     *
     * @return Achat
     */
    public function setPrixUnitaire($prixUnitaire)
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    /**
     * Get prixUnitaire
     *
     * @return float
     */
    public function getPrixUnitaire()
    {
        return $this->prixUnitaire;
    }

    /**
     * Set article
     *
     * @param \StockBundle\Entity\Article $article
     *
     * @return Achat
     */
    public function setArticle(\StockBundle\Entity\Article $article = null)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \StockBundle\Entity\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Achat
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = new \DateTime($createdAt);

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifyOn
     *
     * @param \DateTime $modifyOn
     *
     * @return Achat
     */
    public function setModifyOn($modifyOn)
    {
        $this->modifyOn = $modifyOn;

        return $this;
    }

    /**
     * Get modifyOn
     *
     * @return \DateTime
     */
    public function getModifyOn()
    {
        return $this->modifyOn;
    }
}
