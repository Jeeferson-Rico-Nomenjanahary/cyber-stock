<?php

namespace StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Article
 *
 * @ORM\Table(name="cyber_article")
 * @ORM\Entity(repositoryClass="StockBundle\Repository\ArticleRepository")
 */
class Article
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;
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
     * @var \Achat
     *
     * @ORM\OneToMany(targetEntity="StockBundle\Entity\Achat", mappedBy="article")
     */
    private $achats;

    /**
     * @var \Vente
     *
     * @ORM\OneToMany(targetEntity="StockBundle\Entity\Vente", mappedBy="article")
     */
    private $ventes;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Article
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Article
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Article
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

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
     * @return Article
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

    /**
     * Add achat
     *
     * @param \StockBundle\Entity\Achat $achat
     *
     * @return Article
     */
    public function addAchat(\StockBundle\Entity\Achat $achat)
    {
        $this->achats[] = $achat;

        return $this;
    }

    /**
     * Remove achat
     *
     * @param \StockBundle\Entity\Achat $achat
     */
    public function removeAchat(\StockBundle\Entity\Achat $achat)
    {
        $this->achats->removeElement($achat);
    }

    /**
     * Get achats
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAchats()
    {
        return $this->achats;
    }

    /**
     * Add vente
     *
     * @param \StockBundle\Entity\Vente $vente
     *
     * @return Article
     */
    public function addVente(\StockBundle\Entity\Vente $vente)
    {
        $this->ventes[] = $vente;

        return $this;
    }

    /**
     * Remove vente
     *
     * @param \StockBundle\Entity\Vente $vente
     */
    public function removeVente(\StockBundle\Entity\Vente $vente)
    {
        $this->ventes->removeElement($vente);
    }

    /**
     * Get ventes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVentes()
    {
        return $this->ventes;
    }
}
