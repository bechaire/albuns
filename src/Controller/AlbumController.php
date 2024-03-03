<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ImageException;
use App\Interface\ImageFacadeInterface;
use App\Repository\AlbumRepository;
use App\Repository\FotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private FotoRepository $fotoRepository,
        private Filesystem $filesystem,
        private ImageFacadeInterface $image,
        private LoggerInterface $log,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/images/cache/{idalbum}/{tamanho}/{arquivo}', name: 'app_exibe_imagem', methods: ['GET'])]
    public function exibeImagem(int $idalbum, string $tamanho, string $arquivo, Request $request): Response
    {
        $pathArquivoCriado = $this->cacheiaArquivoSolicitado($idalbum, $tamanho, $arquivo, $request);
        return new BinaryFileResponse(
            file: $pathArquivoCriado,
            autoEtag: true,
            autoLastModified: true,
        );
    }

    private function cacheiaArquivoSolicitado(int $idalbum, string $tamanho, string $arquivo, Request $request): string
    {
        $identificador = str_ireplace(['.jpg', '.jpeg', '.jfif'], '', $arquivo);

        $album = $this->albumRepository->find($idalbum);

        $foto = $this->fotoRepository->getInfoFoto($album, $identificador);
        if (!$foto) {
            throw $this->createNotFoundException('O arquivo solicitado não foi encontrado #1');
        }

        $arquivoArmazenado = $foto->getArquivo();
        $pathArquivoOrigem = $this->getParameter('app.albuns.path') . "/{$idalbum}/{$arquivoArmazenado}";
        if (!$this->filesystem->exists($pathArquivoOrigem)) {
            throw $this->createNotFoundException('O arquivo solicitado não foi encontrado #2');
        }

        $tamanhosDisponiveis = $this->getParameter('app.albuns.cache.sizes');
        $tamanhoPX = array_search($tamanho, $tamanhosDisponiveis);
        if (!$tamanhoPX) {
            throw $this->createNotFoundException('O arquivo solicitado não foi encontrado #3');
        }

        $pathDestino = $this->getParameter('app.albuns.cache.path') . "/{$idalbum}/{$tamanho}";
        try {
            $this->filesystem->mkdir($pathDestino, 0766);
        } catch (IOException) {
            $this->log->error('Não foi possível criar o diretório de destino', [$pathDestino]);
            throw $this->createNotFoundException('O arquivo solicitado não pôde ser criado #1');
        }

        $pathArquivoDestino = $pathDestino . "/{$identificador}.jpg";

        // pode haver solicitação de um arquivo a ser cacheado que foi criado a segundos antes
        if ($this->filesystem->exists($pathArquivoDestino)) {
            return $pathArquivoDestino;
        }

        $opcoesArray = json_decode($foto->getOpcoes(), true);

        try {
            $compressaoJpeg = 80;
            $this->image->loadImage($pathArquivoOrigem);

            if ($opcoesArray['fliph'] > 0) {
                $this->image->flipH();
            }
            if ($opcoesArray['flipv'] > 0) {
                $this->image->flipV();
            }
            if ($opcoesArray['rotate'] > 0) {
                $this->image->rotate($opcoesArray['rotate']);
            }

            $this->image->thumb($tamanhoPX);

            if ($tamanho == 'normal' && $album->getAddtag() == 'S') {
                $tagDominio = strpos($request->getSchemeAndHttpHost(), 'cfjl.com.br') ? 'cfjl.com.br' : 'fahor.com.br';
                $this->image->addTag($tagDominio);
            }

            $this->image->saveAsJpeg(compress: $compressaoJpeg, newFilePath: $pathArquivoDestino);
        } catch (ImageException) {
            $this->log->error('Não foi possível criar o arquivo de destino', [$pathArquivoDestino]);
            throw $this->createNotFoundException('O arquivo solicitado não pôde ser criado #2');
        }

        return $pathArquivoDestino;
    }
}
