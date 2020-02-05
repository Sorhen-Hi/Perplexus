<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use App\Document\ImportState;
use App\Document\ElementStatus;
use App\Services\ElementImportService;
use App\Command\GoGoAbstractCommand;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckExternalSourceToUpdateCommand extends GoGoAbstractCommand
{
   public function __construct(DocumentManager $dm, LoggerInterface $commandsLogger,
                               TokenStorageInterface $security,
                               ElementImportService $importService)
   {
      $this->importService = $importService;
      parent::__construct($dm, $commandsLogger, $security);
   }

    protected function gogoConfigure()
    {
       $this
        ->setName('app:elements:checkExternalSourceToUpdate')
        ->setDescription('Check for updating external sources');
    }

    protected function gogoExecute($dm, InputInterface $input, OutputInterface $output)
    {

      $qb = $dm->createQueryBuilder('App\Document\ImportDynamic');

      $dynamicImports = $qb->field('refreshFrequencyInDays')->gt(0)
                ->field('nextRefresh')->lte(new \DateTime())
                ->getQuery()->execute();

      $this->log('CheckExternalSourceToUpdate : Nombre de sources à mettre à jour : ' . $dynamicImports->count());

      foreach ($dynamicImports as $key => $import)
      {
        $this->log('Updating source : ' . $import->getSourceName());
        try {
          $this->log($this->importService->startImport($import));
        } catch (\Exception $e) {
          $this->dm->persist($import);
          $import->setCurrState(ImportState::Failed);
          $message = $e->getMessage() . '</br>' . $e->getFile() . ' LINE ' . $e->getLine();
          $import->setCurrMessage($message);
          $this->error("Source: " . $import->getSourceName() . " - " . $message);
        }
      }
    }
}