<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Scaffoldbootstrap extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
    
     
	protected $name = 'scaffold:bootstrap';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Initialize Bootstrap 3 and basic Layouts';

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
        
        $path = dirname(dirname(dirname(dirname(__FILE__)))).'/';
        $publicpath = $path.$this->ask("Path to public folder --> {$path}");
        $confirm = $this->confirm("Is {$publicpath} your public path? [yes|no]");
          
            if($confirm =='yes')
            {
                //append route 
                $routepath = getcwd().'/app/routes.php';
                $routedata = "Route::get('/', 'HomeController@getIndex');";
                File::append($routepath, "\n".$routedata); 
                
                //install homecontroller
                $controllerpath = getcwd().'/app/controllers/HomeController.php';
                $controllerdata = File::get(getcwd().'/app/commands/ScaffoldTemplates/HomeControllerTemplate.txt');
                File::put($controllerpath, $controllerdata); 

                //install layouts view
                $destinationlayoutspath = getcwd().'/app/views/layouts';
                $templatelayoutspath = getcwd().'/app/commands/ScaffoldBootstrap/layouts';
                File::makeDirectory($destinationlayoutspath, $mode = 0777, $recursive = false);
                File::copyDirectory($templatelayoutspath, $destinationlayoutspath, $options = null);
                
                //install home index view
                $viewpath = getcwd().'/app/views/home';
                $viewdata = "@extends('layouts.basepage')";
                File::makeDirectory($viewpath, $mode = 0777, $recursive = false); 	
                File::put("{$viewpath}/index.blade.php", $viewdata);
                
                //install bootstrap
                $destinationassetspath = $publicpath.'/assets';
                $templateassetspath = getcwd().'/app/commands/ScaffoldBootstrap/Bootstrap';
                File::makeDirectory($destinationassetspath, $mode = 0777, $recursive = false);
                File::copyDirectory($templateassetspath, $destinationassetspath, $options = null);
            }
            else
            {
                exit;
            }
    }

	protected function getArguments()
	{
		return array(
			array('publicpath', InputArgument::OPTIONAL, 'An example argument.'),
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
