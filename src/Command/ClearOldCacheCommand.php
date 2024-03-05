<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\AlbumRepository;
use App\Service\AlbumService;
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
        private AlbumService $albumService,
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
        $itemsToExclude = ['destaque', 'miniaturas', 'normais', 'baixadas', 'details.json'];

        foreach($albuns as $album) {
            $albumPath = $pathUploads . '/' . $album->getId();
            if (!$this->filesystem->exists($albumPath)) {
                continue;
            }

            $output->writeln('Limpando temporários antigos do álbum ' . $album->getId());
            foreach($itemsToExclude as $subItem) {
                $this->filesystem->remove($albumPath . '/' . $subItem);
            }
            
            $output->writeln([
                'Criando arquivo .json de metadados para o álbum ' . $album->getId(),
                ''
            ]);
            $this->albumService->createInfoOnUploadedAlbumDirectory($album);
        }

        return Command::SUCCESS;
    }

}
