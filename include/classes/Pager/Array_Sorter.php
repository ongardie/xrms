<?php

/**
* Handles multidimentional array sorting by a key (not recursive)
*
* The following sort class came from http://us2.php.net/manual/en/function.uksort.php
*
* Justin modified this function to assume arrays always! (used by Pager)
*
* $Id: Array_Sorter.php,v 1.5 2005/03/08 22:56:31 daturaarutad Exp $
* @author Oliwier Ptak <aleczapka at gmx dot net>
* @author Justin cooper <justin at braverock dot com>
*/
class array_sorter
{
   var $skey = false;
   var $sarray = false;
   var $sasc = true;

   var $sort_is_numeric = true;

   /**
   * Constructor
   *
   * @access public
   * @param mixed $array array to sort
   * @param string $key array key to sort by
   * @param boolean $asc sort order (ascending or descending)
   */
   function array_sorter(&$array, $key, $asc=true)
   {
       $this->sarray = $array;
       $this->skey = $key;
       $this->sasc = $asc;
   }

   /**
   * Sort method
   *
   * @access public
   * @param boolean $remap if true reindex the array to rewrite indexes
   */
   function sortit($remap=true)
   {
       $array = &$this->sarray;

		foreach($array as $k => $v) {

			// this should work for 0, '', and null as well (they shoudn't set 'is not numeric')
			if($v[$this->skey] && !is_numeric($v[$this->skey])) {
				//echo $v[$this->skey]  . " is not numeric!<br>";
				$this->sort_is_numeric = false;
				break;
			}
		}

       uksort($array, array($this, "_as_cmp"));
       if ($remap)
       {
           $tmp = array();
		   reset($array);
           while (list($id, $data) = each($array))
               $tmp[] = $data;
           return $tmp;
       }
       return $array;
   }

   /**
   * Custom sort function
   *
   * @access private
   * @param mixed $a an array entry
   * @param mixed $b an array entry
   */
   function _as_cmp($a, $b)
   {
   	   // Justin modified this function to assume arrays always!

       //since uksort will pass here only indexes get real values from our array
       //if (!is_array($a) && !is_array($b))
       //{
           $a = $this->sarray[$a][$this->skey];
           $b = $this->sarray[$b][$this->skey];
       //}

       //if string - use string comparision
	   // justin changed to use is_numeric instead of ctype_digit, which fails on decimal points
       //if (!ctype_digit($a) && !ctype_digit($b))
       //if (!is_numeric($a) && !is_numeric($b))
	   if($this->sort_is_numeric)
       {
           if ($a == $b)
               return 0;

           if ($this->sasc)
               return ($a > $b) ? -1 : 1;
           else
               return ($a > $b) ? 1 : -1;
       }
       else
       {
           if ($this->sasc)
               return strcmp($a, $b);
           else
               return strcmp($b, $a);
       }
   }

}//end of class

/**
* $Log: Array_Sorter.php,v $
* Revision 1.5  2005/03/08 22:56:31  daturaarutad
* reset the array before calling each()
*
* Revision 1.4  2005/03/03 21:39:25  daturaarutad
* another stab at tidy comments...
*
*/


?>
