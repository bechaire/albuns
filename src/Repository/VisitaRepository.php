<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Visita;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visita>
 *
 * @method Visita|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visita|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visita[]    findAll()
 * @method Visita[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visita::class);
    }
}
