<?php
//$email_template_nav='<div id=Sidebar>';
$email_template_nav.=<<<TILLEND
<table class=widget>
    <tr><td class=widget_header>Manage Templates</td></tr>
    <tr><td class=widget_content>
        <a href="email_template_list.php">Manage Email Templates</a><br>
        <a href="email_template_type_list.php">Manage Email Template Types</a>
    </td></tr>
</table>
TILLEND;
//$email_template_nav.='</div>';
echo $email_template_nav;
?>