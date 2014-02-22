<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class scaffoldadmin extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
    
     
	protected $name = 'scaffold:admin';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Initialize Admin Layout';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$confirm = $this->confirm("This will add an admin view folder, controller, and route. Proceed? [yes|no]");
		
		if($confirm =='yes')
		{
			//append route 
			$routepath = getcwd().'/app/routes.php';
			$routedata = File::get(getcwd().'/app/commands/ScaffoldAdmin/adminroute.txt');
			File::append($routepath, "\n".$routedata); 
			
			//install AdminController
			$controllerpath = getcwd().'/app/controllers/Admin/AdminController.php';
			$controllerdata = File::get(getcwd().'/app/commands/ScaffoldAdmin/AdminController.txt');
			File::makeDirectory(getcwd().'/app/controllers/Admin/', $mode = 0777, $recursive = false);
			File::put($controllerpath, $controllerdata); 

			//install admin view
			$viewpath = getcwd().'/app/views/admin/index.blade.php';
			$viewdata = File::get(getcwd().'/app/commands/ScaffoldAdmin/index.txt');
			File::makeDirectory(getcwd().'/app/views/admin', $mode = 0777, $recursive = false);
			File::put($viewpath, $viewdata);

		}
		else
		{
			exit;
		}
	}

	protected function getArguments()
	{
		return array(
            array('confirm', InputArgument::OPTIONAL, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
