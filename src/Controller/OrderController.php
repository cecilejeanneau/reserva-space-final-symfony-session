<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    #[Route('/order/{id}', name: 'app_order')]
    public function index(Order $order): Response
    {
        //dd($order);
        return $this->render('order/confirmation-commande.html.twig', [
            'order' => $order,
        ]);
    }

    
}
