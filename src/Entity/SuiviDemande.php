<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass=SuiviDemandeRepository::class)
*/
class SuiviDemande
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
private $email;

/**
* @ORM\Column(type="string", length=255)
*/
private $token;

/**
* @ORM\Column(type="string", length=255)
*/
private $etat;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return SuiviDemande
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return SuiviDemande
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     * @return SuiviDemande
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param mixed $etat
     * @return SuiviDemande
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
        return $this;
    }
}
