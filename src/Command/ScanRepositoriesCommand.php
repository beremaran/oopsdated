<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 08.03.2019
 * Time: 17:54
 */

namespace App\Command;


use App\Repository\GithubRepoRepository;
use Enqueue\Client\ProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanRepositoriesCommand extends Command
{
    protected $producer;
    protected $repoRepository;

    protected static $defaultName = "app:scan-repositories";

    public function __construct(GithubRepoRepository $repoRepository,
                                ProducerInterface $producer)
    {
        $this->producer = $producer;
        $this->repoRepository = $repoRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repos = $this->repoRepository->findAll();
        foreach ($repos as $repo) {
            $this->producer->sendEvent('scanRepo', json_encode([
                'repoName' => $repo->getName()
            ]));
        }
    }


}