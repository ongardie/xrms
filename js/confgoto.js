/*
 * Ask a 'quest', and if the operator says
 * yes, then goto URL dest
 */
function confGoTo(quest,dest) {
  if ( confirm(quest) ) {
    window.location = dest;
  }
}
/*
 * $Log: confgoto.js,v $
 * Revision 1.1  2004/07/29 09:33:23  cpsource
 * - Handle javascript confGoTo.js
 *
 */
