<?php
/**
* Model class of the ADOdb_QuickForm system.
*
* Copyright (c) 2004 Explorer Fund Advisors, LLC
* All Rights Reserved.
*
*
* @author Justin Cooper <justin@braverock.com>
* @todo
*
* $Id: ADOdb_QuickForm_Model.php,v 1.1 2005/01/10 14:34:14 daturaarutad Exp $
*/


/**
*
* Model class to represent a single table in an ADOdb accessible database.
*
* Includes functionality to read the schema directly from a database, or
* may be set up manually via functions such as AddField, AddForeignKeyField.
*
* @package ADOdb_QuickForm
*/
class ADOdb_QuickForm_Model {

	/** @var array Internal model structure */
	var $DBStructure;

	/** @var array the current values of the model */
	var $Values;

	/**
	* Constructor
	*
	*/
	function ADOdb_QuickForm_Model() { 
		$this->DBStructure = array();
		$this->Values = array();
	}


	/**
	* Reads the schema using ADOdb fn MetaColumns()
	*
	* @param object dbh ADOdb Database handle
	* @param string tablename Name of the table to query
	* @todo Add support for ADOdb fn MetaForeignKeys()
	*/
	function ReadSchemaFromDB($dbh, $tablename) {

		$struct =& $this->DBStructure;
		
		$struct['tablename'] = $tablename;
		$struct['dbh'] = $dbh;

		$columns = $dbh->MetaColumns($tablename);

		$i=0;

		/* echo "<pre>";
		print_r($columns);
		echo"</pre>";
		*/
		
		foreach($columns as $column) {

			$struct['fields'][$i]['name'] = $column->name;

			if($column->primary_key) {

				$struct['primarykey'] = $column->name;
				$struct['fields'][$i]['type'] = 'primarykey';

			} else {

				$struct['fields'][$i]['type'] = $column->type;

			}
			$struct['fields'][$i]['displayOrder'] = $i;
			$struct['fields'][$i]['displayName'] = $column->name;
			$i++;
		}
		
		/* Not implemented for mysql in ADOdb (yet)
		$foreignkeys = $dbh->MetaForeignKeys($tablename, false, true);
		if($foreignkeys) {
			foreach($foreignkeys as $foreignkey) {
				//do that stuff aww do that stuff
		}
		} */
	}


	/**
	* Manually Add a Field to the Model
	*
	* @param string name Name of the field
	* @param string type Type of the field (db type, that is)
	* @param float displayOrder Unused at the moment
	* @param string displayName Name to use for caption
	*/
	function AddField($name, $type, $displayOrder, $displayName = null, $attributes = null) {

		$i = count($this->DBStructure['fields']) + 1;

		$this->DBStructure['fields'][$i]['name'] = $name;
		$this->DBStructure['fields'][$i]['type'] = $type;
		$this->DBStructure['fields'][$i]['displayOrder'] = $displayOrder;
		$this->DBStructure['fields'][$i]['displayName'] = empty($displayName) ? $name : $displayName;
		$this->DBStructure['fields'][$i]['attributes'] = $attributes;
	}

	/**
	* Add a custom Field to the Model
	*
	* This can be useful if you want to use a <select> but don't want to show every value...just build the select
	* youself and pass it in to this function.  
	*
	* @param string name Name of the field
	* @param integer index Position of the new field
	* @param float displayOrder Unused at the moment
	* @param string displayName Name to use for caption
	* @param string customElement HTML containing the custom element
	*/
	function AddCustomField($name, $index, $customElement, $attributes = null) {

		$index = $index - 0.5;

        $this->DBStructure['fields']["$index"]['name'] = $name;
        $this->DBStructure['fields']["$index"]['type'] = 'custom';
        $this->DBStructure['fields']["$index"]['displayOrder'] = $displayOrder;
        $this->DBStructure['fields']["$index"]['displayName'] = empty($displayName) ? $name : $displayName;
        $this->DBStructure['fields']["$index"]['customElement'] = $customElement;
		$this->DBStructure['fields']["$index"]['attributes'] = $attributes;
		
		$this->ReSort();
	}

	/**
	* Remove a Field from the Model
	*
	* @param string name Name of the field
	*/
	function RemoveField($name) {
	  if(false !== ($i = $this->GetFieldIndex($name))) {

			$tmp = $this->DBStructure['fields'][$i];
			unset($this->DBStructure['fields'][$i]);

			$this->ReSort();
			return $tmp;
		}
		return false;
	}
	function ReSort() {

		$fields = array();
		ksort($this->DBStructure['fields']);
		$i=0;

		foreach($this->DBStructure['fields'] as $k => $field) {
			$fields[$i++] = $field;
		}
		$this->DBStructure['fields'] = $fields;
	}
	function GetFields() {
		return $this->DBStructure['fields'];
	}
	function GetFieldType($name) {
	  if(false !== ($i = $this->GetFieldIndex($name))) {
			return $this->DBStructure['fields'][$this->GetFieldIndex($name)]['type'];
		}
	}

	/**
	* Set the type for a field.  
	*
	* Typically used to set 'file' type from a field that may have been read as "blob"
	*
	* @param string name Name of the field
	* @param string type Type of the field (db type, that is)
	* @return boolean returns false if field not found.
	*/
	function SetFieldType($name, $type, $attributes = null) {
	  if(false !== ($i = $this->GetFieldIndex($name))) {
			$this->DBStructure['fields'][$i]['type'] = $type;
			$this->DBStructure['fields'][$i]['attributes'] = $attributes;
			return true;
		}
		return false;
	}

	/**
	* Returns the index into DBStructure for a field. 
	*
	* Not typically useful outside of this class
	*/
	function GetFieldIndex($name) {
		for($i=0; $i<count($this->DBStructure['fields']); $i++) {
			if($name == $this->DBStructure['fields'][$i]['name']) {
				return $i;
			}
		}
		return false;
	}

	/**
	* Adds a Foreign Key Field to the Model
	*
	* This function is meant to be used only when ReadSchemaFromDB didn't find the field at all.
	* If the field was found but not identified as a Foreign Key, you should use SetForeignKeyField instead.
	*
	* @param string Name of the field
	* @param string Type of the field (db type, that is)
	* @param float Unused at the moment
	* @param string Name to use for caption
	* @param string Foreign table to use
	* @param string Primary Key of the Foreign table to use
	* @param string Field of the Foreign table whose values will appear in the <select>
	* @param object Database handle for the db with the foreign table (optional)
	* @param array An array containing additional <option>s which will be stuffed in top of the <select>
	*/
	function AddForeignKeyField($name, $type, $displayOrder, $displayName, $foreignTable, $foreignKeyName, $foreignDisplayField, $dbh=null, $selectAdditionalValues = null, $orderBy = null, $attributes = null) {
		$i = count($this->DBStructure['fields']) + 1;

		$this->DBStructure['fields'][$i]['name'] = $name;
		$this->DBStructure['fields'][$i]['type'] = $type;
		$this->DBStructure['fields'][$i]['displayOrder'] = $displayOrder;
		$this->DBStructure['fields'][$i]['displayName'] = $displayName;
    	$this->DBStructure['fields'][$i]['foreignTable']= $foreignTable;
    	$this->DBStructure['fields'][$i]['foreignKey']  = $foreignKeyName;
    	$this->DBStructure['fields'][$i]['foreignFields'] = $foreignDisplayField;
    	$this->DBStructure['fields'][$i]['dbh'] = $dbh;
    	$this->DBStructure['fields'][$i]['selectAdditionalValues'] = $selectAdditionalValues;
    	$this->DBStructure['fields'][$i]['orderBy'] = $orderBy;
    	$this->DBStructure['fields'][$i]['isForeignKey'] = true;
    	$this->DBStructure['fields'][$i]['attributes'] = $attributes;
	}

	/**
	* Sets Foreign Key status for a given field
	*
	* @param string Name of the field
	* @param string Name to use for caption
	* @param string Foreign table to use
	* @param string Primary Key of the Foreign table to use
	* @param string Field of the Foreign table whose values will appear in the <select>
	* @param object Database handle for the db with the foreign table (optional)
	* @param array An array containing additional <option>s which will be stuffed in top of the <select>
	*/
	function SetForeignKeyField($name, $displayName, $foreignTable, $foreignKeyName, $foreignDisplayField, $dbh=null, $selectAdditionalValues = null, $orderBy = null, $attributes = null) {
		  
		$i = $this->GetFieldIndex($name);
		if(false !== $i) {

   			$this->DBStructure['fields'][$i]['type']= 'select';
			$this->DBStructure['fields'][$i]['displayName'] = $displayName;
   			$this->DBStructure['fields'][$i]['foreignTable']= $foreignTable;
   			$this->DBStructure['fields'][$i]['foreignKey']  = $foreignKeyName;
   			$this->DBStructure['fields'][$i]['foreignFields'] = $foreignDisplayField;
    		$this->DBStructure['fields'][$i]['dbh'] = $dbh;
	    	$this->DBStructure['fields'][$i]['selectAdditionalValues'] = $selectAdditionalValues;
    		$this->DBStructure['fields'][$i]['orderBy'] = $orderBy;
	    	$this->DBStructure['fields'][$i]['isForeignKey'] = true;
    		$this->DBStructure['fields'][$i]['attributes'] = $attributes;
	
			return true;
		} else {
			return false;
		}
	}


	/**
	* Inform the model that a field should be used as a file field
	*
	* This functionality is fairly hard-coded to work with the ChangeButton data model at the
	* moment.  It presumes that there will be fields in a table for file size, file name, file mime type,
	* and a download link.
	*
	* @param string Fieldname of the blob containing the actual file data
	* @param string Fieldname of the size field
	* @param string Fieldname of the name field
	* @param string Fieldname of the type field
	* @param string downloadLink the URL that will be set for the Download link
	*/
	function SetFileField($fileField, $nameField, $typeField, $sizeField, $downloadLink) {

		$i = $this->GetFieldIndex($fileField);

		if(false !== $i) {
   			$this->DBStructure['fields'][$i]['type']= 'file';
   			$this->DBStructure['fields'][$i]['sizeField']= $sizeField;
   			$this->DBStructure['fields'][$i]['nameField']= $nameField;
   			$this->DBStructure['fields'][$i]['typeField']= $typeField;
   			$this->DBStructure['fields'][$i]['downloadLink']= $downloadLink;
			return true;
		} else {
			return false;
		}
	}
	/**
	* Called by the View...private
	*
	* this fn is called from view when a file has been uploaded.  
	* Write will use the info passed in here to get to the file when it's time to write.
	*/
	function FileUploadNotify($field) {

		$i = $this->GetFieldIndex($field);
		if(false !== $i) {

			$file_name	= $_FILES[$field]['name'];
			$file_type	= $_FILES[$field]['type'];
			$file_size	= $_FILES[$field]['size'];
			$file_tmp_location	= $_FILES[$field]['tmp_name'];


			// set up the Values
			//$con = $this->DBStructure['dbh'];
			//$this->Values[$field] = $con->qstr(fread(fopen($file_tmp_location, "r"), $file_size));
			$this->Values[$field] = fread(fopen($file_tmp_location, "r"), $file_size);
			$this->Values[$this->DBStructure['fields'][$i]['sizeField']] = $file_size;
			$this->Values[$this->DBStructure['fields'][$i]['nameField']] = $file_name;
			$this->Values[$this->DBStructure['fields'][$i]['typeField']] = $file_type;
		} 
	}
	function DoesFileExist($field_name) {
		$i = $this->GetFieldIndex($field_name);
		if($i && $this->Values[$this->DBStructure['fields'][$i]['nameField']] && $this->Values[$this->DBStructure['fields'][$i]['sizeField']]) {
			return true;
		} 
		return false;
	}



	function GetTableName() { return $this->DBStructure['tablename']; }
	function SetTableName($tablename) { $this->DBStructure['tablename'] = $tablename; }

	function GetPrimaryKeyName() { return $this->DBStructure['primarykey']; }
	function SetPrimaryKeyName($primarykeyname) { $this->DBStructure['primarykey'] = $primarykeyname; }

	function GetPrimaryKeyValue() { return $this->Values[$this->GetPrimaryKeyName()]; }

  /**
  * function SetDisplayNames
  * 
  * Sets the Display Names for a form.  By default the labels next to each form 
  * field are the same as the database field name.  Pass this function an assoc.
  * array of fieldname = displayname to override the display names.
  *
  * Note: this function must be called before Process()
  *
  * @param array Associative array of display names
  * 
  */
	function SetDisplayNames($displayNames) { 
		foreach($displayNames as $name => $displayName) {
			$this->SetDisplayName($name, $displayName);
		}
	}

  /**
  * function SetDisplayName
  * 
  * Sets the Display Name for a single element within a form. 
  *
  * Note: this function must be called before Process()
  *
  * @param string Field Name
  * @param string Display Name
  * 
  */
	function SetDisplayName($name, $displayName) { 
		$i = $this->GetFieldIndex($name);
		if(false !== $i) {
			$this->DBStructure['fields'][$i]['displayName'] = $displayName;
			return true;
		} else {
			return false;
		}
	}

	function GetValues() {
		return $this->Values;
	}
	function SetValues($Values) {
		$this->Values = $Values;
	}
	function SetFieldValue($field, $value) {
		$this->Values[$field] = $value;
	}
	function QuoteValues() {
		$quoted_values = array();
		foreach($this->Values as $k => $v) {

			$i = $this->GetFieldIndex($k);
			if(false !== $i) {

				// use stripos if php5
				if(false !== strpos($this->DBStructure['fields'][$i]['type'], 'enum')) {
					$quoted_values[$k] =  $this->DBStructure['dbh']->qstr($v, get_magic_quotes_gpc());
				} else {
					$quoted_values[$k] = $v;
				}
			}
		}
		return $quoted_values;

	}
	function PrintValues() {
		echo "<pre>Values for " . $this->DBStructure['tablename'] . ":\n";
		print_r($this->Values);
		echo "</pre>";
	}

	/**
	* Read from the Database and set model's state
	*
	* @param string ID value of the primary key
	*/
	function Read($id) {
		$tablename = $this->DBStructure['tablename'];
		$primarykeyname = $this->DBStructure['primarykey'];
	
		if($id && $tablename && $primarykeyname) {
			$columns = '';
	
			$columns = $this->GetColumns();
	
   			$sql="select $columns from $tablename where $primarykeyname = $id";
	
			$dbh = $this->DBStructure['dbh']; 
   			$rst = $dbh->execute($sql);
	
   			if (!$rst) { db_error_handler($dbh,$sql); exit; }
	
   			// override GET/POST vars
   			$this->Values = $rst->fields;
		}
	}

	/**
	* Write the current values to the Database
	*
	* Uses GetUpdateSQL
	*
	* @param string ID value of the primary key
	*/
	function Write($id) {
	
		// replace this stuff with a call to $this->Read();
	
		$tablename = $this->DBStructure['tablename'];
		$primarykeyname = $this->DBStructure['primarykey'];
	
		$dbh = $this->DBStructure['dbh']; 
	
		$columns = $this->GetColumns();
	
    	$sql="select $columns from $tablename where $primarykeyname = $id";
	
    	$rst=$dbh->execute($sql);
	
    	if (!$rst) { db_error_handler($dbh,$sql); exit; }
	
    	$sql = $dbh->GetUpdateSQL($rst, $this->Values, true, false, true);
	
    	if($sql) {
      	$rst=$dbh->execute($sql);
      	if (!$rst) { db_error_handler($dbh,$sql); exit; }
    	}
	}

	/**
	* Create a record in the DB using the current values
	*
	*/
	function Create() {
	
		$tablename = $this->DBStructure['tablename'];
		$primarykeyname = $this->DBStructure['primarykey'];

		$dbh = $this->DBStructure['dbh']; 
		// only quote enums on Insert
		$quoted_values = $this->QuoteValues();
	
    	$sql = $dbh->GetInsertSQL($tablename, $quoted_values, false);
	
    	if($sql) {
      		$rst=$dbh->execute($sql);
      		if (!$rst) { db_error_handler($dbh,$sql); exit; }
			return $dbh->Insert_Id();
    	}
		return false;
	}

	/**
	* Used internally to skip over blob fields when performing a Read
	*/
	function GetColumns() {
		$dbh = $this->DBStructure['dbh']; 

		$columns = '';
		for($i=0; $i<count($this->DBStructure['fields']); $i++) {

			if(isset($this->DBStructure['fields'][$i]) && 
				'blob' != $this->DBStructure['fields'][$i]['type'] && 
				'longblob' != $this->DBStructure['fields'][$i]['type']) 
				{
					$columns .= $this->DBStructure['fields'][$i]['name'] . ',';
				}
			}
			if($columns) {
		 		$columns = substr($columns, 0, -1);
			}
			return $columns;
		}
	} // ADOdb_QuickForm_Model class


?>
