<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Foto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Foto>
 *
 * @method Foto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foto[]    findAll()
 * @method Foto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Foto::class);
    }
}
