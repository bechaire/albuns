<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class AlbumService
{
    public function __construct(
        private Filesystem $filesystem,
        private ParameterBagInterface $parameterBag,
        private AlbumRepository $albumRepository,
    ) {
    }

    public function limpaCachePublico(): void
    {
        // limpa diretório de cache (público)
        $this->filesystem->remove($this->parameterBag->get('app.albuns.cache.path'));

        // recria o arquivo json de metadados (backup) dentro de cada pasta álbum existente
        $albuns = $this->albumRepository->findAll();
        foreach($albuns as $album) {
            $this->createInfoOnUploadedAlbumDirectory($album);
        }
    }

    public function createInfoOnUploadedAlbumDirectory(Album $album): void
    {
        $pathUploads = $this->parameterBag->get('app.albuns.path');
        $albumPath = $pathUploads . '/' . $album->getId();

        if (!$this->filesystem->exists($albumPath)) {
            return;
        }

        $pathArquivo = $albumPath . '/albumInfo.json';

        $albumInfo = [
            'idalbum' => $album->getId(),
            'idusuario' => $album->getUsuario()->getId(),
            'status' => $album->getStatus(),
            'data' => $album->getData()->format('Y-m-d'),
            'local' => $album->getLocal(),
            'ano' => $album->getAno(),
            'titulo' => $album->getTitulo(),
            'addtag' => $album->getAddtag(),
            'acessos' => $album->getAcessos(),
            'instituicao' => $album->getInstituicao(),
            'created' => $album->getCreated()->format('Y-m-d H:i:s'),
            'fotos' => []
        ];
        $fotos = $album->getFotos();
        foreach ($fotos as $foto) {
            $albumInfo['fotos'][] = [
                'idfoto' => $foto->getId(),
                'arquivo' => $foto->getArquivo(),
                'arquivoorigem' => $foto->getArquivoOrigem(),
                'youtubeid' => $foto->getYoutubeid(),
                'visivel' => $foto->getVisivel(),
                'destaque' => $foto->getDestaque(),
                'ordem' => $foto->getOrdem(),
                'identificador' => $foto->getIdentificador(),
                'opcoes' => $foto->getOpcoes(),
                'created' => $foto->getCreated()->format('Y-m-d H:i:s'),
            ];
        }

        $this->filesystem->remove($pathArquivo);
        $this->filesystem->appendToFile($pathArquivo, json_encode($albumInfo));;
    }
}
