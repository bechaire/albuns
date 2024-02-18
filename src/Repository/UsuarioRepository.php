<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\UsuarioInputDTO;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends ServiceEntityRepository<Usuario>
 *
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private ValidatorInterface $validator
    ) {
        parent::__construct($registry, Usuario::class);
    }

    public function add(Usuario $entity, bool $flush=false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function storeFromDTO(UsuarioInputDTO $dto, ?Usuario $usuario=null, bool $flush=false): void
    {
        
        if (!$usuario) {
            $usuario = new Usuario(
                $dto->usuario,
                $dto->email,
                $dto->nome,
            );
        } else {
            $usuario->setUsuario($dto->usuario);
            $usuario->setEmail($dto->email);
            $usuario->setNome($dto->nome);
        }

        $usuario->setRoles([$dto->papel]);
        $usuario->setAtivo($dto->ativo);

        $this->add($usuario, $flush);
    }

    public function findAllOrdered(): array
    {
        $dql = <<<DQL
            SELECT usuarios
            FROM App\Entity\Usuario usuarios
            ORDER BY usuarios.ativo DESC, usuarios.nome
        DQL;
        return $this->getEntityManager()->createQuery($dql)->getArrayResult();
    }
}
