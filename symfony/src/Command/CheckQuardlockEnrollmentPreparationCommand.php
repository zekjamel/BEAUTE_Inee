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
    name: 'app:quardlock:check-enrollment-preparation',
    description: 'Vérifie le premier appel Client API Quardlock sans conserver de jeton.',
)]
final class CheckQuardlockEnrollmentPreparationCommand extends Command
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
            $this->quardlock->verifyEnrollmentPreparation();
            $io->success('Le jeton Client API est accepté pour préparer l’enrôlement. Le jeton de test a été révoqué.');

            return Command::SUCCESS;
        } catch (QuardlockApiException $exception) {
            $io->error(sprintf('%s (%s%s)', $exception->getMessage(), $exception->getEndpoint(), $exception->getHttpStatus() ? ' · HTTP ' . $exception->getHttpStatus() : ''));

            return Command::FAILURE;
        }
    }
}
