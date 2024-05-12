<?php

namespace App\Entity;

use App\Repository\AttenteDesignWebRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttenteDesignWebRepository::class)
 */
class AttenteDesignWeb
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Devis", mappedBy="attentesDesignWeb", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $devis;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     * @return AttenteDesignWeb
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDevis()
    {
        return $this->devis;
    }

    /**
     * @param mixed $devis
     * @return AttenteDesignWeb
     */
    public function setDevis($devis)
    {
        $this->devis = $devis;
        return $this;
    }
}
