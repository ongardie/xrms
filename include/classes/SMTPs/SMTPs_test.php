<?php

include 'debug.php';

include 'SMTPs.php';

$objSMTP = new SMTPs ();

/*
$objSMTP->setHost ( 'test' );
$objSMTP->setPort ( 33 );

$objSMTP->setID ( 'walter' );

$objSMTP->setPW ( 'password' );

$objSMTP->setFrom ( 'from@somewhere.tld' );

*/

$objSMTP->setConfig('./SMTPs.ini.php');

$objSMTP->setXheader ( 'X-myHeader: Something Here' );

$objSMTP->setTo ( '"otrWalter" <walter@yahoo.com>, "toA" <to-1Walter@torres.ws>, "toB" <to-2Walter@torres.ws>, harris@torres.ws' );

$objSMTP->setCC ( '"ccBraverock" <ccWalter@torres.ws>, "brian" <brian@braverock.com>' );

$objSMTP->setBCC ( '"Arleen" <bccArleen@torres.ws>' );

$objSMTP->setSubject ( 'an interesting Subject' );

$objSMTP->setBodyContent ( 'This is the TEXT portion of the mixed message.');

/*
$objSMTP->setBodyContent ( '<html><body>
 <p>This is the <b><font color="red">HTML</font> portion</b> of the mixed message.</p>
 </body></html>', 'text/html' );
*/

$objSMTP->sendMsg ();

echo '<hr />Sent';
//do_print_r ( $objSMTP );



?>