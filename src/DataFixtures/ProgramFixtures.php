<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public const TITLE = [
        'Walking dead',
        'Game of Thrones',
        'One Piece',
        'Vikings',
        'Prison Break',
    ];

    public const SYNOPSIS = [
        'Des zombies envahissent la terre',
        'La conquête du Trône de fer',
        'Luffy veut devenir le roi des pirates',
        'Les ambitions de Ragnar Lothbrok',
        "L'évasion d'une prison",
    ];

    public const CATEGORIES = [
        'category_Horreur',
        'category_Fantastique',
        'category_Animation',
        'category_Aventure',
        'category_Action',
    ];

    public const OWNER = [
        'contributor',
        'admin'
    ];

    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i <5; $i++) {
            $program = new Program();
            $program->setTitle(self::TITLE[$i]);
            $program->setSynopsis(self::SYNOPSIS[$i]);
            $program->setCategory($this->getReference(self::CATEGORIES[$i]));
            $slug = $this->slugger->slug($program->getTitle());
            $program->setSlug($slug);

            $rand = rand(0, 1);
            $program->setOwner($this->getReference(self::OWNER[$rand]));

            $manager->persist($program);
            $this->addReference('program_' . $i, $program);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
        ];
    }
}