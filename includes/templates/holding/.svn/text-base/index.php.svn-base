<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Website suspended</title>

</head>

<body bgcolor="#ffffff" background="bg.jpg" marginheight="0" marginwidth="0">
<center>
	<table width="980" border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
    	<tr>
        	<td bgcolor="#2c3f6d" width="143"><img src="/includes/templates/holding/chameleon_logo.jpg" alt="Chameleon Interactive" /></td>
            <td bgcolor="#2c3f6d" width="100%"><img src="/includes/templates/holding/spacer.gif" /></td>
            <td bgcolor="#2c3f6d" width="143"><img src="/includes/templates/holding/spacer.gif" width="143" height="2" /></td>
        </tr>
        <tr>
        	<td bgcolor="#ffffff"></td>
            <td bgcolor="#ffffff" height="800" valign="top">
            	<?php
				switch (strtolower($site->getConfig('holding'))) {
					case 2 :
					case "ip" :
						$holding_page = "restricted-ip";
						break;

					case 1 :
					case "billing" :
					default :
						$holding_page = "billing";
						break;
				}
				//print "switch (".strtolower($site->getConfig('holding')).") hp($holding_page) <br>\n";
				include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/holding/".$holding_page.".html");
				?>
                <p>Tel: 0845 123 5457</p>
                <p><a href="mailto:russell.jones@ichameleon.com">russell.jones@ichameleon.com</a></p>
            </td>
            <td bgcolor="#ffffff"></td>
        </tr>
    </table>
</center>
</body>
</html>
