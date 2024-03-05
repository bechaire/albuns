<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\AlbumRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:clear-oldcache',
    description: 'Apaga diretórios de cache antigos',
    hidden: false,
    aliases: ['app:clear-old-cache']
)]
class ClearOldCacheCommand extends Command
{
    public function __construct(
        private AlbumRepository $album,
        private Filesystem $filesystem,
        private ParameterBagInterface $parameterBag,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $output->writeln([
            'Limpando dados antigos de cache de diretórios de fotos',
            '==================================================================',
            '',
        ]);
        
        $albuns = $this->album->findAll();
        $pathUploads = $this->parameterBag->get('app.albuns.path');
        $itemsToExclude = ['destaque','miniaturas','normais','baixadas','details.json','albumInfo.json'];

        foreach($albuns as $album) {
            $albumPath = $pathUploads . '/' . $album->getId();
            if (!$this->filesystem->exists($albumPath)) {
                continue;
            }
            foreach($itemsToExclude as $subItem) {
                $this->filesystem->remove($albumPath . '/' . $subItem);
            }
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

            $this->filesystem->appendToFile($albumPath . '/albumInfo.json', json_encode($albumInfo, JSON_PRETTY_PRINT));
        }

        return Command::SUCCESS;
    }

}
