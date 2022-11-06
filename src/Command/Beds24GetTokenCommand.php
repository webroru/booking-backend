<?php

namespace App\Command;

use App\Entity\Token;
use App\Providers\Booking\Booking;
use App\Repository\ClientRepository;
use App\Repository\TokenRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'beds24:get-token',
    description: 'Fetch Token and RefreshToken of Beds24 by Invite Code (https://beds24.com/control3.php?pagetype=apiv2)',
)]
class Beds24GetTokenCommand extends Command
{
    public function __construct(
        private readonly Booking $booking,
        private readonly ClientRepository $clientRepository,
        private readonly TokenRepository $tokenRepository,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('code', InputArgument::REQUIRED, 'Invite Code')
            ->addArgument('client', InputArgument::REQUIRED, 'Client name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $code = $input->getArgument('code');
        $clientName = $input->getArgument('client');

        $client = $this->clientRepository->findOneBy(['name' => $clientName]);
        if (!$client) {
            throw new \Exception("Client $clientName is not found");
        }
        $dto = $this->booking->fetchToken($code);
        $token = ($client->getToken() ?? (new Token()))
            ->setToken($dto->token)
            ->setRefreshToken($dto->refreshToken)
            ->setExpiresAt(new \DateTime("+ $dto->expiresIn seconds"))
        ;
        $client->setToken($token);
        $this->tokenRepository->save($token);
        $this->clientRepository->save($client, true);
        $io->success("Token successfully generated: {$token->getToken()}, refreshToken: {$token->getToken()}, expiresIn: {$token->getExpiresAt()->format('Y-m-d H:i:s')}");

        return Command::SUCCESS;
    }
}
