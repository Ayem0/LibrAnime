<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Season;
use App\Entity\Format;
use App\Entity\Status;
use App\Entity\Categorie;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    // ...
    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $password = $this->hasher->hashPassword($admin, 'password');
        $admin->setPassword($password);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('user@gmail.com');
        $passwordUser = $this->hasher->hashPassword($user, 'password');
        $user->setPassword($passwordUser);
        $manager->persist($user);

        $categories = ['Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 'Horror', 'Mahou Shoujo', 'Mecha', 'Music', 'Mystery', 'Psychological', 'Romance', 'Sci-Fi', 'Slice of Life',
        'Sports', 'Supernatural', 'Thriller'];
        $formats = ['TV', 'TV_SHORT', 'MOVIE', 'SPECIAL', 'OVA', 'ONA', 'MUSIC'];
        $seasons = ['WINTER', 'SPRING', 'SUMMER', 'FALL'];
        $statusArray = ['FINISHED', 'RELEASING', 'NOT_YET_RELEASED', 'CANCELLED', 'HIATUS'];

        for ($i = 0 ; $i<count($categories); $i++) {
            $categorie = new Categorie;
            $categorie->setNom($categories[$i]);
            $manager->persist($categorie);
        }

        for ($i = 0 ; $i<count($formats); $i++) {
            $format = new Format;
            $format->setNom($formats[$i]);
            $manager->persist($format);
        }

        for ($i = 0 ; $i<count($seasons); $i++) {
            $season = new Season;
            $season->setNom($seasons[$i]);
            $manager->persist($season);
        }

        for ($i = 0 ; $i<count($statusArray); $i++) {
            $status = new status;
            $status->setNom($statusArray[$i]);
            $manager->persist($status);
        }

        $manager->flush();
    }
}
