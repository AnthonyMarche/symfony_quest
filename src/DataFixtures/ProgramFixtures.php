<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $program = new Program();
        $program->setTitle('Walking dead');
        $program->setSynopsis('Des zombies envahissent la terre');
        $program->setCategory($this->getReference('category_Horreur'));
        $manager->persist($program);

        $program2 = new Program();
        $program2->setTitle('Game of Thrones');
        $program2->setSynopsis('La conquête du Trône de fer');
        $program2->setCategory($this->getReference('category_Fantastique'));
        $manager->persist($program2);

        $program3 = new Program();
        $program3->setTitle('One Piece');
        $program3->setSynopsis('Luffy veut devenir le ri des pirates');
        $program3->setCategory($this->getReference('category_Animation'));
        $manager->persist($program3);

        $program4 = new Program();
        $program4->setTitle('Vikings');
        $program4->setSynopsis('Les ambitions de Ragnar Lothbrok ');
        $program4->setCategory($this->getReference('category_Aventure'));
        $manager->persist($program4);

        $program5 = new Program();
        $program5->setTitle('Prison Break');
        $program5->setSynopsis("L'évasion d'une prison");
        $program5->setCategory($this->getReference('category_Action'));
        $manager->persist($program5);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
        ];
    }
}