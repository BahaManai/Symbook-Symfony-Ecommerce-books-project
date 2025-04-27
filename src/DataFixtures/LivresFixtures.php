<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Faker\Factory;
use App\Entity\Livres;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LivresFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($j = 1; $j < 6; $j++) {
            $faker = Factory::create('fr_FR');
            $cat = new Categories();
            $names = ["Romance", "Thriller", "Drama","Biography","Comedy"];
            $cat->setLibelle($names[$j - 1])->setSlug(str_replace(' ', '-', $names[$j - 1]))->setDescription($faker->text);
            $manager->persist($cat);
            for ($i = 1; $i <= random_int(10,15); $i++) {
                $livre = new Livres();
                $titre = $faker->name;
                $slug = str_replace(' ', '-', $titre);
                $slug = strtolower($slug);
                $livre->setTitre($titre)
                    ->setSlug($slug)
                    ->setIsbn($faker->isbn13())
                    ->setResume($faker->text)
                    ->setEditeur($faker->company)
                    ->setAuteur($faker->name)
                    ->setDateedition($faker->dateTimeBetween("-30 years", "now"))
                    ->setImage("https://picsum.photos/500/?id=" . $i)
                    ->setPrix($faker->randomFloat(nbMaxDecimals: 2, min: 10, max: 700))
                    ->setCategorie($cat);
                $manager->persist($livre);
            }
        }
        $manager->flush();
    }
}