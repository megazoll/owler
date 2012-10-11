<?php

namespace Megazoll\Owler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Megazoll\Owler\Collector\HeadHunterCompany;
use Megazoll\Owler\Collector\HeadHunterAgency;

class GrabCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('owler:grab')
            ->setDescription('Collect companies from job boards')
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'hhc for companies and hha for agencies'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        if ($type == 'hhc') {
            $collector = new HeadHunterCompany;
        } elseif ($type == 'hha') {
            $collector = new HeadHunterAgency;
        }
        $companies = $collector->collect();

        array_map(function (array $company) use ($output) {
            $output->writeln($company['title'].'; '.$company['vacancies']);
        }, $companies);
    }
}
