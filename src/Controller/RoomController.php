<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Form\ReservationType;
use App\Repository\RoomRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace')]
class RoomController extends AbstractController
{
    #[Route('/', name: 'app_room_index', methods: ['GET'])]
    /**
     * Summary of index
     * @param \App\Repository\RoomRepository $roomRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }


    #[Route('/{id}', name: 'app_room_show', methods: ['GET', 'POST'])]
    /**
     * Summary of show
     * @param \App\Entity\Room $room
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Controller\CartController $cart
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Room $room, Request $request, EntityManagerInterface $entityManager, CartController $cartController, SessionInterface $session): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $idResa = uniqid($prefix = "reserva_");
            $reservation->setRoom($room);
            $reservation->setPrice($room->getPrice());

            $cart= $session->get('cart', []);
            $existingReservationDate = false;
            $conflict = false;
            foreach ($cart as $cartReservation) {
                if (($cartReservation['date'] == $reservation->getDate()) && ($cartReservation['roomId'] === $room->getId())) {
                    $existingReservationDate = true;
                    if ($cartReservation['periode'] === 'Journée') {
                        $conflict = true;
                        return $this->render('room/show.html.twig', [
                            'room' => $room,
                            'reservation' => $reservation,
                            'form' => $form->createView(),
                            'conflict' => $conflict
                        ]);
                    } elseif ($cartReservation['periode'] === $reservation->getPeriod()) {
                        $conflict = true;
                        return $this->render('room/show.html.twig', [
                            'room' => $room,
                            'reservation' => $reservation,
                            'form' => $form->createView(),
                            'conflict' => $conflict
                        ]);
                    }
                }
            }
            if (!$existingReservationDate && !$conflict) {
                $cartController->add($idResa, $reservation, $session);
                $entityManager->flush();

                return $this->redirectToRoute('cart', [], Response::HTTP_SEE_OTHER);
            }
            
        }

        return $this->render('room/show.html.twig', [
            'room' => $room,
            'reservation' => $reservation,
            'form' => $form->createView(),
            'conflict' => false,
        ]);
    }
}
