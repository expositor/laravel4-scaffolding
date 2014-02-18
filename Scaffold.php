<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Scaffold extends Command {
	
	protected $name = 'scaffold:full';
	
	protected $description = 'Create a full scaffolding.';
	
	public function __construct()
	{
		parent::__construct();
	}

	public function fire()
	{
		
	//INTRODUCTION 	 
		$this->info('');
		$this->info('Scaffold Generator. Press \'Ctrl C\' to Quit');
		$this->info('');
		

	 //QUESTIONS
		$name = $this->ask('Name = ');
		$fieldamount = $this->ask('Number of Rows = ');
		$i=0; $fielddata=array();
		while($i++ < $fieldamount){$fielddata[] = $this->ask("[Row {$i}] Name:Type:Length = ");}

	//INPUT VALIDATOR
	 	
	
	//NAME CONVERTOR
		$name = Str::lower($name);
		$name = Str::singular($name);
		$nameupper = Str::title($name);
		$nameplural = Str::plural($name);
		$nameupperplural = Str::plural($nameupper);
		
	//ROW CONVERTOR
		$foo = implode(":", $fielddata);
		$bar = explode(":",$foo);
		$baz = array_chunk($bar,3);
		foreach ($baz as $value){
			$qux[] = '$table->'.$value[1]."('".$value[0]."'," .$value[2].');';
			$quack[] = "'".$value[0]."'=>'".$value[1]."',";
			
			}
		
		// migration row
			$quux = implode("\n\t",$qux);
		
		// seeder row
			$quackers = rtrim(implode("\n\t",$quack),',');		
		
		// view index
			
	//BLADE CONVERTOR
		$bladetext = array(
			'{{$name}}' 			=> "{$name}",
			'{{$nameupper}}' 		=> "{$nameupper}",
			'{{$nameplural}}' 		=> "{$nameplural}",
			'{{$nameupperplural}}' 	=> "{$nameupperplural}",
			'{{$quux}}' 			=> "{$quux}",
			'{{$quackers}}' 		=> "{$quackers}");

	
	//DIRECTORY / FILE PATHS / CONTENT / INPUT
		$path = getcwd();
		$templatepath = 'app/commands/ScaffoldTemplates';
		$migrationSSHcommand = "{$path}/artisan migrate:make create_{$nameplural}_table";

		// controller
			$controllerpath = "{$path}/app/controllers/{$nameupperplural}Controller.php";
			$controllerdata = strtr(File::get("{$path}/{$templatepath}/ControllerTemplate.txt"),$bladetext);
			File::put($controllerpath, $controllerdata);
		
		// route 
			$routepath = "{$path}/app/routes.php";
			$routedata = strtr(File::get("{$path}/{$templatepath}/RouteTemplate.txt"),$bladetext);
			File::append($routepath, "\n".$routedata);
		
		// view 
			$viewpath = "{$path}/app/views/{$nameplural}";
			$viewdata = array(
				'index'	=> strtr(File::get("{$path}/{$templatepath}/ViewIndexTemplate.txt"), $bladetext),
				'page' 	=> strtr(File::get("{$path}/{$templatepath}/ViewPageTemplate.txt"), $bladetext),
				'create'=> strtr(File::get("{$path}/{$templatepath}/ViewCreateTemplate.txt"), $bladetext),
				'delete'=> strtr(File::get("{$path}/{$templatepath}/ViewDeleteTemplate.txt"), $bladetext),
				'edit'	=> strtr(File::get("{$path}/{$templatepath}/ViewEditTemplate.txt"), $bladetext));		
			File::makeDirectory($viewpath, $mode = 0777, $recursive = false);
			File::put("{$viewpath}/index.blade.php", $viewdata['index']);
			File::put("{$viewpath}/page.blade.php", $viewdata['page']);
			File::put("{$viewpath}/create.blade.php", $viewdata['create']);
			File::put("{$viewpath}/delete.blade.php", $viewdata['delete']);
			File::put("{$viewpath}/edit.blade.php", $viewdata['edit']);
			
		// model
			$modelpath = "{$path}/app/models/{$nameupper}.php";
			$modeldata = File::get("{$path}/{$templatepath}/ModelTemplate.txt"); $modeldata = strtr($modeldata,$bladetext);
			File::put($modelpath, $modeldata);	
		
		// migration and seeder
			SSH::run($migrationSSHcommand);
			$migrationpath = "{$path}/app/database/migrations/*_create_{$nameplural}_table.php";
			$migrationfile = implode(" ",File::glob($migrationpath));
			$fileline = array(
				'migrationbottom' 	=> '24',
				'migrationtop' 		=> '14',
				'seed' 				=> '12');
		
			$migrationbottomlines = file( $migrationfile , FILE_IGNORE_NEW_LINES );
			$migrationbottomlines[$fileline['migrationbottom']] =strtr(File::get("{$path}/{$templatepath}/MigrationBottomTemplate.txt"), $bladetext);
			File::put( $migrationfile , implode( "\n", $migrationbottomlines ));
			
			$migrationtoplines = file( $migrationfile , FILE_IGNORE_NEW_LINES );
			$migrationtoplines[$fileline['migrationtop']] = strtr(File::get("{$path}/{$templatepath}/MigrationTopTemplate.txt"), $bladetext);
			File::put( $migrationfile , implode( "\n", $migrationtoplines ));
			
		// database seeder
			$databaseseederpath = "{$path}/app/database/seeds/DatabaseSeeder.php";
			$databaseseedlines = file( $databaseseederpath, FILE_IGNORE_NEW_LINES );
			$databaseseedlines[$fileline['seed']] = strtr(File::get("{$path}/{$templatepath}/DatabaseSeederTemplate.txt"), $bladetext);
			File::put( $databaseseederpath , implode( "\n", $databaseseedlines));
		
		// seed file
			$seederpath = "{$path}/app/database/seeds/{$nameupperplural}TableSeeder.php";
			$seedercontent[] = strtr(File::get("{$path}/{$templatepath}/TableSeederTemplate.txt"), $bladetext);
			File::put( $seederpath, implode("\n", $seedercontent));
	}
		
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::OPTIONAL, 'Create a full scaffold.'),
			array('namespace', InputArgument::OPTIONAL, 'Create a full scaffold.'),


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