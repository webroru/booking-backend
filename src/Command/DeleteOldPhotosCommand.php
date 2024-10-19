<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:delete-old-photos',
    description: 'Remove photos of IDs and passports',
)]
class DeleteOldPhotosCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly Filesystem $filesystem,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $startDate = (new \DateTime('+2 days'))->format('Y-m-d');
        $photosDirectory = $this->params->get('photos_directory');

        if (!$photosDirectory) {
            $io->error("Param 'photos_directory' is not configured");
            return Command::FAILURE;
        }

        $io->success("Photos for $startDate will be removed");
        $this->filesystem->remove("$photosDirectory/$startDate");
        $io->success('All photos have been removed.');

        return Command::SUCCESS;
    }
}
