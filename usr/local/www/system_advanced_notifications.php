<?php
/* $Id$ */
/*
	system_advanced_notifications.php
	part of pfSense
	Copyright (C) 2009 Scott Ullrich <sullrich@gmail.com>

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/
/*
	pfSense_MODULE:	system
*/

##|+PRIV
##|*IDENT=page-system-advanced-notifications
##|*NAME=System: Advanced: Tunables page
##|*DESCR=Allow access to the 'System: Advanced: Tunables' page.
##|*MATCH=system_advanced-sysctrl.php*
##|-PRIV

require("guiconfig.inc");
require("notices.inc");

// Growl
if($config['notifications']['growl']['password']) 
	$pconfig['password'] = $config['notifications']['growl']['password'];
if($config['notifications']['growl']['ipaddress']) 
	$pconfig['ipaddress'] = $config['notifications']['growl']['ipaddress'];

// SMTP
if($config['notifications']['smtp']['ipaddress']) 
	$pconfig['smtpipaddress'] = $config['notifications']['smtp']['ipaddress'];
if($config['notifications']['smtp']['notifyemailaddress']) 
	$pconfig['smtpnotifyemailaddress'] = $config['notifications']['smtp']['notifyemailaddress'];

if ($_POST) {

	unset($input_errors);
	$pconfig = $_POST;

	/* if this is an AJAX caller then handle via JSON */
	if (isAjax() && is_array($input_errors)) {
		input_errors2Ajax($input_errors);
		exit;
	}

	if ($_POST['apply']) {
		$retval = 0;
		system_setup_sysctl();		
		$savemsg = get_std_save_message($retval);
	}

	if ($_POST['Submit'] == "Save") {
		$tunableent = array();

		// Growl
		$config['notifications']['growl']['ipaddress'] = $_POST['ipaddress'];
		$config['notifications']['growl']['password'] = $_POST['password'];

		// SMTP
		$config['notifications']['smtp']['ipaddress'] = $_POST['smtpipaddress'];
		$config['notifications']['smtp']['notifyemailaddress'] = $_POST['smtpnotifyemailaddress'];

		write_config();

		// Send test message via growl
		register_via_growl();
		notify_via_growl("This is a test message form pfSense.  It is safe to ignore this message.");

		// Send test message via smtp
		notify_via_smtp("This is a test message form pfSense.  It is safe to ignore this message.");

		pfSenseHeader("system_advanced_notifications.php");
		exit;
    }
}

include("head.inc");

$pgtitle = array("System","Advanced: Notifications");
include("head.inc");

?>

<body link="#0000CC" vlink="#0000CC" alink="#0000CC">
<?php include("fbegin.inc"); ?>
	<form action="system_advanced_notifications.php" method="post">
		<?php
			if ($input_errors)
				print_input_errors($input_errors);
			if ($savemsg)
				print_info_box($savemsg);
		?>
	</form>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<?php
					$tab_array = array();
					$tab_array[] = array("Admin Access", false, "system_advanced_admin.php");
					$tab_array[] = array("Firewall / NAT", false, "system_advanced_firewall.php");
					$tab_array[] = array("Networking", false, "system_advanced_network.php");
					$tab_array[] = array("Miscellaneous", false, "system_advanced_notifications.php");
					$tab_array[] = array("System Tunables", false, "system_advanced_sysctl.php");
					$tab_array[] = array("Notifications", true, "system_advanced_notifications.php");
					display_top_tabs($tab_array);
				?>
			</td>
		</tr>
		<tr>
			<td id="mainarea">
				<div class="tabcont">
					<form action="system_advanced_notifications.php" method="post" name="iform">
					<table width="100%" border="0" cellpadding="6" cellspacing="0">
						<!-- GROWL -->
						<tr>
							<td colspan="2" valign="top" class="listtopic">Growl</td>
						</tr>
						<tr>
							<td width="22%" valign="top" class="vncell">IP Address</td>
							<td width="78%" class="vtable">
								<input name='ipaddress' value='<?php echo $pconfig['ipaddress']; ?>'><br/>
								This is the IP address that you would like to send growl notifications to.
							</td>
						</tr>
						<tr>
							<td width="22%" valign="top" class="vncell">Password</td>
							<td width="78%" class="vtable">
								<input name='password' type='password' value='<?php echo $pconfig['password']; ?>'><br/>
								Enter the password of the remote growl notification device.
							</td>
						</tr>
						<tr>
							<td colspan="2" class="list" height="12">&nbsp;</td>
						</tr>	
						<!-- SMTP -->
						<tr>
							<td colspan="2" valign="top" class="listtopic">SMTP E-Mail</td>
						</tr>
						<tr>
							<td width="22%" valign="top" class="vncell">IP Address of E-Mail server</td>
							<td width="78%" class="vtable">
								<input name='smtpipaddress' value='<?php echo $pconfig['smtpipaddress']; ?>'><br/>
								This is the IP address of the SMTP E-Mail server that will be used to send notifications to.
							</td>
						</tr>
						<tr>
							<td width="22%" valign="top" class="vncell">Notification E-Mail address</td>
							<td width="78%" class="vtable">
								<input name='smtpnotifyemailaddress' type='input' value='<?php echo $pconfig['smtpnotifyemailaddress']; ?>'><br/>
								Enter the e-mail address that you would like email notifications sent to.
							</td>
						</tr>
						<tr>
							<td valign="top" class="">
								&nbsp;
							</td>
							<td>
								<br/>
								<input type='submit' id='Submit' name='Submit' value='Save'></form>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
<?php include("fend.inc"); ?>
</body>
</html>