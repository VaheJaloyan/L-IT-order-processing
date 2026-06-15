<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const REF_JOHN = 'user.john';
    public const REF_JANE = 'user.jane';

    public function load(ObjectManager $manager): void
    {
        // Existing customer from the PDF example
        $john = new User();
        $john->setName('John Doe');
        $john->setEmail('john@example.com');
        $manager->persist($john);

        // Second customer for variety
        $jane = new User();
        $jane->setName('Jane Smith');
        $jane->setEmail('jane@example.com');
        $manager->persist($jane);

        $manager->flush();

        $this->addReference(self::REF_JOHN, $john);
        $this->addReference(self::REF_JANE, $jane);
    }
}
