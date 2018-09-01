<?php

namespace App\Command;

use App\Entity\Fiction;
use App\Entity\FictionChapter;
use App\Entity\User;
use App\Repository\FictionRepository;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use function Functions\getFic;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class ImportFictionsFromExportCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    public const FICTION_BATCH_SIZE = 300;

    private $fictionRepository;
    private $userRepository;
    private $userRoleRepository;
    private $defaultAuthor;
    private $entityManager;

    protected static $defaultName = 'app:import-fictions';

    public function __construct(
        UserRepository $userRepository,
        FictionRepository $fictionRepository,
        UserRoleRepository $userRoleRepository,
        EntityManagerInterface $entityManager,
        ?string $name = null)
    {
        parent::__construct($name);
        $this->userRepository = $userRepository;
        $this->userRoleRepository = $userRoleRepository;
        $this->defaultAuthor = $this->getDefaultAuthor();
        $this->entityManager = $entityManager;
        $this->fictionRepository = $fictionRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import fictions from export')
            ->addArgument('base_folder', InputArgument::REQUIRED, 'Path of base folder where exported fictions lives');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $this->io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('base_folder');
        $finder = new Finder();
        $finder->in($path)->files();
        $this->io->progressStart($finder->count());
        $count = 0;
        foreach ($finder as $file) {
            ++$count;
            $fiction = $this->parseFic($file->getContents());
            if ($this->fictionRepository->findOneBy(['title' => $fiction->getTitle()])) {
                $this->io->text('<comment>Fiction ' . $fiction->getTitle() . ' already exists. Skipping it...</comment>');
            } else {
                $fiction->addAuthor($this->defaultAuthor);
                $this->entityManager->persist($fiction);
            }
            $this->io->progressAdvance();
            if ($count % self::FICTION_BATCH_SIZE === 0) {
                $this->io->text('<comment>Flushing entities...</comment>');
                $this->entityManager->flush();
                $this->entityManager->clear();
                $this->defaultAuthor = $this->getDefaultAuthor();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->io->progressFinish();

        $this->io->success("Successfully persisted $count fictions!");
    }

    protected function printMemoryUsage(): void
    {
        $mem_usage = memory_get_usage(true);

        if ($mem_usage < 1024) {
            $this->io->comment($mem_usage . ' bytes');
        } elseif ($mem_usage < 1048576) {
            $this->io->comment(round($mem_usage / 1024, 2) . ' kB');
        } else {
            $this->io->comment(round($mem_usage / 1048576, 2) . ' MB');
        }
    }

    protected function parseFic(string $fileContent): Fiction
    {
        $fictionChapters = getFic($fileContent);
        $fictionInformations = array_shift($fictionChapters);
        $fiction = (new Fiction())
            ->setTitle($fictionInformations['titre']);

        $this->io->section('Getting ' . $fiction->getTitle() . ' and its ' . $fictionInformations['chapitres'] . ' chapter(s)');
        $this->printMemoryUsage();
        foreach ($fictionChapters as $index => $fictionChapter) {
            $createdAt = \DateTimeImmutable::createFromFormat(
                'd/m/Y H:i:s',
                $fictionChapter['date'] . ' ' . $fictionChapter['heure']
            );
            $chapter = (new FictionChapter())
                ->setTitle($fictionChapter['titre'] === '' ? 'Chapitre ' . ($index + 1) : $fictionChapter['titre'])
                ->setCreatedAt($createdAt)
                ->setPosition($index + 1)
                ->setBody($fictionChapter['contenu']);

            $fiction->addChapter($chapter);
        }

        return $fiction;

    }

    protected function getDefaultAuthor(): ?User
    {
        return $this->userRepository->findOneWithRole(
            $this->userRoleRepository->findOneBy(['role' => 'ROLE_ADMIN'])
        );
    }

}
