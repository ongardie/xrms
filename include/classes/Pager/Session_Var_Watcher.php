<?php
/**
*	How it works:
*		This class takes variable names and their values and stores them internally
*		When UseCache() is called, it compares the values given with the values stored in the session.
* 		Then it stores the current values in the session for the next time the script is called.
*
*		The table below shows the behavior of the CGI vars vs Local vars.
*		What you should get from this is that if you want your Pager to start caching after the first
*		display, you should probably use RegisterLocalVars and not RegisterCGIVars.
*		If your script is always passed in CGI vars, then you will be starting on step 1 and it will work as you'd intuitively suspect.
*
*				CGI behavior
*  	step	CGI		Session		Use Cache
*	-------------------------------------
*	0		--		--			false	
*	1		23		--			false	
* 	2		23		23			true
* 
*				Local var behavior
*  	step	Local	Session		Use Cache
*	-------------------------------------
*	0		34		--			false	
* 	1		34		34			true
*
* @author Justin Cooper
* $Id: Session_Var_Watcher.php,v 1.1 2005/03/21 21:19:10 daturaarutad Exp $
*/

class SessionVarWatcher {

	var $id;
	var $cgi_vars 	= array();
	var $local_vars = array();

	function SessionVarWatcher($id) { 
		$this->id = $id;
	}
    function RegisterCGIVars($vars) {
		foreach($vars as $v) array_push($this->cgi_vars, $v);
	}
    function RegisterCGIVar($var) {
		array_push($this->cgi_vars, $var);
	}
    function RegisterLocalVars($vars) {
		foreach($vars as $k => $v) $this->local_vars[$k] = $v;
		foreach($vars as $k => $v) echo "$k-$v<br>";
	}
    function RegisterLocalVar($k, $v) {
		$this->local_vars[$k] = $v;
	}

	/**
	* @return boolean Indicates whether or not variables have changed since the last invocation
	*/
	function VarsChanged() {
		$return = false;

		// check CGI vars
        foreach($this->cgi_vars as $var) {
            $v = null;
			echo "$var";
            getGlobalVar($v, $var);

            if(array_key_exists($this->id . $var, $_SESSION) && $_SESSION[$this->id . $var] != $v) {
                //echo "$var has changed from {$_SESSION[$this->id . $var]} to $v, flushing the cache<br/>";
				$return = true;
            } else {
                //echo "$var is the same: {$_SESSION[$this->id . $var]} to $v<br/>";
			}
            $_SESSION[$this->id . $var] = $v;
        }

		// check local vars
        foreach($this->local_vars as $varname => $value) {
			// Check for local vars too!
        	if(!array_key_exists($this->id . $varname, $_SESSION) && $_SESSION[$this->id . $varname]) {
            	//echo "no entry in cache for $varname, but var has value $value<br>";
				$return = true;
        	}
        	elseif(array_key_exists($this->id . $varname, $_SESSION) && $_SESSION[$this->id . $varname] != $value) {
            	//echo "$varname has changed from {$_SESSION[$this->id . $varname]} to $value, flushing the cache<br/>";
				$return = true;
        	} else {
            	//echo "$varname has not changed<br>";
			}
        	$_SESSION[$this->id . $varname] = $value;
		}
		return $return;
    }
}

/**
* $Log: Session_Var_Watcher.php,v $
* Revision 1.1  2005/03/21 21:19:10  daturaarutad
* new class for handling caching issues
*
*/

?>
