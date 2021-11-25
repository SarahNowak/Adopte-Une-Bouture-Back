<?php

namespace App\DataFixtures;

use App\Entity\Ads;
use App\Entity\Category;
use App\Entity\Growth;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Nelmio\Alice\Loader\NativeLoader;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // we instantiate the faker to give it the desired language
        // the faker has some class (the providers)
        $faker = Factory::create('fr_FR');

        // we add 3 providers provided by FakerPHP
        // $faker->addProvider(new Ads($faker));
        // $faker->addProvider(new Category($faker));
        // $faker->addProvider(new User($faker));

        // make $faker in parameter so that our $loader takes it into account
        $loader = new NativeLoader($faker);

        //import the fixture file and get the generated entities
        $entities = $loader->loadFile(__DIR__ . '/fixtures.yaml')->getObjects();
        

        //stack the list of objects to save in DB
        foreach ($entities as $entity) {
        $manager->persist($entity);
        };

        //save
        $manager->flush();
    }
}
