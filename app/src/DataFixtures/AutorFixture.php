<?php

namespace App\DataFixtures;

use App\Entity\Autor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AutorFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $autor = new Autor();
        $autor->setEmail("autor@autores.demo");
        $autor->setNombre("Autor");
        $autor->setApellido("Autórez");
        $autor->setGoogleid("20000");
        $manager->persist($autor);
        $manager->flush();
    }
}