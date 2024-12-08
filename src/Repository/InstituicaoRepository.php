<?php

declare(strict_types=1);

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

    public function infoByHost(string $host): array
    {
        preg_match('/fahor|cfjl/', $host, $match);
        $sigla = $match[0] ?? '';

        return $this->createQueryBuilder('i')
                    ->select('i')
                    ->where('i.sigla = :sigla')
                    ->setParameter('sigla', strtoupper($sigla))
                    ->getQuery()
                    ->getArrayResult()[0] ?? [];
    }

    public function getSiglas(): array
    {
        $siglas =  $this->createQueryBuilder('i')
                        ->select('i.sigla')
                        ->orderBy('i.sigla', 'ASC')
                        ->getQuery()
                        ->getResult();
        
        return array_column($siglas, 'sigla');
    }
}
