<?php
// $Id: captcha.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

/*
 * CAPTCHA, see http://www.captcha.net/, a project to make it harder
 * for spam-robots to automatic fill out forms. Now adapted for 'Tavi
 *
 * Original work done by: Jayesh Sheth, Cerulean Sky Creations, LLC, 2004
 *                         ceruleansky.com
 *
 * Heavily modified, when entered into 'Tavi by:  Even Holen      
 *
 * The font library is based on a version from 
 *     http://www.thebobo.com/code/phpfiglet
 * And uses figlet fonts, see http://www.figlet.org/ for additional fonts
 *
 * The main purpose of this extension is to generate a random image based
 * on random characters. The user needs to enter these characters to be able
 * to save the changes he just made.
 */
 
/*
// Dependencies:
require("phpfiglet_class.php");
require("dir_to_array.php");
require("makerandpass.php");
*/


// Renaming:
//     m.ake_ascii_phrase => generate_captcha
//     d.ir_to_array        => get_figlet_fontlist
//     m.akerandpassarray   => generate_phrase 
//

/*        _         _____  _       _       _
 *   ___ | |_  ___ |   __||_| ___ | | ___ | |_
 *  | . ||   || . ||   __|| || . || || -_||  _|
 *  |  _||_|_||  _||__|   |_||_  ||_||___||_|
 *  |_|       |_|            |___|
 *
 *  Author   :    Lucas Baltes (lucas@thebobo.com)
 *          $Author: gpowers $
 *
 *  Website  :    http://www.thebobo.com/
 *
 *  Date   :    $Date: 2005/04/12 20:45:12 $
 *  Rev      :    $Revision: 1.1 $
 *
 *  Copyright:    2003 - Lucas Baltes
 *  License  :    GPL - http://www.gnu.org/licenses/gpl.html
 *
 *  Purpose  :    Figlet font class
 *
 *  Comments :    phpFiglet is a php class to somewhat recreate the
 *          functionality provided by the original figlet program
 *          (http://www.figlet.org/). It does not (yet) support the
 *          more advanced features like kerning or smushing. It can
 *          use the same (flf2a) fonts as the original figlet program
 *          (see their website for more fonts).
 *
 *  Usage    :    $phpFiglet = new phpFiglet();
 *
 *          if ($phpFiglet->loadFont("fonts/standard.flf")) {
 *            $phpFiglet->display("Hello World");
 *          } else {
 *            trigger_error("Could not load font file");
 *          }
 *
 */

class phpFiglet
{

  /*
   *  Internal variables
   */
  var $signature;
  var $hardblank;
  var $height;
  var $baseline;
  var $maxLenght;
  var $oldLayout;
  var $commentLines;
  var $printDirection;
  var $fullLayout;
  var $codeTagCount;
  var $fontFile;

  /*
   *  Contructor
   */
  function phpFiglet()
  {
  }


  /*
   *  Load an flf font file. Return true on success, false on error.
   */
  function loadfont($fontfile)
  {
    $this->fontFile = file($fontfile);
    if (!$this->fontFile) die("Couldnt open fontfile $fontfile\n");
    $hp = explode(" ", $this->fontFile[0]); // get header

    $this->signature = substr($hp[0], 0, strlen($hp[0]) -1);
        $this->hardblank = substr($hp[0], strlen($hp[0]) -1, 1);
        $this->height = $hp[1];
        $this->baseline = $hp[2];
        $this->maxLenght = $hp[3];
        $this->oldLayout = $hp[4];
        $this->commentLines = $hp[5] + 1;
        if (count($hp) > 6) {
          $this->printDirection = $hp[6];
          $this->fullLayout = $hp[7];
          $this->codeTagCount = $hp[8];
        }

        unset($hp);

        if ($this->signature != "flf2a") {
          trigger_error("Unknown font version " . $this->signature . "\n");
          return false;
        } else {
          return true;
        }
  }

  /*
   *  Get a character as a string, or an array with one line
   *  for each font height.
   */
  function getCharacter($character, $asarray = false)
  {
    $asciValue = ord($character);
    $start = $this->commentLines + ($asciValue - 32) * $this->height;
    $data = ($asarray) ? array() : "";

    for ($a = 0; $a < $this->height; $a++)
    {
      $tmp = $this->fontFile[$start + $a];
      $tmp = str_replace("@", "", $tmp);
      //$tmp = trim($tmp);
      $tmp = str_replace($this->hardblank, " ", $tmp);

      if ($asarray) {
        $data[] = $tmp;
      } else {
        $data .= $tmp;
      }
    }

    return $data;
  }

  /*
   *  Returns a figletized line of characters.
   */
  function fetch($line)
  {
    $ret = "";

    for ($i = 0; $i < (strlen($line)); $i++)
    {
      $data[] = $this->getCharacter($line[$i], true);
    }

    @reset($data);

    for ($i = 0; $i < $this->height; $i++)
    {
      while (list($k, $v) = each($data))
      {
        $ret .= str_replace("\n", "", $v[$i]);
      }
      reset($data);
      $ret .= "\n";
      
    }

    return $ret;
  }


  /*
   *  Display (print) a figletized line of characters.
   */
  function display($line)
  {
    print $this->fetch($line);
  }
  
  function ret_display($line)
  // J.S., 2004
  {
    return $this->fetch($line);
  }

} // End of class phpFiglet

// Reads files from a directory, and filters according to extension
function get_figlet_fontlist($dir, $fileExt)
{
  $handle = opendir($dir);
  $dirfiles = array();
  
  // Read all files from given directory
  while ($file = readdir($handle)) 
  {
    // Filter out those with only one '.' and correct extension
    if (preg_match("/[^.]*\.$fileExt/", $file)) {
         $dirfiles[] = "$dir/$file"; // Store list of files in an array        
    }
  }
  
  closedir($handle);

  if ( count($dirfiles) > 0 ) // Do not return array if it is empty
  {
    return $dirfiles;  
  }
}

function generate_phrase($plength = 8)
{
  $phrase = '';
  for($i = 0; $i < $plength; $i++)
  {
    // Note numbers are not used, due to a mysterious bug in phpFiglet
    $letter = rand(65, 90);//65 - 90 is the ascii codes of capital letters
    $phrase .= chr($letter);  
  }
  return $phrase;
}

// Concatenates each of the character images in $chars to be one large
// string. 
function join_ascii_images($chars){
  $maxLines = 0; $i=0;
  
  // Find max number of lines in character images
  foreach ($chars as $char) {
    $eachLine[$i] = explode("\n", $char);
    $noLines = count($eachLine[$i]);
    if ($noLines > $maxLines) 
      { $maxLines = $noLines; }
    $i++;
  }
  
  // Join all images into one array, and add empty space to align everything
  for ($i=0; $i<$maxLines; $i++) {$result[$i] = ''; };
  for ($i=0; $i< count($chars); $i++) {
    $currLines      = count ($eachLine[$i]) - 1; // Last line is empty
    $j=0; $maxChars = strlen($eachLine[$i][0]);
    while ($j < $currLines) {
      $result[$j] .= $eachLine[$i][$j];
      $j++;
    }
    while ($j <  $maxLines) {
      $result[$j] .= str_repeat(" ", $maxChars);
      $j++;
    }
  }
  
  // Add some random noise here and there ;-)
  return implode("\n", $result);
}


// Returns a ascii art image of a random phrase, and the phrase
// Inputs $phrlen as length of phrase
function generate_captcha($phrlen)
{
  global $FigletFontdir; 
  
  // Try locating a figlet font directory
  if (is_dir($FigletFontdir)) {
    // Everything is swell. User has specified a working directory
    $filelist = get_figlet_fontlist($FigletFontdir, "flf");   
  } else if ($FigletFontdir[0] != '/') {
    // Search in ini-paths
    foreach (explode(':', ini_get("include_path")) as $path) {
      if (!preg_match('|/$|', $path)) 
        { $path .= '/'; }
      if (is_dir($path . $FigletFontdir)) {
         $filelist = get_figlet_fontlist($path . $FigletFontdir, "flf");
         break; // Found what we're looking for, so bail out
      }
    }
  }
  
  $phrase = generate_phrase($phrlen);
  
  if( is_array($filelist) && (strlen($phrase) == $phrlen) )
  {
    $randstr_array = array(); // Holds ascii art components of phrase
    
    $phpFiglet = new phpFiglet(); // instantiate phpFiglet class
    
    /* 
    Create the components for an ascii art phrase of the specified length.
    Use a random ascii art font and a random letter or number for each component of this phrase.
    */
    // Build array of ascii art characters, based on characters in $phrase
    // Random font for each letter
    for ($i = 0; $i < $phrlen ; $i++) 
    {
      // Choose random ascii art font
      $font_index = array_rand($filelist);
      $randomfont = $filelist[$font_index];
      
      if ($phpFiglet->loadFont($randomfont)) 
      {
        $randstr_array[] = $phpFiglet->ret_display(substr($phrase, $i, 1));
      } 
      else 
      {
        //**** function only availabe from php 4.0.1... What is our demand on php version
        trigger_error("Could not load font file: $font_index");
      }
    }
    
    $preRandstr = join_ascii_images($randstr_array);
    return array($preRandstr, $phrase);
  }
  else
  {
    //**** function only availabe from php 4.0.1... What is our demand on php version
    trigger_error("Could not generate captcha-phrase");  
    echo "<p><strong>Could not generate captcha-phrase</strong></p>";  
  }
}

?>