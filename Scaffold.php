<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Scaffold extends Command {
	
	private $allowedType= '/^([a-z_\.]+):((integer|bigInteger|date|dateTime|timestamp|boolean|time|text|binary)$|((?<=)string:[0-9]{1,4}$))/';

    protected $name = 'scaffold:full';
	
	protected $description = 'Create a full scaffolding.';
	
	public function __construct()
	{

        parent::__construct();
        
	}

    public function fire()
	{
        $i=0;$fielddata=array();$sortedfielddata= array();$allowedType = $this ->allowedType;
        $datatwo=array();$datathree=array();$meow=array('string' =>array(array('','','',)));
		
    //INTRODUCTION 	 
		$this->info('');
		$this->info('Scaffold Generator. Press \'Ctrl C\' to Quit at any time');
		$this->info('');


	 //INPUTS
        
        //name?
            $name = $this->ask('Name = ');
            
			if (!preg_match ('/^[a-zA-Z]+$/', $name))
			{
				$this->error('Invalid Input!');
				exit;}
            
			$name = Str::lower($name);
            $name = Str::singular($name);
            $nameupper = Str::title($name);
            $nameplural = Str::plural($name);
            $nameupperplural = Str::plural($nameupper);
			
        //number of rows?
            $fieldamount = $this->ask('Number of Rows = ');
			
            if (!preg_match ('/^[0-9]+$/', $fieldamount))
			{
				$this->error('Numbers only!');
				exit;}
        
        //row name:type:length?
            $this->info('');
			$this->info('Name: Must be in lowercase, and spaces must be separated with an underscore');
			$this->info('');
			$this->info("Type: Supported types are: [string: integer: bigInteger: date: dateTime: timestamp: boolean: time: softDeletes: text: binary:](note: Primary autoincrementing 'id', 'softDeletes()', and 'timestamps()' will be added automatically)");
			$this->info('');
			$this->info("Length: A numerical length is required only for string:");
			$this->info('');
			$this->info("Format example: 'title:string:255'");
			$this->info("\t\t'page_views:integer'");
			$this->info('');
            
			while($i++ < $fieldamount){
				$fielddata[] = $this->ask("[Row {$i}] Name:Type:Length = ");
				if ($fielddata!=preg_grep ($allowedType, $fielddata)){
					$this->error('Invalid Input!');
					exit;}}
					
            foreach ($fielddata as $key => $val){
				foreach (explode(':', $val) as $part){
					$newarray[$key][] = $part;}}           
					
			foreach($newarray as $el){
				$sortedfielddata[$el[1]][] = $el;}
				$sortedfielddata=$sortedfielddata+$meow;

		////twofieldmigration data 
				$twofielddata = array_chunk(array_flatten(array_except($sortedfielddata, array('string'))),2);
				foreach ($twofielddata as $key=>$value){
					$datatwo[] = '$table->'.$value[1]."('".$value[0]."');";}
				$twofieldmigrationrow = implode("\n\t\t\t",$datatwo);	
			
		////threefieldmigration data 
				//$threefielddata=array_chunk(array_flatten($sortedfielddata['string']),3);
				$threefielddata=array_filter(array_map('array_filter', array_chunk(array_flatten($sortedfielddata['string']),3)));
				foreach ($threefielddata as $key=>$value){
					$datathree[] = '$table->'.$value[1]."('".$value[0]."'," .$value[2].');';}
				$threefieldmigrationrow = implode("\n\t\t\t",$datathree);
			
			
		//// seeder and view data
				
				$fielddata=array_filter(array_map('array_filter', array_merge($threefielddata, $twofielddata)));
				foreach ($fielddata as $key=>$value){
					$quack[] = "'".$value[0]."'=>'".$value[1]."',";
					$templaterowdata[] = '<td>{{$'.$name.'->'.$value[0].'}}</td>';
					$templatenamedata[] = '<td><b>'.$value[0].'</b></td>';}
		
				$quackers = rtrim(implode("\n\t\t\t",$quack),',');		
				$templatenamedata = implode("\n\t",$templatenamedata);
				$templaterowdata = implode("\n\t",$templaterowdata);
			
/* 			print_r($sortedfielddata);
			print_r($twofielddata);
			print_r($threefielddata);
			print_r($fielddata);
			print_r($twofieldmigrationrow);
			print_r($threefieldmigrationrow);
			print_r($quackers);
			print_r($templatenamedata);
			print_r($templaterowdata); */

	
			
	//BLADE CONVERTOR
        $bladetext = array(
			'{{$name}}' 					=> "{$name}",
			'{{$nameupper}}' 				=> "{$nameupper}",
			'{{$nameplural}}' 				=> "{$nameplural}",
			'{{$nameupperplural}}' 			=> "{$nameupperplural}",
			'{{$templaterowdata}}' 			=> "{$templaterowdata}",
            '{{$templatenamedata}}' 		=> "{$templatenamedata}",
            '{{$threefieldmigrationrow}}'	=> "{$threefieldmigrationrow}",
			'{{$twofieldmigrationrow}}'		=> "{$twofieldmigrationrow}",
			'{{$quackers}}' 				=> "{$quackers}");
	
	//DIRECTORY / FILE PATHS / CONTENT / INPUT
		$path = getcwd();
		$templatepath = 'app/commands/ScaffoldTemplates';
		$migrationSSHcommand = "{$path}/artisan migrate:make create_{$nameplural}_table";

		// controller
			$controllerpath = "{$path}/app/controllers/{$nameupperplural}Controller.php";
			$controllerdata = strtr(File::get("{$path}/{$templatepath}/ControllerTemplate.txt"),$bladetext);
			File::put($controllerpath, $controllerdata); 
			$this->info("created {$path}/app/controllers/{$nameupperplural}Controller.php");
		
		// route 
			$routepath = "{$path}/app/routes.php";
			$routedata = strtr(File::get("{$path}/{$templatepath}/RouteTemplate.txt"),$bladetext);
			File::append($routepath, "\n".$routedata); 
			$this->info("updated {$path}/app/routes.php");
		
		// view 
			$viewpath = "{$path}/app/views/{$nameplural}";
			$viewdata = array(
				'index'	=> strtr(File::get("{$path}/{$templatepath}/ViewIndexTemplate.txt"), $bladetext),
				'page' 	=> strtr(File::get("{$path}/{$templatepath}/ViewPageTemplate.txt"), $bladetext),
				'create'=> strtr(File::get("{$path}/{$templatepath}/ViewCreateTemplate.txt"), $bladetext),
				'delete'=> strtr(File::get("{$path}/{$templatepath}/ViewDeleteTemplate.txt"), $bladetext),
				'edit'	=> strtr(File::get("{$path}/{$templatepath}/ViewEditTemplate.txt"), $bladetext));		
			File::makeDirectory($viewpath, $mode = 0777, $recursive = false); 	
			$this->info("created directory {$path}/app/views/{$nameplural}");
			File::put("{$viewpath}/index.blade.php", $viewdata['index']); 		
			$this->info("created {$viewpath}/index.blade.php");
			File::put("{$viewpath}/page.blade.php", $viewdata['page']); 		
			$this->info("created {$viewpath}/page.blade.php");
			File::put("{$viewpath}/create.blade.php", $viewdata['create']);	 	
			$this->info("created {$viewpath}/create.blade.php");
			File::put("{$viewpath}/delete.blade.php", $viewdata['delete']); 	
			$this->info("created {$viewpath}/delete.blade.php");
			File::put("{$viewpath}/edit.blade.php", $viewdata['edit']); 		
			$this->info("created {$viewpath}/edit.blade.php");
			
		// model
			$modelpath = "{$path}/app/models/{$nameupper}.php";
			$modeldata = File::get("{$path}/{$templatepath}/ModelTemplate.txt"); $modeldata = strtr($modeldata,$bladetext);
			File::put($modelpath, $modeldata);	
			$this->info("created {$path}/app/models/{$nameupper}.php");
		
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
			$this->info("updated {$path}/app/database/seeds/DatabaseSeeder.php");
		// seed file
			$seederpath = "{$path}/app/database/seeds/{$nameupperplural}TableSeeder.php";
			$seedercontent[] = strtr(File::get("{$path}/{$templatepath}/TableSeederTemplate.txt"), $bladetext);
			File::put( $seederpath, implode("\n", $seedercontent));
			$this->info("created {$path}/app/database/seeds/{$nameupperplural}TableSeeder.php");
	}
		
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::OPTIONAL, 'Create a full scaffold.'),
			array('namespace', InputArgument::OPTIONAL, 'Create a full scaffold.'),
		);
	}

	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}