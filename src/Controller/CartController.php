<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Reservation;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/panier', name: 'cart')]
    /**
     * Summary of index
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart');
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/panier/ajouter/{idResa}', name: 'add_to_cart')]
    /**
     * Summary of add
     * @param \App\Entity\Reservation $reservation
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function add(string $idResa, Reservation $reservation, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $newReservation = [
            'price' => $reservation->getPeriod() === 'Journée' ? $reservation->getPrice()*2 : $reservation->getPrice(),
            'room' => $reservation->getRoom(),
            'roomName' => $reservation->getRoom()->getName(),
            'date' => $reservation->getDate(),
            'periode' => $reservation->getPeriod(),
        ];
        $cart[$idResa] = $newReservation;

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart');
    }

    #[Route('/panier/supprimer/{id}', name: 'remove')]
     /**
      * Summary of remove
      * @param string $id
      * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
      * @return \Symfony\Component\HttpFoundation\RedirectResponse
      */
     public function remove(string $id, SessionInterface $session) {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * Summary of validate
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/panier/valider', name: 'validate_cart')]
    public function validate(SessionInterface $session, RoomRepository $roomRepository, EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey('STRIPE_SECRET_KEY');
        $cart = $session->get('cart', []);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setTotal(0);

        $lineItems = [];

        foreach ($cart as $reservationData) {
            $reservation = new Reservation();

            $reservation->setPrice($reservationData['price']);
            $reservation->setDate($reservationData['date']);
            $reservation->setPeriod($reservationData['periode']);
            $reservation->setCreatedAt(new \DateTimeImmutable());

            $room = $roomRepository->find($reservationData['room']->getId());
            $reservation->setRoom($room);
            $order->addReservation($reservation);

            $order->setTotal($order->getTotal() + $reservation->getPrice());

            $entityManager->persist($reservation);

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $reservationData['roomName'],
                    ],
                    'unit_amount' => $reservationData['price'] * 100,
                ],
                'quantity' => 1,
            ];
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $session->set('cart', []);

        $session = \Stripe\Checkout\Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/checkout_success',
            'cancel_url' => 'http://127.0.0.1:8000/checkout_error'
        ]);

        return $this->redirect($session->url, 303);
        // return $this->redirectToRoute('app_order', ['id'=>$order->getId()]);
    }

    /**
     * Summary of delete
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route('/panier/vider', name: 'delete_cart')]
     public function delete(SessionInterface $session) {
        $session->set('cart', []);

        return $this->redirectToRoute('cart');
    }
}
