<?php

	/**
	* Controller class of the ADOdb_QuickForm system.
	*
	* Copyright (c) 2004 Explorer Fund Advisors, LLC
	* All Rights Reserved.
	*
	*
	* @author Justin Cooper <justin@braverock.com>
	* @todo
	*
	* $Id: ADOdb_QuickForm_Controller.php,v 1.1 2005/01/10 14:34:14 daturaarutad Exp $
	*/


	/**
	*
	* Controller class of the ADOdb_QuickForm system.
	*
	* Typical Usage: 
	*
	* require_once "classes/QuickForm/ADOdb_QuickForm.php";
	* 
	* $model = new ADOdb_QuickForm_Model();
	* $model->ReadSchemaFromDB($con, 'appointments');
	* 
	* $model->SetDisplayNames(array('patient_id' => 'Patient Name', 
    *                         	  'appointment_name' => 'Appointment Name'
    *                         	  'appointment_time' => 'Time'));
	* 
	* $model->SetForeignKeyField('patient_id', 'Patient', 'patients', 'patient_id', 'patient_name');
	* 
	* $view = new ADOdb_QuickForm_View($con, 'Patient');
	* $view->SetReturnButton('Return to List', $return_url);
	* 
	* $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
	* $form_html = $controller->ProcessAndRenderForm();
	* 
	* @package ADOdb_QuickForm
	* @todo Perhaps split ProcessAndRenderForm into ProcessForm and RenderForm if there
	* is ever a need for it.
	*/
	class ADOdb_QuickForm_Controller {

		var $Models = array();
		var $View = null;

		/**
		* You must pass in at least one model and only one view to initialize the Controller.  
		*
		*/
		function ADOdb_QuickForm_Controller($models, $view) { 
			$this->Models = $models;
			$this->View = $view;
		}


		/**
		* Process and Render the form
		*
		* This function will look at the CGI var form_action and determine if the Model should
		* be updated from the CGI vars and written, or read, or created in the database.
		* 
		*/
		function ProcessAndRenderForm($show_submit = true) {
			getGlobalVar($form_action, 'form_action');

			//echo "Controller's form_action: $form_action<br/>";

			switch($form_action) {
	  			case 'view':
					for($j=0; $j<count($this->Models); $j++) {

						$object_id = $this->View->GetPrimaryKeyValue($this->Models[$j]);
						$this->Models[$j]->Read($object_id);
						$this->View->AddModel($this->Models[$j]);
					}
					$this->View->InitForm();
					
					$this->View->SetNextFormAction(null);
					$this->View->SetReadOnly();
	    			return $this->View->GetForm($form_action, $show_submit);
		
	    		break;
		
	  			case 'edit':
					for($j=0; $j<count($this->Models); $j++) {
						$object_id = $this->View->GetPrimaryKeyValue($this->Models[$j]);

						$this->Models[$j]->Read($object_id);
						$this->View->AddModel($this->Models[$j]);
					}
					$this->View->InitForm();

	    			$this->View->SetNextFormAction('update');
	    			return $this->View->GetForm($form_action, $show_submit);
		
	    		break;
		
	  			case 'update':
					for($j=0; $j<count($this->Models); $j++) {
						$object_id = $this->View->GetPrimaryKeyValue($this->Models[$j]);
						$this->Models[$j]->Read($object_id);
						$this->View->AddModel($this->Models[$j]);
					}
					$this->View->InitForm();

	    			$this->View->UpdateModelsFromView();
	

					for($j=0; $j<count($this->Models); $j++) {
						$object_id = $this->View->GetPrimaryKeyValue($this->Models[$j]);
						$this->Models[$j]->Write($object_id);
					}

					$this->View->SetConstants();
		
	    			$this->View->SetNextFormAction();
					$this->View->SetReadOnly();
	    			return $this->View->GetForm($form_action, $show_submit);
		
	    		break;

	  			case 'create':

					for($j=0; $j<count($this->Models); $j++) {
						$this->View->AddModel($this->Models[$j]);
					}
					// add elements to the view's QF object
					$this->View->InitForm();
					
					// Set the model values from the <form> values
	    			$this->View->UpdateModelsFromView();

					for($j=0; $j<count($this->Models); $j++) {
						$model_id = $this->Models[$j]->Create();

						// store the primary key
						if($model_id) {
							$values = $this->Models[$j]->GetValues();
							$values[$this->Models[$j]->GetPrimaryKeyName()] = $model_id;
							$this->Models[$j]->SetValues($values);
						}
					}
					$this->View->SetConstants();

		
	    			$this->View->SetNextFormAction();
					$this->View->SetReadOnly();
	    			return $this->View->GetForm($form_action, $show_submit);

	    		break;


	   			case 'new':
				default:

					for($j=0; $j<count($this->Models); $j++) {
						$this->View->AddModel($this->Models[$j]);
					}
					$this->View->InitForm();
	    			$this->View->SetNextFormAction('create');
	    			return $this->View->GetForm($form_action, $show_submit);
		
	    		break;
		
				}
			}
	} // ADOdb_QuickForm_Controller class


?>
