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
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Foto::class);
    }

    public function add(Foto $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        $unitOfWork = $this->getEntityManager()->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        if ($entity->getDestaque() == 'S' && isset($changeSet['identificador'])) {
            $album = $this->getEntityManager()->getReference(Album::class, $entity->getAlbum()->getId());
            $album->setDestaque($entity->getIdentificador());
            $this->getEntityManager()->persist($album);
        }

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

    public function defineFotoDestaque(Foto $entity): void
    {
        // se a foto atual já é o destaque, ignora e não realiza o update
        if ($entity->getDestaque() == 'S') {
            return;
        }

        // define na tabela de fotos o registro que é a foto destaque do álbum
        $dql = <<<DQL
            UPDATE 
                App\Entity\Foto f 
            SET 
                f.destaque = (CASE WHEN f.id = ?1 THEN 'S' ELSE 'N' END) 
            WHERE 
                f.album = ?2
        DQL;

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(1, $entity->getId());
        $query->setParameter(2, $entity->getAlbum());
        $query->execute();

        // define na tabela de álbuns o identificador da foto destaque que foi atualizado
        $dql = <<<DQL
            UPDATE 
                App\Entity\Album a 
            SET 
                a.destaque = (SELECT f.identificador FROM App\Entity\Foto f WHERE f.id = ?1) 
            WHERE 
                a.id = ?2
        DQL;

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(1, $entity->getId());
        $query->setParameter(2, $entity->getAlbum());
        $query->execute();
    }

    /**
     * Retorna detalhes da imagem do álbum que foi solicitada
     *
     * @param Album $album
     * @param string $identificador
     * @return Foto|null
     */
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
