<?php

namespace App\Repository;

use App\Entity\LigneCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LigneCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneCommande::class);
    }

    public function getTopSellingBooksByMonth(int $month, int $year): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT l.titre AS titre, SUM(lc.quantite) AS total
            FROM ligne_commande lc
            INNER JOIN livres l ON lc.livre_id = l.id
            INNER JOIN commande c ON lc.commande_id = c.id
            WHERE MONTH(c.date_commande) = :month
              AND YEAR(c.date_commande) = :year
            GROUP BY l.id
            ORDER BY total DESC
            LIMIT 5
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'month' => $month,
            'year' => $year,
        ]);

        return $result->fetchAllAssociative();
    }
}