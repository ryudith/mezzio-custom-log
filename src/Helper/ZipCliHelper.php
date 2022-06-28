<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI ZIP helper.
 */
class ZipCliHelper extends Command
{
    /**
     * Reference to input instance.
     * 
     * @var InputInterface $input
     */
    private InputInterface $input;

    /**
     * Reference to outout instance.
     * 
     * @var OutputInterface $output
     */
    private OutputInterface $output;

    public function __construct (
        /**
         * Assoc array library configuration.
         * 
         * @var array $config
         */
        private array $config
    ) {
        parent::__construct();
    }

    /**
     * Configure argument and options for CLI command.
     * 
     * @return void
     */
    protected function configure () : void 
    {
        $this->addOption('dest', null, InputOption::VALUE_OPTIONAL, 'Destination ZIP file');
        $this->addOption('src', null, InputOption::VALUE_OPTIONAL, 'Source directory contain log files');
        $this->addOption('start', null, InputOption::VALUE_OPTIONAL, 'Start datetime modify log file to put in ZIP');
        $this->addOption('end', null, InputOption::VALUE_OPTIONAL, 'End datetime modify log file to put in ZIP');
        $this->addArgument('cmd', InputArgument::OPTIONAL, 'Command to run, default \'help\'.');
    }

    /**
     * Execute CLI helper.
     * 
     * @param InputInterface $input CLI input reference.
     * @param OutputInterface $output CLI output reference.
     * @return int Result of process.
     */
    public function execute (InputInterface $input, OutputInterface $output) : int
    {
        $this->input = $input;
        $this->output = $output;

        $cmd = strtolower($input->getArgument('cmd') ?? '');
        if ($cmd === 'pack')
        {
            return $this->pack();
        }

        return $this->help();
    }

    /**
     * Show help information.
     * 
     * @return int Always return 0 or Command::SUCCESS
     */
    private function help () : int
    {
        $this->output->writeln("\nDefault argument is 'help', use 'pack' to start ZIP log file.\n\n".
            "help -> Show this help information.\n".
            "pack -> Pack log files to ZIP file.\n\n".
            "Example usage : \n\n".
            "  $ [laminas-registered-command] pack --dest=./data/zip/log_20220615.zip --src=./data/log --start=\"2022-06-10 00:00:00\" --end=\"2022-06-16 00:00:00\"\n".
            "  $ [laminas-registered-command] pack --src=./data/log --start=\"2022-06-10 00:00:00\" --end=\"2022-06-16 00:00:00\"\n".
            "  $ [laminas-registered-command] pack --end=\"2022-06-16 00:00:00\"\n\n".
            "Options list :");

        $table = new Table($this->output);
        $table
            ->setHeaders(['Option', 'Description', 'Example Value', 'Default Value'])
            ->setRows([
                ['dest', 'Destination ZIP file', '--dest=./data/zip/log_20220615.zip', './data/zip/log_[date(\'Ymd\')].zip'],
                ['src', 'Source log location directory', '--src=./data/log', './data/log'],
                ['start', 'Start datetime modify log file to put in ZIP', '--start="2022-06-10 00:00:00"', '>= date(\'Y-m-d 00:00:00\')'],
                ['end', 'End datetime modify log file to put in ZIP', '--end="2022-06-16 00:00:00"', '< date(\'Y-m-d 00:00:00\') + 1 day'],
            ]);
        $table->render();

        return Command::SUCCESS;
    }

    /**
     * Do packing log files to ZIP.
     * 
     * @return int Return process result in int
     */
    private function pack () : int 
    {
        $dest = $this->input->getOption('dest');
        if ($dest === null)
        {
            $dest = $this->config['zip_log_filename'];
        }

        $src = $this->input->getOption('src');
        if ($src === null)
        {
            $src = $this->config['log_dir'];
        }

        $start = $this->input->getOption('start');
        $end = $this->input->getOption('end');

        $result = Zip::packLog($dest, $src, $start, $end);
        if ($result)
        {
            return Command::SUCCESS;
        }

        print("Error : ".Zip::$errorMessage."\n\n");

        return Command::FAILURE;
    }
}