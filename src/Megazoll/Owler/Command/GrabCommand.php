<?php

namespace Megazoll\Owler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Megazoll\Owler\Collector\HeadHunter;

class GrabCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('owler:grab')
            ->setDescription('Collect companies from job boards')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collector = new HeadHunter;
        $companies = $collector->collect();

        array_map(function (array $company) use ($output) {
            $output->writeln($company['title'].': '.$company['vacancies']);
        }, $companies);
    }
}
