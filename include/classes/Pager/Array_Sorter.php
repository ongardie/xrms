<?php

// the following sort class came from http://us2.php.net/manual/en/function.uksort.php
/**
* Handles multidimentional array sorting by a key (not recursive)
*
* @author Oliwier Ptak <aleczapka at gmx dot net>
*/
class array_sorter
{
   var $skey = false;
   var $sarray = false;
   var $sasc = true;

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
       uksort($array, array($this, "_as_cmp"));
       if ($remap)
       {
           $tmp = array();
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
       //since uksort will pass here only indexes get real values from our array
       if (!is_array($a) && !is_array($b))
       {
           $a = $this->sarray[$a][$this->skey];
           $b = $this->sarray[$b][$this->skey];
       }

       //if string - use string comparision
	   // justin changed to use is_numeric instead of ctype_digit, which fails on decimal points
       //if (!ctype_digit($a) && !ctype_digit($b))
       if (!is_numeric($a) && !is_numeric($b))
       {
           if ($this->sasc)
               return strcmp($a, $b);
           else
               return strcmp($b, $a);
       }
       else
       {
           if ($a == $b)
               return 0;

           if ($this->sasc)
               return ($a > $b) ? -1 : 1;
           else
               return ($a > $b) ? 1 : -1;
       }
   }

}//end of class


?>
