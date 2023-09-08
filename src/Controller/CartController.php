<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        // Récupérez les réservations depuis la session
        $cart = $session->get('cart', []);

        // Créez une nouvelle commande
        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setTotal(0); // Vous devrez calculer le total en fonction des réservations

        //$reservations = [];


        // Bouclez sur les réservations du panier
        foreach ($cart as $reservationData) {
            $reservation = new Reservation();

            // La salle ! identifiant de la salle
            // new Salle avec l'id puis ajout à la réservation
            // on  ajoute les propriété de la résa
            $reservation->setPrice($reservationData['price']);
            // $reservation->setRoom($reservationData['room']);
            $reservation->setDate($reservationData['date']);
            $reservation->setPeriod($reservationData['periode']);
            $reservation->setCreatedAt(new \DateTimeImmutable());

            // Ajout de la room
            $room = $roomRepository->find($reservationData['room']->getId());
            $reservation->setRoom($room);
            //dd($reservationData['room']);
            //$reservation->setRoom()
            // ... Set other reservation properties

            // $reservationData['room']->addReservation($reservation);
            // $reservations[] = $reservation;

            // Associez la réservation à la commande
            $order->addReservation($reservation);

            // Mettez à jour le total de la commande
            $order->setTotal($order->getTotal() + $reservation->getPrice());

            // Enregistrez la réservation dans la base de données
            $entityManager->persist($reservation);
        }

        // Enregistrez la commande dans la base de données
        $entityManager->persist($order);
        $entityManager->flush();

        // Videz le panier
        $session->set('cart', []);

       /*  $roomRepository = $entityManager->getRepository(Room::class);
        foreach ($reservations as $reservation) {
            $room = $roomRepository->find($reservation->getRoomId());
            $roomName = $room->getName();
            if ($room) {
                // Mettez à jour la réservation avec le nom de la salle
                $reservation['roomName'] = $roomName;
            }
        } */

        // Redirigez vers une page de confirmation ou une autre page appropriée

        return $this->redirectToRoute('app_order', ['id'=>$order->getId()]);
       /*  return $this->render('cart/confirmation-commande.html.twig', [
            'order' => $order
            //'reservations' => $reservations
        ]); */
    }

    // #[Route('/panier/confirmation-commande', name: 'order_confirmation')]
    // public function orderConfirmation(SessionInterface $session): Response
    // {
    //     // 1. Récupérez les détails de la commande depuis la session
    //     $cart = $session->get('cart', []);

    //     // 2. Initialisez des variables pour stocker les détails de la commande et le montant total
    //     $orderDetails = [];
    //     $totalAmount = 0.0;

    //     // 3. Parcourez les éléments du panier pour construire les détails de la commande
    //     foreach ($cart as $item) {
    //         // Calculez le montant total pour cet élément
    //         $itemTotal = $item['price'];
            
    //         // Ajoutez cet élément aux détails de la commande
    //         $orderDetails[] = [
    //             'price' => $item['price'],
    //             'roomId' => $item['roomId'],
    //             'date' => $item['date']->format('Y-m-d'),
    //             'periode' => $item['periode'],
    //             'total' => $itemTotal,
    //         ];

    //         // Ajoutez le montant total de cet élément au montant total de la commande
    //         $totalAmount += $itemTotal;
    //     }

    //     // 4. Envoyez les détails de la commande et le montant total au modèle Twig
    //     return $this->render('cart/confirmation-commande.html.twig', [
    //         'orderDetails' => $orderDetails,
    //         'totalAmount' => $totalAmount,
    //     ]);
    // }

    // #[Route('/confirmation-commande', name: 'order_confirmation')]
    // public function orderConfirmation(): Response
    // {
    //     // Vous pouvez ici obtenir les détails de la commande et des réservations
    //     // à partir de la session ou de votre base de données
    //     $orderDetails = /* Récupérez les détails de la commande ici */;

    //     return $this->render('cart/order_confirmation.html.twig', [
    //         'orderDetails' => $orderDetails,
    //     ]);
    // }

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
