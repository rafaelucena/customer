<?php

namespace App\DataFixtures;

use App\Entity\Hotel;
use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $names = [
            'Monte Casino',
            'Casanova',
            'Corner',
            'White House',
            'La Casa Rosada',
            'Imperial',
            'Beira mar',
            'Dubai',
            'Grand Budapest',
            'Pool',
        ];

        for ($i = 0; $i < 10; $i++) {
            $id = random_int(0, count($names) - 1);
            $hotel = new Hotel();
            $hotel->setName($names[$id]);
            $manager->persist($hotel);
        }

        for ($j = 0; $j < 100000; $j++) {
            $id = random_int(1, 10);
            $timePast = random_int(1, 730);
            $date = new \DateTime();
            $date->modify("-$timePast days");
            
            $review = new Review();
            $review->setHotelId($id);
            $review->setScore(random_int(1, 10));
            $review->setComment('etc');
            $review->setCreatedDate($date);
            $manager->persist($review);

            if ($j % 10000 === 0) {
                $manager->flush();
            }
        }

        $manager->flush();
    }
}
