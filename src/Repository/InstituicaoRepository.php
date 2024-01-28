<?php

namespace App\Repository;

use App\Entity\Instituicao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Instituicao>
 *
 * @method Instituicao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Instituicao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instituicao[]    findAll()
 * @method Instituicao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstituicaoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Instituicao::class);
    }

//    /**
//     * @return Instituicao[] Returns an array of Instituicao objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Instituicao
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
