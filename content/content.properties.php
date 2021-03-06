<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class contentExtensionFileBrowserProperties extends AdministrationPage{

		private $_FileManager;

		function __construct(){
			parent::__construct();
			
			$this->_FileManager =Symphony::ExtensionManager()->create('filebrowser');
			
			$this->setTitle('Symphony &ndash; File Browser &ndash; ' . str_replace(DOCROOT . $this->_FileManager->getStartLocation(), NULL, $_GET['file']));
		}
		
		function action(){
			
			$FileManager = Symphony::ExtensionManager()->create('filebrowser');
			$file = new File(DOCROOT . $FileManager->getStartLocation() . $_GET['file']);
			
			if(isset($_POST['action']['save'])){
				$fields = $_POST['fields'];
				
				$file->setName($fields['name']);
				
				if(isset($fields['contents'])) $file->setContents($fields['contents']);
				
				$file->setPermissions($fields['permissions']);
				
				$relpath = str_replace(DOCROOT . $FileManager->getStartLocation(), NULL, dirname($_GET['file']));
				
				if($file->isWritable())
					redirect($FileManager->baseURL() . 'properties/?file=' . rtrim(dirname($_GET['file']), '/') . '/' . $file->name() . '&result=saved');
				
				else redirect($FileManager->baseURL() . 'browse/' . $relpath);
				
			}
			
			elseif(isset($_POST['action']['delete'])){

				General::deleteFile($file->path() . '/' . $file->name());
				
				$relpath = str_replace(DOCROOT . $FileManager->getStartLocation(), NULL, dirname($_GET['file']));
				
				redirect($FileManager->baseURL() . 'browse/' . $relpath);
				
			}
		}
		
		function view(){
			
			$this->Form->setAttribute('action', extension_filebrowser::baseURL() . 'properties/?file=' . $_GET['file']);
			
			$file = new File(DOCROOT . $this->_FileManager->getStartLocation() . $_GET['file']);

			$FileManager = Symphony::ExtensionManager()->create('filebrowser');

			$formHasErrors = (is_array($this->_errors) && !empty($this->_errors));
			
			if($formHasErrors) $this->pageAlert('An error occurred while processing this form. <a href="#error">See below for details.</a>', AdministrationPage::PAGE_ALERT_ERROR);

			if(isset($_GET['result'])){
				switch($_GET['result']){
					
					case 'saved':
					
						$this->pageAlert(
							__(
								'%s updated successfully',
								array(($file->isDir() ? 'Folder' : 'File'))
							),
							Alert::SUCCESS
						);
						
						break;
					
				}
			}
			
			$this->setPageType('form');	
			
			$path = extension_filebrowser::baseURL() . 'browse/';
			$breadcrumb = '';
			$pathelements = explode('/', $_GET['file']);
			foreach($pathelements as $element) {
				if($element != '') {
					$path .= $element . '/';
					$breadcrumb .= ' / ' . ($element == end($pathelements) ? $element : Widget::Anchor($element, $path)->generate());
				}
			}
			
			$this->appendSubheading(trim($FileManager->getStartLocationLink(), '/') . $breadcrumb);
			
			$fields = array();
			
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Essentials'));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');
		
			$label = Widget::Label('Name');
			$label->appendChild(Widget::Input('fields[name]', General::sanitize($file->name())));
		
			if(isset($this->_errors['name'])) $div->appendChild(Widget::wrapFormElementWithError($label, $this->_errors['name']));
			else $div->appendChild($label);

			$label = Widget::Label('Permissions');
			$label->appendChild(Widget::Input('fields[permissions]', General::sanitize($file->permissions())));
		
			if(isset($this->_errors['permissions'])) $div->appendChild(Widget::wrapFormElementWithError($label, $this->_errors['permissions']));
			else $div->appendChild($label);	
			
			$fieldset->appendChild($div);
			
			$this->Form->appendChild($fieldset);
	
			
			if(!$file->isDir() && in_array(File::fileType($file->name()), array(File::CODE, File::DOC))){
		
				$fieldset = new XMLElement('fieldset');
				$fieldset->setAttribute('class', 'settings');
				$fieldset->appendChild(new XMLElement('legend', 'Editor'));
				
				$label = Widget::Label('Contents');
				$textarea = Widget::Textarea('fields[contents]',25,50,General::sanitize($file->contents()),array('class' => 'code'));
				$label->appendChild($textarea);
		
				if(isset($this->_errors['contents'])) $fieldset->appendChild(Widget::wrapFormElementWithError($label, $this->_errors['contents']));
				else $fieldset->appendChild($label);
		
				$this->Form->appendChild($fieldset);
		
			}
			
			if(!$file->isDir() && File::fileType($file->name()) == File::IMAGE){
			
				$fieldset = new XMLElement('fieldset');
				$fieldset->setAttribute('class', 'settings');
				$fieldset->appendChild(new XMLElement('legend', 'Preview'));
			
				$img = new XMLElement('img');
				$img->setAttribute('src', URL . $FileManager->getStartLocation() . $_GET['file']);
				$img->setAttribute('alt', $file->name());
				$fieldset->appendChild($img);
		
				$this->Form->appendChild($fieldset);
		
			}
			
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			
			if(is_writeable(DOCROOT . $this->_FileManager->getStartLocation() . $_GET['file'])) {
			
				$div->appendChild(Widget::Input('action[save]', 'Save Changes', 'submit', array('accesskey' => 's')));
			
				$button = new XMLElement('button', 'Delete');
				$button->setAttributeArray(array('name' => 'action[delete]', 'class' => 'confirm delete', 'title' => 'Delete this ' . ($file->isDir() ? 'Folder' : 'File')));
				$div->appendChild($button);
			}
			else {
				$notice = new XMLElement('p','The server does not have permission to edit this file.');
				$notice->setAttribute('class','inactive');
				$div->appendChild($notice);
			}
			
			$this->Form->appendChild($div);			

		}
	}
	
?>
