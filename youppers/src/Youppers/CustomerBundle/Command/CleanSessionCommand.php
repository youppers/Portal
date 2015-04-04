<?php
/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Youppers\CustomerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

class CleanSessionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('youppers:customer:session:clean')
            ->setDescription('Clean useless sessions')
            ->addOption('delete','y', InputOption::VALUE_OPTIONAL,'Delete sessions', true)
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will remove old and useless sessions.

  <info>php %command.full_name%</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
	{
		$n = $this->getContainer()->get('youppers.customer.session')->clean($input->getOption('delete'));
		$output->writeln(sprintf("Deleted %d sessions",$n));		
    }
}
