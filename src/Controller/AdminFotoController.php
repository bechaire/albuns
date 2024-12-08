<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Foto;
use App\Exception\ImageException;
use App\Interface\ImageFacadeInterface;
use App\Repository\AlbumRepository;
use App\Repository\FotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminFotoController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private FotoRepository $fotoRepository,
        private SluggerInterface $slugger,
        private Filesystem $filesystem,
        private ImageFacadeInterface $image,
        private LoggerInterface $log,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/admin/albuns/{idalbum}/fotos', name: 'app_admin_albuns_fotos_list', methods: ['GET'], condition: "request.headers.get('Accept') matches '/.+?json/'")]
    public function adminAlbunsFotosList(int $idalbum, Request $request): Response
    {
        $album = $this->entityManager->getReference(Album::class, $idalbum);
        $fotosDoAlbum = $this->fotoRepository->getAllPhotos($album);

        $pathCaches = array_reduce($this->getParameter('app.albuns.cache.sizes'), function($acumulador, $item) use ($idalbum) {
            $acumulador["path_{$item}"] = "/images/cache/{$idalbum}/{$item}";
            return $acumulador;
        }, []);

        return $this->json([
            ...$pathCaches,
            'id' => $idalbum,
            'fotos' => $fotosDoAlbum,
        ]);
    }

    #[Route('/admin/albuns/{idalbum}/fotos', name: 'app_admin_albuns_fotos_del', methods: ['DELETE'])]
    public function adminAlbunsFotosDelete(int $idalbum, Request $request): Response
    {
        $opcoes = json_decode($request->request->get('opcoes'), true);
        if (!$opcoes) {
            return $this->json([
                'status' => 'error',
                'message' => 'Falha ao se comunicar com o servidor, alteração não recebidas e não realizadas',
            ]);
        }

        $foto = $this->fotoRepository->find($opcoes['idfoto']);
        if (!$foto) {
            return $this->json([
                'status' => 'error',
                'message' => 'Foto não localizada no banco de dados',
            ]);
        }

        try {
            $arquivoOriginal = $this->getParameter('app.albuns.path') . '/' . $idalbum . '/' . $foto->getArquivo();
            $this->filesystem->remove($arquivoOriginal);
            $this->fotoRepository->remove($foto, true);
        } catch(IOException) {
            return $this->json([
                'status' => 'error',
                'message' => 'Falha de permissão ao remover o arquivo do servidor, contate o CI',
            ]);
        }

        $tamanhosDisponiveis = $this->getParameter('app.albuns.cache.sizes');
        foreach ($tamanhosDisponiveis as $tamanho) {
            $arquivoCacheado = $this->getParameter('app.albuns.cache.path') . "/{$idalbum}/{$tamanho}/{$foto->getIdentificador()}.jpg";
            $this->filesystem->remove($arquivoCacheado);
        }

        return $this->json([
            'status' => 'success',
            ...$opcoes
        ]);
    }

    #[Route('/admin/albuns/{idalbum}/fotos', name: 'app_admin_albuns_fotos_upd', methods: ['PATCH'])]
    public function adminAlbunsFotosUpdate(int $idalbum, Request $request): Response
    {
        $opcoes = json_decode($request->request->get('opcoes'), true);
        if (!$opcoes) {
            return $this->json([
                'status' => 'error',
                'message' => 'Falha ao se comunicar com o servidor, alteração não recebidas e não realizadas',
            ]);
        }

        $foto = $this->fotoRepository->find($opcoes['idfoto']);
        if (!$foto) {
            return $this->json([
                'status' => 'error',
                'message' => 'Foto não localizada no banco de dados',
            ]);
        }

        if (!in_array($opcoes['destaque'], ['S', 'N'])) {
            throw $this->createNotFoundException('DESTAQUE com valor inválido');
        }

        if (!in_array($opcoes['visivel'], ['S', 'N'])) {
            throw $this->createNotFoundException('VISÍVEL com valor inválido');
        }

        $opcoes['ordem'] = (int) ($opcoes['ordem_nova'] ?? $opcoes['ordem']);
        $opcoes['fliph'] = (int) $opcoes['fliph'];
        $opcoes['flipv'] = (int) $opcoes['flipv'];
        $opcoes['rotate'] = (int) $opcoes['rotate'];

        $foto->setVisivel($opcoes['visivel']);
        $foto->setOrdem($opcoes['ordem']);
        $foto->setOpcoes($opcoes);

        if ($opcoes['destaque'] == 'S') {
            $this->fotoRepository->defineFotoDestaque($foto);
        }

        $this->fotoRepository->add($foto, true);

        $pathCaches = array_reduce($this->getParameter('app.albuns.cache.sizes'), function($acumulador, $item) use ($idalbum) {
            $acumulador["path_{$item}"] = "/images/cache/{$idalbum}/{$item}";
            return $acumulador;
        }, []);

        return $this->json([
            'status' => 'success',
            'identificador' => $foto->getIdentificador(),
            ...$opcoes,
            ...$pathCaches,
        ]);
    }

    #[Route('/admin/albuns/{idalbum}/fotos', name: 'app_admin_albuns_fotos_add', methods: ['POST'])]
    public function adminAlbunsFotosAdd(int $idalbum, Request $request): Response
    {
        /** @var UploadedFile $foto */
        $foto = $request->files->get('foto');
        if (!$foto) {
            return $this->json([
                'message' => 'Falha ao validar o upload, o conteúdo do arquivo não foi recebido.'
            ], 400);
        }

        if (strpos($foto->getMimeType(), 'image') !== 0) {
            return $this->json([
                'message' => sprintf('O arquivo enviado não é uma imagem, mas sim um <b>%s</b>', $foto->getMimeType())
            ], 400);
        }

        $pathDestino = $this->getParameter('app.albuns.path') . '/' . $idalbum;
        try {
            $this->filesystem->mkdir($pathDestino, 0766);
        } catch (IOException) {
            return $this->json([
                'message' => 'Falha ao criar repositório de destino para este álbum, por favor, entre em contato com a equipe de TI'
            ], 500);
        }

        $posicao = $request->request->getInt('posicao') ?: 0;

        try {
            $this->registraImagemNoAlbum($pathDestino, $foto, $posicao, $idalbum);
        } catch (FileException $e) {
            $erro = 'Falha ao salvar arquivo no diretório de destino, entre em contato com a equipe de TI';
            $this->log->error($erro, [$e]);
            return $this->json(['message' => $erro], 500);
        } catch (ImageException $e) {
            $erro = 'Falha ao renderizar a imagem, entre em contato com a equipe de TI.';
            $this->log->error($erro, [$e]);
            return $this->json(['message' => $erro], 500);
        }

        return $this->json([
            'message' => sprintf('O arquivo "%s" foi recebido com sucesso, salvo no álbum "%s" na posicao "%s"', $foto->getClientOriginalName(), $idalbum, $posicao)
        ]);
    }

    private function registraImagemNoAlbum(string $pathDestino, UploadedFile $foto, int $posicao, int $idalbum): void
    {
        $identificador = Foto::criaNovoIdentificador();
        $arquivoEnviadoRenomeado = $identificador . '.' . $foto->guessExtension();
        $arquivoEnviadoConvertido = $identificador . '.jpg';

        $foto->move(
            $pathDestino,
            $arquivoEnviadoRenomeado
        );
        $this->comprimeArquivo($pathDestino, $arquivoEnviadoRenomeado);

        /** @var Album $album */
        $album = $this->entityManager->getReference(Album::class, $idalbum);
        $entityFoto = new Foto($album);
        $entityFoto->setArquivo($arquivoEnviadoConvertido);
        $entityFoto->setIdentificador($identificador);
        $entityFoto->setOrdem($posicao);
        $entityFoto->setArquivoOrigem($foto->getClientOriginalName());

        $this->fotoRepository->add($entityFoto, true);
    }

    private function comprimeArquivo(string $pathOrigem, string $arquivoOrigem): void
    {
        $compressaoJpeg = $this->getParameter('app.albuns.jpeg.compress');
        $tamanhoMaiorLadoImagem = $this->getParameter('app.albuns.max.side');
        $this->image->loadImage($pathOrigem . DIRECTORY_SEPARATOR . $arquivoOrigem);
        $this->image->resizeToMaxSide($tamanhoMaiorLadoImagem);
        $this->image->saveAsJpeg($compressaoJpeg, true);
    }

}
