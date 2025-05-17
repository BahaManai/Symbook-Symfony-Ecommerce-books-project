<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findRecentCommandes(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateCommande', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
    }

    public function getOrdersGroupedByPeriod(string $period): array
    {
        $groupBy = match ($period) {
            'day' => "DATE(c.dateCommande)",
            'week' => "CONCAT(YEAR(c.dateCommande), '-', WEEK(c.dateCommande))",
            'month' => "CONCAT(YEAR(c.dateCommande), '-', MONTH(c.dateCommande))",
            default => "DATE(c.dateCommande)"
        };

        $qb = $this->createQueryBuilder('c')
            ->select($groupBy . ' AS label, COUNT(c.id) AS count')
            ->where('c.etatPaiement = 1')
            ->groupBy('label')
            ->orderBy('label', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getOrdersOverTime(string $period = 'month'): array
    {
        // Validation de la période
        if (!in_array($period, ['day', 'week', 'month'])) {
            $period = 'month';
        }

        $format = match ($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-W%v',
            'month' => '%Y-%m',
        };

        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT DATE_FORMAT(date_commande, :format) AS label, COUNT(*) AS count
            FROM commande
            WHERE etat_paiement = 1
            GROUP BY label
            ORDER BY label ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['format' => $format]);

        return $result->fetchAllAssociative();
    }
}