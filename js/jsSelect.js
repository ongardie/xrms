// Library of Methods to handle SELECT and OPTIONS Objects


// Set Global Variable to store current List in focus
// This is used throughout the methods defined below
var objFocusList ='';


// Set Focus and remove focus from other List
function setListFocus ( objList )
{
    if ( objList == objFocusList)
        return

    if ( typeof ( objFocusList ) == 'object' )
    {

alert ( objFocusList.id )
alert ( objFocusList.selectedIndex )
alert (objFocusList.options[objFocusList.selectedIndex].selected)

        objFocusList.options[objFocusList.selectedIndex].selected = false;
    }

    objFocusList = objList;
}


// Select a single option
function selectOption ( objList, intSelectItemIndex )
{
    // See if we passed anything in.
    // If not, use the object in focus
    if ( objList )
        objFocusList = objList

    // Do we have a list to deal with?
    if ( ! objFocusList )
        return

    // Move focus and Select Item
    objFocusList.focus();
    objFocusList.options[intSelectItemIndex].selected = true;
}


// Delete a single Item from a Select Option List
function deleteOption ( objList )
{
    // See if we passed anything in.
    // If not, use the object in focus
    if ( objList )
        objFocusList = objList;

    // Do we have a list to deal with?
    if ( ! objFocusList )
        return

    // Which Item are we dealing with
    var intItem = objFocusList.selectedIndex;

    // Kill Selected item, if we have one
    if ( intItem != -1 )
        objFocusList.options[intItem] = null;
}

// Delete ALL the Items from a Select Option List
function deleteAllOption ( objList )
{
    // See if we passed anything in.
    // If not, use the object in focus
    if ( objList )
        objFocusList = objList

    // Do we have a list to deal with?
    if ( ! objFocusList )
        return

    // Kill Selected item
    objFocusList.options.length = 0;
}

// Add a single Item to a Select Option List
function addOption ( strList, strItemText, strItemValue, bolDefaultSelected, bolSelected )
{
    // Which list
    // If not defined, we use current list in focus
    //var objList = ( strList ) ? document.all[strList] : objFocusList;
    var objList = ( strList ) ? document.getElementById(strList) : objFocusList;

    // Do we have a list to deal with?
    if ( ! objList )
    {
        alert ( 'Please highlight List you wish to add this item into' );
        return false
    }

    // See if this item is in the list already
    if ( isDuplicateItem ( objList, strItemText, strItemValue ) )
        return false

    // Add New Item Selected item
    objList.options[objList.options.length] = new Option( strItemText, strItemValue, bolDefaultSelected, bolSelected );

}

function isDuplicateItem ( objList, strItemText, strItemValue )
{
    // declare vars
    var strCurItemText;
    var strCurItemValue;

    // default return value
    var bolIsDub = false

    // loop down list looking for item
    for ( var i = 0; i < objList.options.length; i++ )
    {
        // Get the Selected Items Value and Display Text
        strCurItemText  = objList.options[i].text;
        strCurItemValue = objList.options[i].value;

        // See if text match
        if ( strItemText == strCurItemText )
        {
            alert ( "Sorry, '" + strCurItemText + "' is a duplicate item." );
            bolIsDub = true;
        }

        // See if item strCurItemValue
        else if ( strItemValue == strCurItemValue )
        {
            strErrText  = "Sorry, '" + strCurItemValue + "' is a duplicate item value.\n";
            strErrText += "This value belongs to '" + strCurItemText + "'";

            alert ( strErrText );
            bolIsDub = true;
        }

        // if we found a dublicate, don't process any longer
        if ( bolIsDub )
            break;
    }

    // send back condition
    return bolIsDub
}




function moveListItemAll( strFromList, strToList )
{
    // Do we have both to work with?
    if ( ( ( strFromList == null ) || ( strFromList == '' ) ) ||
         ( ( strToList   == null ) || ( strToList   == '' ) )  )
        return

    var objFromList = document.getElementById(strFromList);
    var objToList = document.getElementById(strToList);

    for (i = 0; i < objFromList.options.length; i++)
    {
    	addOption ( strToList, objFromList.options[i].text, objFromList.options[i].value )
    }

    // Remove item in old list
    deleteAllOption (objFromList );

    // Set focus over to new list
    selectOption ( objToList, objToList.options.length - 1)
}

function moveListItem( strFromList, strToList )
{
    // Do we have both to work with?
    if ( ( ( strFromList == null ) || ( strFromList == '' ) ) ||
         ( ( strToList   == null ) || ( strToList   == '' ) )  )
        return

    // Define where the Item is coming from
    var objFromList = document.getElementById(strFromList);

    // Determine if anything was selected, otherwise bale...
    if ( objFromList.selectedIndex == -1 )
        return

    // Define where the Item is going to
    var objToList = document.getElementById(strToList);
    
    // Place item in new list
    addOption ( strToList, objFromList.options[objFromList.selectedIndex].text, objFromList.options[objFromList.selectedIndex].value )
    
    // Remove item in old list
    deleteOption ( );

//alert ( objToList.options.length ); 
//alert ( objToList.selectedIndex );
    // Set focus over to new list
    selectOption ( objToList, objToList.options.length - 1)
}   




// Originator: Zay Perez <zay_perez@hotmail.com>
// From Javascript mailing list - javascript@p2p.wrox.com
// Highly modified by: Walter Torres <walter@torres.ws>

function shiftListItem( strDirection, strList )
{
    // Do we have a list to deal with?
    if ( ! objFocusList )
        return

    // Set vars
    var intDirection;
    var strStoreValue;
    var bale = true;


    // Do we have anything to work with?
    if ( objFocusList.selectedIndex == -1 )
        return

    else
        // What is our item index
        var strItemIndex = objFocusList.selectedIndex;

    // Which Item are we dealing with
    var objOptionItem = objFocusList.options[strItemIndex];

    // Set direction value
    if ( strDirection == 'up' )
    {
        // Set direction to go UP
        intDirection = -1;
        // if its first, do nothing
        bale = (strItemIndex == 0 ) ? true : false;
    }

    else if ( strDirection == 'dn' )
    {
        // Set direction to go DOWN
        intDirection = 1;
        // if its last, do nothing
        bale = ( strItemIndex == objFocusList.options.length - 1 ) ? true : false;
    }

    // We have nothing to do
    if ( bale )
        return

    // Get the value of the current item
    var objOptionItem = new Object();
        objOptionItem = objFocusList.options[strItemIndex];

    // Get the value of the next/previous item
    var objNextOptionItem = new Object();
        objNextOptionItem.text = objFocusList.options[strItemIndex + intDirection].text;
        objNextOptionItem.value = objFocusList.options[strItemIndex + intDirection].value;

    // Swap item with next/previous item
    objFocusList.options[strItemIndex + intDirection].text = objOptionItem.text;
    objFocusList.options[strItemIndex + intDirection].value = objOptionItem.value;

    objFocusList.options[strItemIndex].text = objNextOptionItem.text;
    objFocusList.options[strItemIndex].value = objNextOptionItem.value;

    // Now select our "new" item
    objFocusList.selectedIndex = strItemIndex + intDirection;
}

// Disable a SELECT List
// by: Walter Torres
function disableSelect ( objList )
{
    // Disable list!
    objList.disabled = true;
}

// Enable a SELECT List
// by: Walter Torres
function enableSelect ( objList )
{
    // Disable list!
    objList.disabled = false;
}

function addSep( strListName )
{
    // Which list
    // If not defined, we use current list in focus
    var objList = ( strListName ) ? document.getElementById(strListName) : objFocusList;

    // Do we have a list to deal with?
    if ( ! objList )
        return

    // Default value
    var intLabelLength = 0;

    for (i = 0; i < objList.options.length; i++)
    {
        // Storelength forfuturecompare
        tmpvalue = objList.options[i].text.length;

        //Keep only if larger then previous
        if ( tmpvalue > intLabelLength )
            intLabelLength = tmpvalue;
    }

    //Build seperator based upon longest text in list
    var strSeperator = '';
    for (i = 0; i < intLabelLength; i++)
        strSeperator += '-';

    // Place new seperator in list
    addOption ( strListName, strSeperator, 0 );
}


// ===============================================================
// ===============================================================

// Debugging functions
function setDisplay( objList )
{
    // What item is selected
    var intSelectedItem = objList.selectedIndex

    // If we have nothing, just bale...
    if ( intSelectedItem == -1 )
        return

//  // Get the Selected Items Value and Display Text
    var strSelectedText  = objList.options[intSelectedItem].text;
    var strSelectedValue = objList.options[intSelectedItem].value;

    // Now place this info in the text boxes...
    //document.all.itemText.value  = strSelectedText;
    //document.all.itemValue.value = strSelectedValue;

	//document.getElementById('itemText').value = strSelectedText;
	//document.getElementById('itemValue').value = strSelectedValue;


};


// eof
