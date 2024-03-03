<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Album;
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

    public function add(Foto $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllPhotos(Album $album, bool $apenasVisiveis = false): array
    {
        $dql = <<<DQL
            SELECT f.id, f.arquivo, f.identificador, f.visivel, f.destaque, f.ordem, f.opcoes
            FROM App\Entity\Foto f
            WHERE f.album = ?1
            AND (f.visivel = ?2 OR ?2 = '')
            ORDER BY f.ordem, f.arquivoorigem, f.id
        DQL;
        $query = $this->getEntityManager()->createQuery($dql);
        $visiveis = $apenasVisiveis ? 'S' : '';
        $query->setParameter(1, $album);
        $query->setParameter(2, $visiveis);

        return $query->getArrayResult();
    }

    public function remove(Foto $entity, bool $flush = false)
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            return $this->getEntityManager()->flush();
        }
        return 'x';
    }

    public function getInfoFoto(Album $album, string $identificador): ?Foto
    {
        $dql = <<<DQL
            SELECT f
            FROM App\Entity\Foto f
            WHERE f.album = ?1
            AND f.identificador = ?2
        DQL;
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(1, $album);
        $query->setParameter(2, $identificador);

        return $query->getOneOrNullResult();
    }

}
