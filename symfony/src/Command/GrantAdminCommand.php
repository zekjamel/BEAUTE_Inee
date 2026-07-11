<?php

namespace App\Command;

use App\Entity\AccountActivationToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:admin:grant',
    description: 'Accorde le rôle administrateur à un compte existant.',
)]
final class GrantAdminCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail du compte à promouvoir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = mb_strtolower(trim((string) $input->getArgument('email')));
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user instanceof User) {
            $user = (new User())
                ->setEmail($email)
                ->setRoles(['ROLE_ADMIN'])
                ->setIsActive(false);

            $plainToken = bin2hex(random_bytes(32));
            $activationToken = (new AccountActivationToken())
                ->setUser($user)
                ->setTokenHash(hash('sha256', $plainToken))
                ->setExpiresAt(new \DateTimeImmutable('+7 days'));

            $this->entityManager->persist($user);
            $this->entityManager->persist($activationToken);
            $this->entityManager->flush();

            $output->writeln(sprintf('<info>Profil administrateur créé pour %s.</info>', $email));
            $output->writeln(sprintf('Lien d’activation : http://127.0.0.1:8002/account/activate/%s', $plainToken));

            return Command::SUCCESS;
        }

        $user->setRoles(array_values(array_unique([...$user->getRoles(), 'ROLE_ADMIN'])));
        $user->setIsActive(true);
        $this->entityManager->flush();

        $output->writeln(sprintf('<info>%s est maintenant administrateur.</info>', $email));

        return Command::SUCCESS;
    }
}
