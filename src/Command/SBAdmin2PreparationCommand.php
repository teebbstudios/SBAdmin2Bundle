<?php

namespace Teebb\SBAdmin2Bundle\Command;

/**
 * Move the npm package.json and gulpfile.js to project root folder,
 * then run npm and gulp command to download the latest resources files.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SBAdmin2PreparationCommand extends Command
{
    protected static $commandName = 'sbadmin2:preparation';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function configure()
    {
        $this->setDescription('Move the npm package.json and gulpfile.js to project root folder.')
            ->addArgument('path', InputArgument::OPTIONAL, 'Where to move the package and gulp files. Please Input the absolute path.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln("starting...");

        $path = $this->container->getParameter('kernel.project_dir');

        if ($input->hasArgument('path')) {
            $path = $input->getArgument('path') === null ? $path : $input->getArgument('path');
        }

        $packageResult = copy( dirname(__DIR__) . '/Resources/package/package.json',  $path.'/package.json');
        $gulpResult = copy( dirname(__DIR__) . '/Resources/package/gulpfile.js',  $path.'/gulpfile.js');

        if ($packageResult && $gulpResult){
            $message = 'Preparation done! Please run npm install and gulp vendor to install resources.';
            $this->block('[OK] - '.$message, $output, 'green', 'black');

        }else{
            $message = 'php copy function error. Please check it!';
            $this->block('[WARNNING] - '.$message, $output, 'red', 'black');
        }
    }

    private function block(
        string $message,
        OutputInterface $output,
        string $background = null,
        string $font = null
    ): void {
        $options = [];

        if (null !== $background) {
            $options[] = 'bg='.$background;
        }

        if (null !== $font) {
            $options[] = 'fg='.$font;
        }

        $pattern = ' %s ';

        if (!empty($options)) {
            $pattern = '<'.implode(';', $options).'>'.$pattern.'</>';
        }

        $output->writeln($block = sprintf($pattern, str_repeat(' ', strlen($message))));
        $output->writeln(sprintf($pattern, $message));
        $output->writeln($block);
    }

}