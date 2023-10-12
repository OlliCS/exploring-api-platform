<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Room;
use App\Entity\Booking;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $json = file_get_contents(__DIR__ . '/rooms.json');
        $data = json_decode($json, true);

        foreach($data as $roomData) {
            $room = new Room();
            $room->setName($roomData['name']);
            $room->setCapacity($roomData['capacity']);
            $manager->persist($room);
        }

        $manager->flush();

        $json = file_get_contents(__DIR__ . '/bookings.json');
        $data = json_decode($json, true);

        foreach($data as $bookingData){
            $booking = new Booking();
            $room = $manager->getRepository(Room::class)->find($bookingData['room_id']);

            if($room === null){
                continue;
            }

            $booking->setRoom($room);
            $booking->setStartDate(new DateTime($bookingData['start_date']));
            $booking->setEndDate(new DateTime($bookingData['end_date']));

            $manager->persist($booking);
        }

        $manager->flush();
 
    }
}
