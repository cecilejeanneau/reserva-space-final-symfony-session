<?php

namespace App\Controller;

use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/panier', name: 'cart')]
    public function index(SessionInterface $session): Response
    {
        dd($session->get("cart"));
    }

    #[Route('/panier/ajouter/{id}', name: 'add_to_cart')]
    public function add(Reservation $reservation, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $cart[] = $reservation->getId();
        $session->set('cart', $cart);

        return $this->redirectToRoute('cart');
    }
}
