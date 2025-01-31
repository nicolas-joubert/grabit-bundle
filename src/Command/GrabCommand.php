<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use NicolasJoubert\GrabitBundle\Grabber\Exceptions\GrabException;
use NicolasJoubert\GrabitBundle\Grabber\Exceptions\ValidationException;
use NicolasJoubert\GrabitBundle\Grabber\Grabber;
use NicolasJoubert\GrabitBundle\Manager\ExtractedDataManager;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use NicolasJoubert\GrabitBundle\Repository\SourceRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GrabCommand extends Command
{
    private SymfonyStyle $io;
    private bool $hasErrors = false;

    private EntityManagerInterface $em;
    private SourceRepositoryInterface $sourceRepository;
    private Grabber $grabber;
    private ExtractedDataManager $extractedDataManager;

    public function setServices(
        EntityManagerInterface $em,
        SourceRepositoryInterface $sourceRepository,
        Grabber $grabber,
        ExtractedDataManager $extractedDataManager,
    ): void {
        $this->em = $em;
        $this->sourceRepository = $sourceRepository;
        $this->grabber = $grabber;
        $this->extractedDataManager = $extractedDataManager;
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption(
                'source_id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specific Source.id',
            )
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->info(sprintf('Begin command "%s"', $this->getName()));

        /** @var ?string $sourceId */
        $sourceId = $input->getOption('source_id');
        if (null !== $sourceId) {
            $source = $this->sourceRepository->find($sourceId);
            if (null === $source) {
                $this->io->error(sprintf('Source with id "%s" not found', $sourceId));

                return Command::FAILURE;
            }
            $this->processSource($source);
        } else {
            foreach ($this->sourceRepository->findBy(['enabled' => true]) as $source) {
                $this->processSource($source);
            }
        }

        $endMessage = sprintf('End command "%s"', $this->getName());

        if ($this->hasErrors) {
            $this->io->error($endMessage);
        } else {
            $this->io->success($endMessage);
        }

        return Command::SUCCESS;
    }

    private function processSource(SourceInterface $source): void
    {
        $this->io->info(sprintf('Begin processing source "%s"', $source->getLabel()));

        try {
            $hasErrors = false;
            $grabbedNumber = 0;
            $grabbers = $this->grabber->grabSource($source);

            try {
                foreach ($grabbers as $grabbed) {
                    $this->extractedDataManager->createWithGrabbed($grabbed, $source);
                    ++$grabbedNumber;
                }
                $this->extractedDataManager->flushRemaining();
            } catch (ValidationException $e) {
                $hasErrors = true;
                $this->hasErrors = true;
                $this->io->error($e->getMessage().'. Source #'.$source->getId());
                $this->addError($source, $e->getMessage());
            }
        } catch (GrabException $e) {
            $hasErrors = true;
            $this->hasErrors = true;
            $this->io->error($e->getMessage());
            $this->addError($source, $e->getMessage());
        }

        $endMessage = sprintf(
            'End processing source "%s" : %s items grabbed',
            $source->getLabel(),
            $grabbedNumber
        );

        if ($hasErrors) {
            $this->io->error($endMessage);
        } else {
            $this->io->success($endMessage);
            $this->resetCountError($source);
        }
    }

    private function addError(SourceInterface $source, string $reason): void
    {
        $source
            ->setCountError($source->getCountError() + 1)
            ->setLastError($reason)
        ;
        if ($source->getCountError() > $source->getMaxNumberError()) {
            $source->setEnabled(false);
        }
        $this->em->flush();
    }

    private function resetCountError(SourceInterface $source): void
    {
        if ($source->getCountError() > 0) {
            $source->setCountError(0);
            $this->em->flush();
        }
    }
}
