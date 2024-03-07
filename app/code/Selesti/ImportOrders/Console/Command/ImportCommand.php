<?php

namespace Selesti\ImportOrders\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class ImportCommand
 *
 * @package CedricBlondeau\CatalogImportCommand\Console\Command
 */
class ImportCommand extends Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $state
    ) {
        $this->objectManager = $objectManager;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('selestiorders:import')
            ->setDescription('Import Orders')
            ->addArgument('filename', InputArgument::REQUIRED, "/pub/selesti/importorders");
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $import = $this->getImportModel();
        // $output->writeln($input->getArgument('filename'));
        $conn = $this->objectManager->create('\Magento\Framework\App\ResourceConnection')->getConnection();;
        try {
            $result = $import->execute($input->getArgument('filename'),$conn);
            if ($result) {
                $output->writeln('<info>The import was successful.</info>');
            } else {
                $output->writeln('<error>Import failed.</error>');
            }

        } catch (FileNotFoundException $e) {
            $output->writeln('<error>File not found.</error>');

        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>Invalid source.</error>');
            $output->writeln("Log trace:");
        }
    }

    protected function getImportModel()
    {
        $this->state->setAreaCode('adminhtml');
        return $this->objectManager->create('Selesti\ImportOrders\Model\Import');
    }
}