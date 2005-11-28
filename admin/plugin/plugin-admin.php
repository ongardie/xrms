<?php
/**
 * Plugin Administration
 *
 * This page reads and writes the $include_location.oplugin-cfg.php
 * file and sets which plugins are enabled.
 *
 * Portions Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Remainder copyright 2004 XRMS Development Team
 *
 * Modified for XRMS by Brian Peterson
 *
 * @author Philippe Mingo
 * @author Brian Peterson
 *
 * $Id: plugin-admin.php,v 1.7 2005/11/28 18:46:16 daturaarutad Exp $
 * @package xrms
 * @subpackage plugins
 */

// include common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'plugin.php');

$session_user_id = session_check( 'Admin' );

$plugin_submit = isset($_POST['plugin_submit']) ? $_POST['plugin_submit'] : false;
if ($plugin_submit=='true') {
    $plg = $_POST ['plg'];
}

/**
 * parse the config file
 *
 * @param $cfg_file path and filename to the config file
 *
 * @return
 */
function parseConfig( $cfg_file ) {

    global $newcfg;

    $cfg = file( $cfg_file );
    $mode = '';
    $l = count( $cfg );
    $modifier = FALSE;

    for ($i=0;$i<$l;$i++) {
        $line = trim( $cfg[$i] );
        $s = strlen( $line );
        for ($j=0;$j<$s;$j++) {
            switch ( $mode ) {
            case '=':
                if ( $line{$j} == '=' ) {
                    // Ok, we've got a right value, lets detect what type
                    $mode = 'D';
                } else if ( $line{$j} == ';' ) {
                    // hu! end of command
                    $key = $mode = '';
                }
                break;
            case 'K':
                // Key detect
                if( $line{$j} == ' ' ) {
                    $mode = '=';
                } else {
                    $key .= $line{$j};
                }
                break;
            case ';':
                // Skip until next ;
                if ( $line{$j} == ';' ) {
                    $mode = '';
                }
                break;
            case 'S':
                if ( $line{$j} == '\\' ) {
                    $value .= $line{$j};
                    $modifier = TRUE;
                } else if ( $line{$j} == $delimiter && $modifier === FALSE ) {
                    // End of string;
                    $newcfg[$key] = $value . $delimiter;
                    $key = $value = '';
                    $mode = ';';
                } else {
                    $value .= $line{$j};
                    $modifier = FALSE;
                }
                break;
            case 'N':
                if ( $line{$j} == ';' ) {
                    $newcfg{$key} = $value;
                    $key = $mode = '';
                } else {
                    $value .= $line{$j};
                }
                break;
            case 'C':
                // Comments
                if ( $s > $j + 1  &&
                     $line{$j}.$line{$j+1} == '*/' ) {
                    $mode = '';
                    $j++;
                }
                break;
            case 'D':
                // Delimiter detect
                switch ( $line{$j} ) {
                case '"':
                case "'":
                    // Double quote string
                    $delimiter = $value = $line{$j};
                    $mode = 'S';
                    break;
                case ' ':
                    // Nothing yet
                    break;
                default:
                    if ( strtoupper( substr( $line, $j, 4 ) ) == 'TRUE'  ) {
                        // Boolean TRUE
                        $newcfg{$key} = 'TRUE';
                        $key = '';
                        $mode = ';';
                    } else if ( strtoupper( substr( $line, $j, 5 ) ) == 'FALSE'  ) {
                        $newcfg{$key} = 'FALSE';
                        $key = '';
                        $mode = ';';
                    } else {
                        // Number or function call
                        $mode = 'N';
                        $value = $line{$j};
                    }
                }
                break;
            default:
                if ( $line{$j} == '$' ) {
                    // We must detect $key name
                    $mode = 'K';
                    $key = '$';
                } else if ( $s < $j + 2 ) {
                } else if ( strtoupper( substr( $line, $j, 7 ) ) == 'GLOBAL ' ) {
                    // Skip untill next ;
                    $mode = ';';
                    $j += 6;
                } else if ( $line{$j}.$line{$j+1} == '/*' ) {
                    $mode = 'C';
                    $j++;
                } else if ( $line{$j} == '#' || $line{$j}.$line{$j+1} == '//' ) {
                    // Delete till the end of the line
                    $j = $s;
                }
            }
        }
    }
}

/* ---------------------- main -------------------------- */
/**
 * content for the page itself goes here
 */

$cfgfile = $include_directory.'/plugin-cfg.php';

$page_title = _("Plugin Administration");

$newcfg = array();

ob_start();

echo "<p><br><br>\n<form method=post name=plugin-admin>\n"
    . "<table align=center cellspacing=0>\n"
    . "<input type=hidden name=plugin_submit value=true>\n";
    //. "<tr><th colspan=2>" . _("Plugin Administration") . "</th></tr>",

/* Special Plugins Block */

// parseConfig( $include_directory . 'config/config_default.php' );
parseConfig( $cfgfile );

// echo '<html><table>';
echo "<tr><th colspan=2>" .
     _("Plugins") . "</th></tr>\n";


  $plugpath = $xrms_file_root . '/plugins/';
  if ( file_exists($plugpath) ) {
      $fd = opendir( $plugpath );
      $op_plugin = array();
      $p_count = 0;
      while (false !== ($file = readdir($fd))) {
        if ($file != '.' && $file != '..' && $file != 'CVS' && is_dir($plugpath . $file) ) {
            $op_plugin[] = $file;
            $p_count++;
        }
      }
      closedir($fd);
      asort( $op_plugin );

      /* Lets get the plugins that are active */
      $plugins = array();
      if ( getGlobalVar( $v, 'plg' ) ) {
        foreach ( $op_plugin as $plg ) {
            if (  getGlobalVar( $v, "plgs_$plg" ) && $v == 'on' ) {
                $plugins[] = $plg;
            }
        }
        $i = 0;
        foreach ( $plugins as $plg ) {
            $k = "\$plugins[$i]";
            $newcfg[$k] = "'$plg'";
            $i++;
        }
        while ( isset( $newcfg["\$plugins[$i]"] ) ) {
            $k = "\$plugins[$i]";
            $newcfg[$k] = '';
            $i++;
        }
      } else {
        $i = 0;
        while ( isset( $newcfg["\$plugins[$i]"] ) ) {
            $k = "\$plugins[$i]";
            $v = $newcfg[$k];
            $plugins[] = substr( $v, 1, strlen( $v ) - 2 );
            $i++;
        }
      }
      echo "<tr><td colspan=2><input type=hidden name=plg value=on><center><table></td></tr>\n";
      foreach ( $op_plugin as $plg ) {
        if ( in_array( $plg, $plugins ) ) {
            $sw = ' checked';
        } else {
            $sw = '';
        }
        echo '<tr>' .
             "<td>$plg</td><td><input$sw type=checkbox name=plgs_$plg></td>".
             "</tr>\n";
      }
      echo '<tr><td><input type=submit value='._("Submit").'></td></tr>';
      echo '</table>';
  } else {
      echo '<tr><td colspan=2 align="center">'._("Plugin directory could not be found: ") . $plugpath . "</td></tr>\n";
  }

if ($plugin_submit == 'true') {
    /*    Write the options to the file.  */
    if( $fp = @fopen( $cfgfile, 'w' ) ) {
        fwrite( $fp, "<?php\n"
                . "/*** Plugins ***/\n"
		. "if ( !defined('IN_XRMS') )"
                . "    {"
		. "        die('Hacking attempt');"
		. "        exit;"
		. "    }"
		. "\n"
                . "/**\n"
                . " * To install plugins, just add elements to this array that have\n"
                . " * the plugin directory name relative to the /plugins/ directory.\n"
                . " * For instance, for the 'clock' plugin, you'd put a line like\n"
                . " * the following.\n"
                . ' *    $plugins[0] = "clock";'."\n"
                . ' *    $plugins[1] = "inventory";'."\n"
                . " *\n"
                . " * Normally, this file is generated by admin/plugin/plugin-admin.php\n"
                . " */\n\n"
                . "// Add list of enabled plugins here \n" );

        foreach ( $newcfg as $k => $v ) {
            if ( $k{0} == '$' && $v <> '' ) {
                if ( substr( $k, 1, 11 ) == 'ldap_server' ) {
                    $v = substr( $v, 0, strlen( $v ) - 1 ) . "\n)";
                    $v = str_replace( 'array(', "array(\n\t", $v );
                    $v = str_replace( "',", "',\n\t", $v );
                }
                fwrite( $fp, "$k = $v;\n" );
            }
        }
        fwrite( $fp, '?>' );
        fclose( $fp );
        $status_msg =  _("Config written successfully.");
    } else {
        $status_msg =  _("Config file can't be opened. Please check vars.php.");
    }
} //end write check
echo '</table></form></p>';

$output = ob_get_contents();
ob_end_clean();



start_page($page_title, true, $status_msg);
echo $output;


/**
 * $Log: plugin-admin.php,v $
 * Revision 1.7  2005/11/28 18:46:16  daturaarutad
 * move status message to top of page
 *
 * Revision 1.6  2004/08/04 10:54:33  cpsource
 * - Resolve undefined usage of plugin_submit
 *   Fix bug 1001879 to add hacking check to generated file.
 *
 * Revision 1.5  2004/07/29 23:50:03  maulani
 * - update html to fix table row entry and general formatting
 *
 * Revision 1.4  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/07/16 13:51:59  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.2  2004/06/28 13:55:06  gpowers
 * - HTML layout changes, to improve rendering
 *   - removed outer of two nested tables
 *   - enclosed <form> and <table> inside of <p>
 *
 * Revision 1.1  2004/03/21 18:14:05  braverock
 * Initial Revision of Plugin Administration page
 *
 */
?>
