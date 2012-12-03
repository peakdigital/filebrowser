<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class contentExtensionFilebrowserDownload extends AdministrationPage{

		function __construct(){
			parent::__construct();
			
			$FileManager = Symphony::ExtensionManager()->create('filebrowser');
			
			$file = $_REQUEST['file'];
			
			$FileManager->download($file);
			
			exit();
		}
		
	}
?>