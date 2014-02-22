<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Scaffold extends Command {
	
	private $allowedType= '/^([a-z_\.]+):((integer|bigInteger|date|dateTime|timestamp|boolean|time|text|binary)$|((?<=)string:[0-9]{1,4}$))/';
	
	private $adminpathlower=null;
	
	private $adminpathupper =null;
	
	private $adminnameupper =null;
    
	private $admintoken = 0;
	
	protected $name = 'scaffold:full';
	
	protected $description = 'Create a full scaffolding.';
	
	public function __construct()
	{

        parent::__construct();
        
	}

    public function fire()
	{
		
	//ADMIN MODE
		$admin = $this->option('admin');
		$adminpathlower = $this->adminpathlower;
		$adminpathupper = $this->adminpathupper;
		$adminnameupper = $this->adminnameupper;
		$admintemplates = null;
		$admintoken = $this->admintoken;
		
		if ($admin =='admin'){
			
			$this->info("\nAdmin Mode\nThis will route the scaffold to the admin section of your app.\nYou must have ran scaffold:admin first!");
			$adminpathlower = '/admin';
			$adminpathupper = '/Admin';
			$adminnameupper ='Admin';
			$admintemplates = '/AdminTemplates';
			$admintoken = 1;}
	
    
	//INTRODUCTION 	 
	
	$this->info("\nScaffold Generator. Press Ctrl^C to Quit at any time.\nYou should run scaffold:bootstrap to properly view the pages.\n");
	

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
		
		$this->info("\nName: Must be in lowercase, and spaces must be separated with an underscore\n\nType: Supported types are: [string: integer: bigInteger: date: dateTime: timestamp: boolean: time: softDeletes: text: binary:](note: Primary autoincrementing 'id', 'softDeletes()', and 'timestamps()' will be added automatically)\n\nLength: A numerical length is required only for string:\n\nFormat example: 'title:string:255'\n\t\t'page_views:integer'\n");
		
            $i=0;
			$datatwo=array();
			$datathree=array();
			$fielddata=array();
			$sortedfielddata= array();
			$allowedType = $this ->allowedType;
			$dummystring=array('string' =>array(array('','','',)));
			
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
				$sortedfielddata = $sortedfielddata + $dummystring;

		//// twofieldmigration data 
				$twofielddata = array_chunk(array_flatten(array_except($sortedfielddata, array('string'))),2);
				foreach ($twofielddata as $key=>$value){
					$datatwo[] = '$table->'.$value[1]."('".$value[0]."');";}
				
				$twofieldmigrationrow = implode("\n\t\t\t",$datatwo);	
			
		//// threefieldmigration data 
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

				
	// BLADE TEXT CONVERTOR
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
				
	// CONTROLLER, ROUTE, MODEL, VIEW SECTION
		$path = getcwd();
		$templatepath = 'app/commands/ScaffoldTemplates';
		
			$controllerpath = "{$path}/app/controllers{$adminpathupper}/{$adminnameupper}{$nameupperplural}Controller.php";
			$controllerdata = strtr(File::get("{$path}/{$templatepath}{$admintemplates}/{$adminnameupper}ControllerTemplate.txt"),$bladetext);
			
			$routepath = "{$path}/app/routes.php";
			$routedata = strtr(File::get("{$path}/{$templatepath}{$admintemplates}/{$adminnameupper}RouteTemplate.txt"),$bladetext);
			
			$modelpath = "{$path}/app/models/{$nameupper}.php";
			$modeldata = File::get("{$path}/{$templatepath}/ModelTemplate.txt"); $modeldata = strtr($modeldata,$bladetext);
			
			$viewpath = "{$path}/app/views/{$nameplural}";
			$viewdata = array(
				'index'	=> strtr(File::get("{$path}/{$templatepath}/ViewIndexTemplate.txt"), $bladetext),
				'page' 	=> strtr(File::get("{$path}/{$templatepath}/ViewPageTemplate.txt"), $bladetext),
				'create'=> strtr(File::get("{$path}/{$templatepath}{$admintemplates}/{$adminnameupper}ViewCreateTemplate.txt"), $bladetext),
				'delete'=> strtr(File::get("{$path}/{$templatepath}{$admintemplates}/{$adminnameupper}ViewDeleteTemplate.txt"), $bladetext),
				'edit'	=> strtr(File::get("{$path}/{$templatepath}{$admintemplates}/{$adminnameupper}ViewEditTemplate.txt"), $bladetext));	
			

		// ControllerTemplate.txt, AdmincontrollerTemplate.txt
			File::put($controllerpath, $controllerdata);
			$this->info("created {$path}/app/controllers/{$adminnameupper}{$nameupperplural}Controller.php");
		
		// RouteTemplate.txt, AdminRouteTemplate.txt
			File::append($routepath, "\n".$routedata); 
			$this->info("updated {$path}/app/routes.php");
		
		// ModelTemplate.txt
			File::put($modelpath, $modeldata);	
			$this->info("created {$path}/app/models/{$nameupper}.php");

		// Directory apps/views/nameplural/
			File::makeDirectory($viewpath, $mode = 0777, $recursive = false);
			$this->info("created directory {$path}/app/views/{$nameplural}");	
		
		// ViewIndexTemplate.txt 
			File::put("{$viewpath}/index.blade.php", $viewdata['index']); 	
			$this->info("created {$viewpath}/index.blade.php");
		
		// ViewPageTemplate.txt
			File::put("{$viewpath}/page.blade.php", $viewdata['page']); 		
			$this->info("created {$viewpath}/page.blade.php");			
			
			
			if($admintoken == 1){
				$adminpath = "{$path}/app/views/admin/{$nameplural}";
		
		// Directory app/views/admin/nameplural		
				File::makeDirectory($adminpath, $mode = 0777, $recursive = false);
				
				$adminviewindex= strtr(File::get("{$path}/{$templatepath}{$admintemplates}/{$adminnameupper}ViewIndexTemplate.txt"), $bladetext);
				$adminroutedata = strtr(File::get("{$path}/{$templatepath}{$admintemplates}/Non{$adminnameupper}RouteTemplate.txt"),$bladetext);
				$nonadmincontrollerpath = "{$path}/app/controllers/{$nameupperplural}Controller.php";
				$nonadmincontrollerdata = strtr(File::get("{$path}/{$templatepath}{$admintemplates}/Non{$adminnameupper}ControllerTemplate.txt"),$bladetext);
		
		// NonAdminControllertemplate.txt
				File::put($nonadmincontrollerpath, $nonadmincontrollerdata);
				$this->info("created {$path}/app/controllers/{$adminnameupper}{$nameupperplural}Controller.php");
				
		// NonAdminRouteTemplate.txt
				File::append($routepath, "\n".$adminroutedata); 
				$this->info("updated {$path}/app/routes.php");
		
		// AdminViewIndexTemplate.txt
				File::put("{$adminpath}/index.blade.php", $adminviewindex);
				$this->info("created {$adminpath}/index.blade.php");
		
		// AdminViewCreateTemplate.txt		
				File::put("{$adminpath}/create.blade.php", $viewdata['create']);	 	
				$this->info("created {$adminpath}/create.blade.php");	
		
		// AdminViewDeleteTemplate.txt
				File::put("{$adminpath}/delete.blade.php", $viewdata['delete']); 	
				$this->info("created {$adminpath}/delete.blade.php");
		
		// AdminViewEditTemplate.txt
				File::put("{$adminpath}/edit.blade.php", $viewdata['edit']); 		
				$this->info("created {$adminpath}/edit.blade.php");
			
				}else{
		
		// ViewCreateTemplate.txt
				File::put("{$viewpath}/create.blade.php", $viewdata['create']);	 	
				$this->info("created {$viewpath}/create.blade.php");	
		
		// ViewDeleteTemplate.txt
				File::put("{$viewpath}/delete.blade.php", $viewdata['delete']); 	
				$this->info("created {$viewpath}/delete.blade.php");
		
		// ViewEditTemplate.txt
				File::put("{$viewpath}/edit.blade.php", $viewdata['edit']); 		
				$this->info("created {$viewpath}/edit.blade.php");
				}


	// MIGRATION AND SEEDER SECTION
		$migrationSSHcommand = "{$path}/artisan migrate:make create_{$nameplural}_table";
		
		//app/database/migrations/*_create_nameplural_table.php
			SSH::run($migrationSSHcommand);
			
			$migrationpath = "{$path}/app/database/migrations/*_create_{$nameplural}_table.php";
			
			$migrationfile = implode(" ",File::glob($migrationpath));
			
			$fileline = array(
				'migrationbottom' 	=> '24',
				'migrationtop' 		=> '14',
				'seed' 				=> '12');


			$migrationbottomlines = file( $migrationfile , FILE_IGNORE_NEW_LINES );
			$migrationbottomlines[$fileline['migrationbottom']] =strtr(File::get("{$path}/{$templatepath}/MigrationBottomTemplate.txt"), $bladetext);
			
			$migrationtoplines = file( $migrationfile , FILE_IGNORE_NEW_LINES );
			$migrationtoplines[$fileline['migrationtop']] = strtr(File::get("{$path}/{$templatepath}/MigrationTopTemplate.txt"), $bladetext);
			
			$databaseseederpath = "{$path}/app/database/seeds/DatabaseSeeder.php";
			$databaseseedlines = file( $databaseseederpath, FILE_IGNORE_NEW_LINES );
			$databaseseedlines[$fileline['seed']] = strtr(File::get("{$path}/{$templatepath}/DatabaseSeederTemplate.txt"), $bladetext);		
			
			$seederpath = "{$path}/app/database/seeds/{$nameupperplural}TableSeeder.php";
			$seedercontent[] = strtr(File::get("{$path}/{$templatepath}/TableSeederTemplate.txt"), $bladetext);
			
		
		// MigrationBottomTemplate.txt
			File::put( $migrationfile , implode( "\n", $migrationbottomlines ));
		
		// MigrationTopTemplate.txt
			File::put( $migrationfile , implode( "\n", $migrationtoplines ));

		// DatabaseSeederTemplate.txt
			File::put( $databaseseederpath , implode( "\n", $databaseseedlines));
			$this->info("updated {$path}/app/database/seeds/DatabaseSeeder.php");
		
		// TableSeederTemplate.txt
			File::put( $seederpath, implode("\n", $seedercontent));
			$this->info("created {$path}/app/database/seeds/{$nameupperplural}TableSeeder.php");
	}
		
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::OPTIONAL, 'Create a full scaffold.'),
			//array('admin', InputArgument::OPTIONAL, 'Create a full scaffold.'),
		);
	}

	protected function getOptions()
	{
		return array(
			array('admin', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}