<?php

namespace App\Command;

use App\Exception\QuardlockApiException;
use App\Service\QuardlockServerApiClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:quardlock:check-connection',
    description: 'Vérifie la connexion Quardlock sans conserver de session client.',
)]
final class CheckQuardlockConnectionCommand extends Command
{
    public function __construct(private readonly QuardlockServerApiClient $quardlock)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->quardlock->isConfigured()) {
            $io->error('QUARDLOCK_API_KEY est absente de .env.local.');

            return Command::FAILURE;
        }

        try {
            $this->quardlock->verifyConnection();
            $io->success('Connexion Quardlock vérifiée. La session de test a été révoquée.');

            return Command::SUCCESS;
        } catch (QuardlockApiException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
    }
}
