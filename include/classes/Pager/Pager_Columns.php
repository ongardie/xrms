<?php


/*

	-user loads one_portfolio.php
	-one_portfolio.php creates Pager_Columns object
	-Pager_Columns object checks to see if new columns have been selected by checking for a CGI var Portfolio1_New_Columns_View, adding them to session
	-pager_columns checks to see if there is a column list in the session (for Portfolio1)
	-if there is, it does the thing to drop teh columns
	-if not, it does the same, but with the default list
	-page renders

	-user clicks 'display selectable columns'
	-div hidden toggles and changes text to 'hide selectable columns'
	-user clicks around and at some point is happy with their columns list and clicks 'update column view for this session' button which triggers a javascript function
	-javascript packs the column list into a variables along with the pager name, submits form
*/


class Pager_Columns {
	
	var $pager_name;
	var $pager_columns;
	var $default_columns;
	var $form_id;

	function Pager_Columns($pager_name, $pager_columns, $default_columns, $form_id) {

		getGlobalVar($new_columns_view, $pager_name . '_New_Columns_View');

		if($new_columns_view) {

			// getGlobalVar for the user_columns, pack into an array $user_columns
			getGlobalVar($new_columns, $pager_name . '_New_Columns');

			$user_columns = explode(',', $new_columns);
			//$user_columns = array(explode(',' $new_columns));

			// set it in the session
			$_SESSION[$pager_name . '_columns_view'] = $user_columns;
		}

		$this->pager_name = $pager_name;
		$this->pager_columns = $pager_columns;
		$this->default_columns = $default_columns;
		$this->form_id = $form_id;
	}


	function GetUserColumns() {

        // User Columns will come from the session or default list passed in
		if($_SESSION[$this->pager_name . '_columns_view']) {
			$user_columns = $_SESSION[$this->pager_name . '_columns_view'];
		} else {
			$user_columns = $this->default_columns;
		}

        $return_pager_columns = array();

		// replace with a funkier function that does in_array for arrays-of-arrays
        foreach($user_columns as $user_column) {
            foreach($this->pager_columns as $pager_column) {
                if($user_column == $pager_column['index']) {
                    $return_pager_columns[] = $pager_column;
                    break;
                }
            }
        }
        return $return_pager_columns;
	}

	function GetSelectableColumnsButton() {

		return '<input type="button" class="button" onclick="document.getElementById(\'' . $this->pager_name . '_selectable_columns\').style.display=\'block\'" value="Select Columns">';
	}
	function GetSelectableColumnsWidget() {

		$s = <<<END
		<input type="hidden" name="{$this->pager_name}_New_Columns_View">
		<input type="hidden" name="{$this->pager_name}_New_Columns">

<script language="JavaScript" type="text/javascript">

	function {$this->pager_name}_columns_change(obj) {

		// get the form from obj, which is the button that was pushed (should be able to navigate upwards to get to the actual form

		// set the form vars to reflect the columns selected by the error
		// and submit the form
		// also set this thingy:
		document.{$this->form_id}.{$this->pager_name}_New_Columns_View.value = true;
		document.{$this->form_id}.{$this->pager_name}_New_Columns.value = 'symbol,instrument_name,net_value,start_position,start_position_value,end_position,end_position_value,average_cost,total_txn_cost,realized_gl,unrealized_gl';

		document.{$this->form_id}.submit();
	}
</script>


<!-- the hidden div -->
<div id="{$this->pager_name}_selectable_columns">
	<textarea rows="5" cols="30" name="{$this->pager_name}_all_columns" value="yomammy">
this is the list of ALL columns
blah blah blah
	</textarea>
	<textarea rows="5" cols="30" name="{$this->pager_name}_selected_columns" value="yomammy">
this is the list of Selected columns
for now, let's pretend that you wanted
to remove long_value and short_value...
	</textarea>
	<br/>

	<input type="button" name="button" onclick="{$this->pager_name}_columns_change(this)" value="Update Columns List">

</div>

<script language="JavaScript" type="text/javascript">
	document.getElementById('{$this->pager_name}_selectable_columns').style.display = 'none';
</script>

END;


		return $s;
	}
}


















?>
