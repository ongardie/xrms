<?

//this page contains the variables which are used by the web-form plugin for XRMS

//VARIABLES

//If no company is provided by the form, do I add a new company with the name <First Name><Last Name>?
//useful for website contacts with the public.
$add_person_as_company=true;

//Do i mail the form to an administrator?

$email_to_admin=true;

$email_admin_address="youremail@email.com";

//Do i respond with a template?

$email_respond=true;

$email_to_address=$_POST['email'];

$email_template_id=1;//blank template

$email_template_from="youremail@email.com";

//Do I add an activity automatically? If so, what sort of activity and who by?

$add_activity=true;

$activity_type_id=4;//email from

$activity_status="o";

$activity_description = $_POST['YourMessage'];

$activity_title="Website Email..".substr($activity_description,0,10);

$response_time='+2 DAYS';//the time you have in PLAIN ENGLISH to respond to an email

//Do I add an opportunity? If so, what sort and how much etc?

$add_opportunity=true;

$add_opportunity_status_id=1;//new

$add_opportunity_title="Membership";

$add_opportunity_description="Membership";

$add_opportunity_probability="20";

$add_opportunity_size="225";




?>