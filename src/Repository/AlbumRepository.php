<?php

declare(strict_types=1);

namespace App\Repository;

use Alura\Leilao\Model\Usuario;
use App\DTO\AlbumInputDTO;
use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends ServiceEntityRepository<Album>
 *
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private ValidatorInterface $validator,
        private Security $security,
    ) {
        parent::__construct($registry, Album::class);
    }

    public function albunsListWithPhotosCount(): array
    {
        $dql = <<<DQL
            SELECT a.id, a.instituicao, a.data, a.titulo, a.acessos, a.status, COUNT(f) qtdfotos
            FROM App\Entity\Album a
            LEFT JOIN a.fotos f
            GROUP BY a.id, a.instituicao, a.data, a.titulo, a.acessos, a.status
            ORDER BY a.data DESC, a.titulo, a.instituicao
        DQL;
        return $this->getEntityManager()->createQuery($dql)->getResult();
    }

    public function add(Album $entity, bool $flush=false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function storeFromDTO(AlbumInputDTO $dto, ?Album $album=null, bool $flush=false): int
    {
        $errors = $this->validator->validate($dto);
        if (count($errors)) {
            throw new \DomainException("Falha ao validar dados recebidos\n\n" . (string) $errors);
        }

        if (!$album) {
            $album = new Album(
                $dto->instituicao,
                $dto->data,
                $dto->local,
                $dto->titulo,
            );
            $album->setUsuario($this->security->getUser());
        } else {
            $album->setInstituicao($dto->instituicao);
            $album->setData($dto->data);
            $album->setLocal($dto->local);
            $album->setTitulo($dto->titulo);
        }

        $album->setStatus($dto->status);
        $album->setAddtag($dto->addtag);

        $this->add($album, $flush);

        return $album->getId();
    }
}
