<?php
/**
 * View class of the ADOdb_QuickForm system.  
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 *
 * @author Justin Cooper <justin@braverock.com>
 * @todo
 *
 * $Id: ADOdb_QuickForm.php,v 1.1 2005/01/10 14:34:14 daturaarutad Exp $
 */



	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/Default.php';
	require_once "ADOdb_QuickForm_Controller.php";
	require_once "ADOdb_QuickForm_Model.php";

	PEAR::setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_WARNING);

	/**
	* ADOdb_QuickForm_View Class 
	*
	* View class of the ADOdb_QuickForm system.  
	*
	* This class creates the <form> and the HTML widgets based on the Model object.  
	*
 	* @package ADOdb_QuickForm
	* @todo:
	* Big ones:
	* -get default value from meta db data and setDefault();
	* -get size from meta db data, set a validation rule
	* -implement displayOrder
	* -allow user to specify different UPDATE types for GetUpdateSQL
	*	
	*/
	class ADOdb_QuickForm_View {

		/** @var object The internal Pear HTML_QuickForm object */
	  	var $QF;
		/** @var object The ADOdb database handle */
	  	var $DBH;

		/** @var string Used to hold display names of DB fields */
		var $DisplayTitle;

		/** @var string Javascript code to output before and after the <form> */
		var $JSCodePost; 

		/** @var string Text to display for the return button */
		var $ReturnButtonCaption;
		/** @var string URL to set for the return button */
		var $ReturnButtonURL;

		/** @var array Array of QuickForm_Model objects */
		var $Models; 
		/** @var string value to set form_action to when the current form is rendered */
		var $next_form_action;

		/** @var string A template for the <form> tag.  see code for examples. */
		var $form_template;
		/** @var string A template for each element */
		var $element_template;

		/** @var string A template for each element */
		var $form_method;

		/** @var boolean Whether or not the model's tablename should be prepended to all elements for that model */
		var $prepend_tablename;


	/**
	* ADOdb_QuickForm_View Constructor
	* 
	* @param handle dbh ADOdb connection reference
	* @param string displayTitle used in the default template
	* @param string form_method HTML &lt;form&gt; method (get or post) remember to use POST if you're expecting file uploads
	* @param boolean Whether or not the model's tablename should be prepended to all elements for that model 
	* 
	*/
	function ADOdb_QuickForm_View($dbh, $displayTitle, $form_method = 'get', $prependTablename = false) {
		$this->DBH = $dbh;
		$this->DisplayTitle = $displayTitle;
		$this->PrependTablename = $prependTablename;
		$this->JSCodePost = array(); 

		$this->Models = array();

		$this->QF = new HTML_QuickForm($displayTitle, $form_method);


		$this->form_template	= "\n<form{attributes}>\n<table border=\"0\" class=\"widget\"><tr><td colspan=\"2\" class=\"widget_header\">" . $this->DisplayTitle . "</td> </tr>\n{content}\n</table>\n</form>";

		$this->element_template = "\n\t<tr>\n\t\t<td align=\"right\" valign=\"top\" class=\"widget_content\"><!-- BEGIN required --><span style=\"color: #ff0000\">*</span><!-- END required --><b>{label}</b></td>\n\t\t<td valign=\"top\" align=\"left\" class=\"widget_content_form_element\"><!-- BEGIN error --><span style=\"color: #ff0000\">{error}</span><br /><!-- END error -->\t{element}</td>\n\t</tr>";

		$this->form_method = $form_method;

		jscalendar_includes();
	}

	/**
	* Add a model to the View object
	*
	* @param object model ADOdb_Quickform_Model object to add 
	*/
	function AddModel(&$model) {
		$this->Models[] = &$model;
	}

	/**
	* Set the next action for the form.
	* Possible values are (view, edit, update, create, new)
	*
	* @param string next_form_action
	*/
	function SetNextFormAction($next_form_action = null) {
		$this->next_form_action = $next_form_action;
	}

	/**
	* Sets the template for the form.  
	* Default is "\n<form{attributes}>\n<table border=\"0\" class=\"widget\"><tr><td colspan=\"2\" class=\"widget_header\">" . $this->DisplayTitle . "</td> </tr>\n{content}\n</table>\n</form>"
	*
	* @param string template
	*/
	function SetFormTemplate($template) {
		$this->form_template = $template;
	}

	/**
	* Sets the element template for the form.  
	* Default is "\n\t<tr>\n\t\t<td align=\"right\" valign=\"top\" class=\"widget_content\"><!-- BEGIN required --><span style=\"color: #ff0000\">*</span><!-- END required --><b>{label}</b></td>\n\t\t<td valign=\"top\" align=\"left\" class=\"widget_content_form_element\"><!-- BEGIN error --><span style=\"color: #ff0000\">{error}</span><br /><!-- END error -->\t{element}</td>\n\t</tr>";
	*
	* @param string template
	*/
	function SetElementTemplate($template) {
		$this->element_template = $template;
	}

	/**
	* For internal use: quotes enum values before sending them to the database.
	* Note: if there are multiple models with the same field name things will get weird unless you use PrependTablename
	*/
	function GetSubmitValues() {

		$values = $this->QF->getSubmitValues();
		$my_values = array();
		foreach($values as $field_name => $v) {
			
			for($j=0; $j<count($this->Models); $j++) {
				if(false !== $this->Models[$j]->GetFieldIndex($field_name)) {
					$field_name = $this->GetOriginalFieldName($this->Models[$j]->GetTableName(), $field_name);
					break;
				}
			}
			$my_values[$field_name] = $v;
		}
		return $my_values;
	}

	/**
	* Sets the URL and Caption for the return button.
	* Maybe someday when there's more time we'll change this to AddLinkButton and allow multiple
	*
	* @param string caption Button text to display
	* @param string url URL to navigate to when button is clicked 
	*/
	function SetReturnButton($caption, $url) {
		$this->ReturnButtonCaption = $caption; 
		$this->ReturnButtonURL = $url; 
	}


	/**
	* Do this immediately after View->AddModels()
	*
	* Adds elements to the HTML_QuickForm object 
	* Sets the default values (not necessary?)
	*/
	function InitForm() {
		for($j=0; $j<count($this->Models); $j++) {
		// handle files first?
			$this->AddElementsToQuickForm($this->Models[$j]);
			$this->SetModelDefaults($j);
		}
	}

	/**
	* Renders the view.

	* @param string current_form_action Typically this is $GET['form_action'] (or POST)
	* @param boolean show_submit Whether or not to render the submit button (optional)
	* @todo make this granular! (return an array of widgets using SimpleHTML renderer) 
	* @return string HTML widgets
	*/
	function GetForm($current_form_action, $show_submit = true) {
		$renderer =& new HTML_QuickForm_Renderer_Default();

		$renderer->setFormTemplate($this->form_template);
		$renderer->setElementTemplate($this->element_template);

		if($show_submit) {
			$this->AddSubmitButtonToQuickForm($current_form_action);
		}

		$this->QF->accept($renderer);
		
		$output = $renderer->toHtml();

		foreach($this->JSCodePost as $jsItem) {
			$output .= $jsItem;
		}
		return $output;
	}

	/**
	* Set the QF object's values (these are overridden by GET/POST or SetConstant()
	* (if using PrependTablename, Model knows them as 'field', View as 'tablename_field')
	* @param integer j Model index
	*/
	function SetModelDefaults($j) {
		$values = $this->Models[$j]->GetValues();
		$tablename = $this->Models[$j]->GetTableName();

		$my_values = array();

		foreach($values as $k => $v) {
			$my_values[$this->GetPrependedFieldName($tablename, $k)] = $v;
		}
		$this->QF->setDefaults($my_values);
	}

	/**
	* Get the value for the Primary Key from a form for a model 
	* @param object model 
	*/
	function GetPrimaryKeyValue($model) {
		$tablename = $model->GetTableName();
 		getGlobalVar($object_id, $this->GetPrependedFieldName($tablename, $model->GetPrimaryKeyName()));
		return $object_id;
	}


	/**
	* function AddElementsToQuickForm
	* 
	* Adds elements to the internal PEAR QuickForm object based on an ADOdb_QuickForm_Model object
	* Not meant to be called externally
	*
	* @param string action current form action
	* 
	*/
	function AddElementsToQuickForm($model) {

		$form =& $this->QF;
		$tablename = $model->GetTableName();

	    // add the different elements based on their QF display type
	    //foreach($this->Schema->GetFields() as $field) {
	    foreach($model->GetFields() as $field) {

			$field_name = $this->GetPrependedFieldName($tablename,  $field['name']);

			// process foreign keys
			if($field['isForeignKey']) {

				$select = array();
				
				// add user specified keys first
				if($field['selectAdditionalValues']) {
					foreach($field['selectAdditionalValues'] as $option => $value) {
						$select[$option] = $value;
					}
				}

				// read db and add table lookup keys
				if($field['foreignTable']) {
			  		$sql="select  {$field['foreignKey']}, {$field['foreignFields']} from {$field['foreignTable']}";
                                        if ($field['orderBy']) { $sql .= " ORDER BY {$field['orderBy']}"; }
					if($field['dbh']) {
						$dbh = $field['dbh'];
					} else {
						$dbh = $this->DBH;
					}

			  		$rst=$dbh->execute($sql);
	
			  		if (!$rst) { db_error_handler($dbh,$sql); exit; }

					while (!$rst->EOF) {
						// update for array
						$select[$rst->fields[$field['foreignKey']]] =  $rst->fields[$field['foreignFields']];
						$rst->MoveNext();
					}
				}
				$form->addElement($field['type'], $field_name, $field['displayName'], $select);

			} else {

      			switch($field['type']) {

					case 'file':
						// The standard way we deal with files is to add the download link underneath the
						// Browse button in "edit" mode.  In Read Only mode, see the SetReadOnly() function.

						if(strcasecmp('GET', $this->form_method) == 0) {
							echo _("<h1>QuickForm Error: Form method GET will not work with File upload widget!</h1>");
						}
						$form->addElement('file', $field_name, $field['displayName'], $field['attributes']);


						/* Only showing download link in 'read-only' mode for now...

						$download_link = '';
						if($model->DoesFileExist($field_name)) {		
							$model_id = $this->GetPrimaryKeyValue($model);
							$s = '$download_link = "' . $field['downloadLink'] . '";';
							eval($s);
						}
						if($download_link) {
							$file_group = array();
							$file_group[] =  $form->createElement('file', $field_name, $field['displayName']);
							$file_group[] =  $form->createElement('static', null, null);

							// this is kindof weird.  the download link is set as a 'seperator', which will only get used
							// if there is a second element (the static created above)
							$form->addGroup($file_group, null, $field['displayName'], '<br/>' . '<a href="' . $download_link . '">Download this file</a>');
						} else {
							$form->addElement('file', $field_name, $field['displayName']);
						}
						*/
						break;
					case 'longblob':
					case 'blob':
						$form->addElement('static',  $field_name, $field['displayName'], 'Blob data not shown');
						break;
					case 'int':
					case 'tinyint':
					case 'double':
					case 'float':
					case 'string':
					case 'varchar':
					case 'char':
					case 'double unsigned':
					case 'text':
					case 'time':
 						$form->addElement('text', $field_name, $field['displayName'], $field['attributes']);
 						break;
					case 'textarea':
	          			$form->addElement('textarea', $field_name, $field['displayName'], $field['attributes']);
	          			break;
	        		case 'primarykey':
	        		case 'hidden':
	          			$form->addElement('hidden', $field_name, $field['attributes']);
	          			break;
	        		case 'date':
						global $http_site_root;

						$triggerName = "f_trigger_" . count($this->JSCodePost);

						$buttonHTML = "<tr><td><img ID=\"$triggerName\" style=\"CURSOR: hand\" border=0 src=\"$http_site_root/img/cal.gif\"></td></tr>";

						$date = array();
						$field['attributes']['id'] = $field_name;
	          			$date[] = $form->createElement('text', $field_name, $field['displayName'], $field['attributes']);
						$date[] = $form->createElement('image', null, "$http_site_root/img/cal.gif", array('id' => $triggerName, 'style' => 'CURSOR: hand', 'border' => 0));
						$form->addGroup($date, null, $field['displayName'], ' ');

						$this->JSCodePost[] = <<<END
							<script language="JavaScript" type="text/javascript">
								Calendar.setup({
	      						inputField     :    "$field_name",      // id of the input field
	      						ifFormat       :    "%Y-%m-%d",       // format of the input field
	      						showsTime      :    false,            // will display a time selector
	      						button         :    "$triggerName",   // trigger for the calendar (button ID)
	      						singleClick    :    true,           // double-click mode
	      						step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
	      						align          :    "Bl"           // alignment (defaults to "Bl")
									})
							</script>
END;

	          			break;
	        		case 'datetime':
							global $http_site_root;

							$triggerName = "f_trigger_" . count($this->JSCodePost);

							$date = array();
							$field['attributes']['id'] = $field_name;
	          				$date[] = $form->createElement('text', $field_name, $field['displayName'], $field['attributes']);
							$date[] = $form->createElement('image', null, "$http_site_root/img/cal.gif", array('id' => $triggerName, 'style' => 'CURSOR: hand', 'border' => 0));
							$form->addGroup($date, null, $field['displayName'], ' ');
	
							$this->JSCodePost[] = <<<END
							<script language="JavaScript" type="text/javascript">
								Calendar.setup({
	      						inputField     :    "$field_name",      // id of the input field
	      						ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
	      						showsTime      :    true,            // will display a time selector
	      						button         :    "$triggerName",   // trigger for the calendar (button ID)
	      						singleClick    :    true,           // double-click mode
	      						step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
	      						align          :    "Bl"           // alignment (defaults to "Bl")
								})
							</script>
END;
	          				break;

						case ((0 != preg_match("/enum\((.*)\)/", $field['type'], $matches)) ? $field['type'] : ''):

							$enum = array();
							$match_splinters = explode(',', $matches[1]);
							for($i=0; $i<count($match_splinters); $i++) {
							  $item = substr($match_splinters[$i], 1, -1);
								$enum[$item] = $item;
							}
							$form->addElement('select', $field_name, $field['displayName'], $enum, $field['attributes']);
							break;

						case 'html':
							$form->addElement('html', $field_name);
							break;

						case 'custom':
							$form->addElement('static',  $field_name, $field['displayName'], $field['customElement']);
							break;
						case 'db_only':
							// for this, we don't display anything
							break;
	
	        			default:
							echo "ADOdb_QuickForm Warning: no match found for {$field['type']}<br/>\n";
				}
			}
		}
	}


	/**
	* Adds a submit button to the form
	* If form_action is 'edit' it creates an 'update' submit button
	* This function also adds the hidden next_form_action and the returnURL button
	* @param string form_action The current form action (typ. $GET['form_action'] or $POST['form_action'])
	*/
	function AddSubmitButtonToQuickForm($form_action) {
		
	    $form =& $this->QF;

		// This is not this->SetConstants!
		$form->SetConstants(array('form_action' => $this->next_form_action));
 		$form->addElement('hidden', 'form_action', $form_action);
 		$form->addElement('hidden', 'return_url', $return_url);

		// form action is the one that brought us here (the action we just handled)
		switch($form_action) {
			case 'view':
				break;
			case 'edit':
 				$form->addElement('submit', 'btnSubmit', 'Update', array('id' => 'btnSubmit', 'class' => 'button'));
				break;
		case 'new':
 				$form->addElement('submit', 'btnSubmit', 'Create', array('id' => 'btnSubmit', 'class' => 'button'));
				break;
		}

	    if(!empty($this->ReturnButtonCaption) && !empty($this->ReturnButtonURL)) {
 			$form->addElement('button', 'returnURL', $this->ReturnButtonCaption, array('id' => 'returnURL', 'class' => 'button'));

			$this->JSCodePost[] = <<<END
				<script language="JavaScript" type="text/javascript">
					document.forms[0].returnURL.onclick = function() {
						location.href='{$this->ReturnButtonURL}';
					}
				</script>
END;
		}
	}

	/**
	* Updates the values in the ADOdb_QuickForm_Model objects based on the form (GET/POST) values.
	*
	* The elements 
	*/
	function UpdateModelsFromView() {

		for($j=0; $j<count($this->Models); $j++) {
			//print_r($this->GetSubmitValues());
			$this->Models[$j]->SetValues($this->GetSubmitValues());
		}

		// Do this part second because SetValues above overwrites all values
		foreach($_FILES as $field_name => $file_info) {
			if(!$file_info['error']) {
				for($j=0; $j<count($this->Models); $j++) {
					if(false !== $this->Models[$j]->GetFieldIndex($field_name)) {
						$this->Models[$j]->FileUploadNotify($field_name);
					}
				}
			} else {
				if(UPLOAD_ERR_NO_FILE != $file_info['error']) {
					echo "QuickForm Error: file upload error {$file_info['error']}<br/>";
				}
			}
		}
	}

	/**
	* This function is necessary to make sure that the values in Model
	* override the ones that come in from GET/POST.
	*/
	function SetConstants() {
		for($j=0; $j<count($this->Models); $j++) {
			$this->QF->SetConstants($this->Models[$j]->GetValues());
		}
	}
	function SetDefaults() {
		for($j=0; $j<count($this->Models); $j++) {
			$this->QF->SetDefaults($this->Models[$j]->GetValues());
		}
	}

	/**
	* Make the form be non-editable
	* which will make the form read-only
	*/
	function SetReadOnly() {
		// when the form is read only, all 'file' elements should be changed to download links.
		// when the form is in edit mode, the browse button should be there.

		// if the name field is set, lets assume that there is a file.
		// if there is a file, let's show the download link.


		for($j=0; $j<count($this->Models); $j++) {

			// This crazy code is needed because we can only insertElementBefore so we have to track
			// where in the list of form elements this one fits since there is no insertElementAfter.

			$fields 	= $this->Models[$j]->GetFields();
			$tablename 	= $this->Models[$j]->GetTableName();


   			for($i=0; $i<count($fields); $i++) {
				$field = $fields[$i];

				// change date to text (effectively removing the date image)
				//  removeGroup doesn't exist yet...maybe someday...  

				// change file to a link
				if('file' == $field['type']  &&  true == $this->Models[$j]->DoesFileExist($field['name'])) {
					$field_name = $this->GetPrependedFieldName($tablename,  $field['name']);

					$old = $this->QF->removeElement($field_name, false);
					$model_id = $this->Models[$j]->GetPrimaryKeyValue();
					$s = '$download_link = "' . $field['downloadLink'] . '";';
					eval($s);
					$add_link_to_next = true;
					$display_name = $field['displayName'];

					if($i + 1 < count($fields)) {
						$new = $this->QF->createElement('link', 'test', $display_name, $download_link, 'Download this file');

						$field_name_after = $this->GetPrependedFieldName($tablename,  $fields[$i + 1]['name']);
						$this->QF->insertElementBefore($new, $field_name_after);

					} else {
						$this->QF->addElement('link', 'test', $display_name, $download_link, 'Download this file');
					}
				}
			}
		}
		$this->QF->freeze();
	}

	// create the table_QF_fieldname string
	function GetPrependedFieldName($table, $field) {
		if($this->PrependTablename) {
			return $table . '_QF_' . $field;
		} else {
			return $field;
		}
	}
	// recreate the original fieldname
	function GetOriginalFieldName($table, $field) {
		if($this->PrependTablename) {
			return substr($field, strlen($table . '_QF_'));
		} else {
			return $field;
		}
	}

}

/**
* $log $
*/

?>
