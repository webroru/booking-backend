<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ClientRepository;
use App\Service\GuestReportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:guest-report',
    description: 'Send guest report',
)]
class GuestReportCommand extends Command
{
    public function __construct(
        private readonly GuestReportService $guestReportService,
        private readonly ClientRepository $clientRepository,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $clients = $this->clientRepository->findWithEnabledAutoSend();

        foreach ($clients as $client) {
            try {
                $this->guestReportService->reportByClient($client);
                $io->success(sprintf('Guest report for client "%s" has been sent.', $client->getName()));
            } catch (\Exception $e) {
                $io->error(sprintf(
                    'Error sending guest report for client "%s": %s',
                    $client->getName(),
                    $e->getMessage()
                ));
            }
        }

        return Command::SUCCESS;
    }
}
