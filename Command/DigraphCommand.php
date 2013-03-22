<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Trismegiste\Mondrian\Transform\Grapher;
use Trismegiste\Mondrian\Transform\Graphviz;
use Symfony\Component\Finder\Finder;

/**
 * DigraphCommand transforms a bunch of php files into a digraph
 * rendered in the GraphViz format.
 *
 * Good thing to know :
 * - circumference
 * - centrality (by Katz and by Betweeness
 * - Closeness
 * - http://en.wikipedia.org/wiki/Entanglement_(graph_measure)
 * - http://en.wikipedia.org/wiki/Cycle_rank
 *
 */
class DigraphCommand extends Command
{

    protected function configure()
    {
        $this
                ->setName('kandinsky:digraph')
                ->setDescription('Transforms a bunch of php file into a digraph fo GraphViz')
                ->addArgument('dir', InputArgument::OPTIONAL, 'The directory to explore', './src')
                ->addArgument('report', InputArgument::OPTIONAL, 'The filename of the report', 'report.dot')
                ->addOption('ignore', 'i', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Directories to ignore', array('Tests'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('dir');
        $reportName = $input->getArgument('report');
        $ignoreDir = $input->getOption('ignore');

        $listing = array();
        $scan = new Finder();
        $scan->files()->in($directory)->name('*.php')->exclude($ignoreDir);
        foreach ($scan as $fch) {
            $listing[] = (string) $fch->getRealPath();
        }

        $transformer = new Grapher();
        $graph = $transformer->parse($listing);

        $dumper = new Graphviz($graph);
        file_put_contents($reportName, $dumper->getDot());
    }

}