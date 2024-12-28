<?php

namespace App\Controller\Back;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactShowController extends AbstractController
{
    #[Route('/back/contact/show', name: 'back_contact_show')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $contactInfo = $entityManager->getRepository(Contact::class)->findAll();

        return $this->render('back/contact_show/index.html.twig',
        [
            'contactInfo' => $contactInfo,
        ]);
    }
}
