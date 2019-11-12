<?php
//Namespace should be FreePBX\Console\Command
namespace FreePBX\Console\Command;

//Symfony stuff all needed add these
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;
//Process
use Symfony\Component\Process\Process;
class Util extends Command {
	protected function configure(){
		$this->setName('util')
			->setDescription(_('Common utilities'))
			->setDefinition(array(
				new InputArgument('args', InputArgument::IS_ARRAY, null, null),));
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		global $amp_conf;

		$args = $input->getArgument('args');
		$command = isset($args[0])?$args[0]:'';
		switch ($command) {
			case 'cleanplaybackcache':
				$output->writeln(_("Starting Cache cleanup"));
				$days = \FreePBX::Config()->get("CACHE_CLEANUP_DAYS");

				$path = \FreePBX::Config()->get("AMPPLAYBACK");
				$path = trim($path);

				if(empty($path) || $path == "/") {
					$output->writeln("<error>".sprintf(_("Invalid path %s"),$path)."</error>");
					exit(1);
				}

				$user = \FreePBX::Config()->get("AMPASTERISKWEBUSER");

				$finder = new Finder();
				foreach($finder->in($path)->date("before $days days ago") as $file) {
					$info = posix_getpwuid($file->getOwner());
					if($info['name'] != $user) {
						continue;
					}
					if ($file->isFile()) {
						$output->writeln(sprintf(_("Removing file %s"),basename($file->getRealPath())));
						unlink($file->getRealPath());
					}
				}
				$output->writeln(_("Finished cleaning up cache"));
			break;
			case 'signaturecheck':
				\module_functions::create()->getAllSignatures(false,true);
			break;
			case 'tablefix':
				$cmd = 'mysqlcheck -u'.$amp_conf['AMPDBUSER'].' -p'.$amp_conf['AMPDBPASS'].' --repair --all-databases';
				$process = new Process($cmd);
				try {
					$output->writeln(_("Attempting to repair MySQL Tables (this may take some time)"));
					$process->mustRun();
					$output->writeln(_("MySQL Tables Repaired"));
				} catch (ProcessFailedException $e) {
					$output->writeln(sprintf(_("MySQL table repair Failed: %s"),$e->getMessage()));
				}
				$cmd = 'mysqlcheck -u'.$amp_conf['AMPDBUSER'].' -p'.$amp_conf['AMPDBPASS'].' --optimize --all-databases';
				$process = new Process($cmd);
				try {
					$output->writeln(_("Attempting to optimize MySQL Tables (this may take some time)"));
					$process->mustRun();
					$output->writeln(_("MySQL Tables Repaired"));
				} catch (ProcessFailedException $e) {
					$output->writeln(sprintf(_("MySQL table repair Failed: %s"),$e->getMessage()));
				}
			break;
			case "zendid":
				$output->writeln("===========================");
				foreach(zend_get_id() as $id){
					$output->writeln($id);
				}
				$output->writeln("===========================");
			break;
			case "resetastdb":
				FreePBX::Core()->devices2astdb();
				FreePBX::Core()->users2astdb();
			break;
			default:
				$output->writeln('Invalid argument');
			break;
		}
	}
}
