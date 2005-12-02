<?php
/* fckeditor.php
*
* Extends the HTML_QuickForm_element to utilize the FCKEditor for WYWIWYG editing
*
*
* $Id: ADOdb_QuickForm_fckeditor.php,v 1.2 2005/12/02 19:24:35 daturaarutad Exp $
*/

require_once("HTML/QuickForm/element.php");
require_once("ADOdb_QuickForm_config.php");


global $fckeditor_location;
include("$fckeditor_location/fckeditor.php") ;







/**
 * HTML class for a fckeditor type field
 * 
 * @author       Justin Cooper <justin@braverock.com>
 * @version      1.0
 * @access       public
 */
class HTML_QuickForm_fckeditor extends HTML_QuickForm_element
{
    // {{{ properties

    /**
     * Field value
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_value = null;

    // }}}
    // {{{ constructor
        
    /**
     * Class constructor
     * 
     * @param     string    Input field name attribute
     * @param     mixed     Label(s) for a field
     * @param     mixed     Either a typical HTML attribute string or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_fckeditor($elementName=null, $elementLabel=null, $attributes=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'fckeditor';
    } //end constructor
    
    // }}}
    // {{{ setName()

    /**
     * Sets the input field name
     * 
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        $this->updateAttributes(array('name'=>$name));
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->getAttribute('name');
    } //end func getName

    // }}}
    // {{{ setValue()

    /**
     * Sets value for fckeditor element
     * 
     * @param     string    $value  Value for fckeditor element
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setValue($value)
    {
        $this->_value = $value;
    } //end func setValue
    
    // }}}
    // {{{ getValue()

    /**
     * Returns the value of the form element
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getValue()
    {
        return $this->_value;
    } // end func getValue

    // }}}
    // {{{ setWrap()

    /**
     * Sets wrap type for fckeditor element
     * 
     * @param     string    $wrap  Wrap type
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setWrap($wrap)
    {
        $this->updateAttributes(array('wrap' => $wrap));
    } //end func setWrap
    
    // }}}
    // {{{ setRows()

    /**
     * Sets height in rows for fckeditor element
     * 
     * @param     string    $rows  Height expressed in rows
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setRows($rows)
    {
        $this->updateAttributes(array('rows' => $rows));
    } //end func setRows

    // }}}
    // {{{ setCols()

    /**
     * Sets width in cols for fckeditor element
     * 
     * @param     string    $cols  Width expressed in cols
     * @since     1.0
     * @access    public
     * @return    void
     */ 
    function setCols($cols)
    {
        $this->updateAttributes(array('cols' => $cols));
    } //end func setCols

    // }}}
    // {{{ toHtml()

    /**
     * Returns the fckeditor element in HTML
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {

            global $fckeditor_location_url;
            global $fckeditor_height;

            $oFCKeditor = new FCKeditor($this->_attributes['name']) ;
    
            $oFCKeditor->BasePath   = $fckeditor_location_url;
            $oFCKeditor->Value      = $this->_value;

            if($fckeditor_height) {
                $oFCKeditor->Height = $fckeditor_height;
            }

            $editor = $oFCKeditor->CreateHtml() ;
    
            return $this->_getTabs() . $editor;
        }
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags (in this case, value is changed to a mask)
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getFrozenHtml()
    {
        if ($this->getAttribute('show_html') == 'off') {
            $value = htmlspecialchars($this->getValue());
        } else {
            $value = $this->getValue();
        }
        if ($this->getAttribute('wrap') == 'off') {
            $html = $this->_getTabs() . '<pre>' . $value."</pre>\n";
        } else {
            $html = nl2br($value)."\n";
        }
        return $html . $this->_getPersistantData();
    } //end func getFrozenHtml

    // }}}

} //end class HTML_QuickForm_textarea
?>
