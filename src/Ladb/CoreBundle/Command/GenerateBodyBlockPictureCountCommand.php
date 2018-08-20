<?php

namespace Ladb\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Ladb\CoreBundle\Utils\BlockBodiedUtils;

class GenerateBodyBlockPictureCountCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('ladb:generate:bodyblockpicturecount')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force updating')
            ->setDescription('Generate bodyblockpicturecount')
            ->setHelp(<<<EOT
The <info>ladb:generate:bodyblockpicturecount</info> command generate bodyblockpicturecount
EOT
            );
    }

    private function _process($entityClass, $em, OutputInterface $output, $blockBodiedUtils)
    {

        $output->write('<info>Retrieve ' . $entityClass . '...</info>');

        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select(array( 'e' ))
            ->from($entityClass, 'e')
        ;

        try {
            $entities = $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $entities = array();
        }

        $output->writeln('<comment> [' . count($entities) . ' ' . $entityClass . ']</comment>');

        $entityCount = 0;
        foreach ($entities as $entity) {
            $blockBodiedUtils->preprocessBlocks($entity);
            $entityCount++;
        }
        unset($entities);

        return $entityCount;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $forced = $input->getOption('force');

        $om = $this->getContainer()->get('doctrine')->getManager();
        $blockBodiedUtils = $this->getContainer()->get(BlockBodiedUtils::NAME);

        $entityCount = 0;

        $entityCount += $this->_process('LadbCoreBundle:Blog\Post', $om, $output, $blockBodiedUtils);
        $entityCount += $this->_process('LadbCoreBundle:Faq\Question', $om, $output, $blockBodiedUtils);
        $entityCount += $this->_process('LadbCoreBundle:Howto\Article', $om, $output, $blockBodiedUtils);
        $entityCount += $this->_process('LadbCoreBundle:Wonder\Creation', $om, $output, $blockBodiedUtils);
        $entityCount += $this->_process('LadbCoreBundle:Wonder\Workshop', $om, $output, $blockBodiedUtils);

        if ($forced) {
            $om->flush();
            $output->writeln('<info>' . $entityCount . ' generated</info>');
        } else {
            $output->writeln('<info>' . $entityCount . ' to generate</info>');
        }

    }
}
