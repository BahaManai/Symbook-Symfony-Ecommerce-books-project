<?php

namespace App\Repository;

use App\Entity\Livres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livres>
 */
class LivresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livres::class);
    }

//    /**
//     * @return Livres[] Returns an array of Livres objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Livres
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function search(string $filterBy, string $search): array
    {
        $qb = $this->createQueryBuilder('l');

        switch ($filterBy) {
            case 'titre':
                $qb->andWhere('l.titre LIKE :search');
                break;
            case 'auteur':
                $qb->andWhere('l.auteur LIKE :search');
                break;
            case 'categorie':
                $qb->join('l.categorie', 'c')
                    ->andWhere('c.libelle LIKE :search');
                break;
            default:
                return [];  // On retourne un tableau vide si le filterBy est incorrect.
        }

        return $qb
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('l.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
