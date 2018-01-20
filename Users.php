<?php
class Users {
	public $settings = array(
		'admin_menu_category' => 'General',
		'admin_menu_name' => 'Users',
		'description' => 'Shows admins a list of users and allows editing the settings of users.',
		'admin_menu_icon' => '<i class="icon-group"></i>',
		'permissions' => array(
			'Users_Verify',
			'Users_Login_As_User',
			'Users_Assign_Credit',
			'Users_Remove_Credit'
		) ,
	);
	function admin_area() {
		global $billic, $db;
		if (isset($_GET['ID'])) {
			$user_row = $db->q('SELECT * FROM `users` WHERE `id` = ?', $_GET['ID']);
			$user_row = $user_row[0];
			if (empty($user_row)) {
				err('User does not exist');
			}
			if (isset($_GET['AjaxPage'])) {
				$billic->disable_content();
				if ($_GET['AjaxPage'] == 'Account') {
					echo '<form method="POST">';
					echo '<table class="table table-striped"><tr><th colspan="4">Account Information</th></tr>';
					echo '<tr>';
					echo '<td style="width: 10%">First Name</td><td style="width: 40%"><input type="text" class="form-control" name="firstname" value="' . safe($user_row['firstname']) . '"></td>';
					echo '<td style="width: 10%">Address 1</td><td style="width: 40%"><input type="text" class="form-control" name="address1" value="' . safe($user_row['address1']) . '"></td>';
					echo '</tr><tr>';
					echo '<td>Last Name</td><td><input type="text" class="form-control" name="lastname" value="' . safe($user_row['lastname']) . '"></td>';
					echo '<td>Address 2</td><td><input type="text" class="form-control" name="address2" value="' . safe($user_row['address2']) . '"></td>';
					echo '</tr><tr>';
					echo '<td>Email</td><td><input type="text" class="form-control" name="email" value="' . safe($user_row['email']) . '"></td>';
					echo '<td>City</td><td><input type="text" class="form-control" name="city" value="' . safe($user_row['city']) . '"></td>';
					echo '</tr><tr>';
					echo '<td>Company Name</td><td><input type="text" class="form-control" name="companyname" value="' . safe($user_row['companyname']) . '"></td>';
					echo '<td>State</td><td><input type="text" class="form-control" name="state" value="' . safe($user_row['state']) . '"></td>';
					echo '</tr><tr>';
					echo '<td>VAT Number</td><td><input type="text" class="form-control" name="vatnumber" value="' . safe($user_row['vatnumber']) . '"></td>';
					echo '<td>Post Code</td><td><input type="text" class="form-control" name="postcode" value="' . safe($user_row['postcode']) . '"></td>';
					echo '</tr><tr>';
					echo '<td>Phone Number</td><td><input type="text" class="form-control" name="phonenumber" value="' . safe($user_row['phonenumber']) . '"></td>';
					echo '<td' . $billic->highlight('country') . '>Country:</td><td><select class="form-control" name="country">';
					foreach ($billic->countries as $key => $country) {
						echo '<option value="' . $key . '"' . ($key == $user_row['country'] ? ' selected="1"' : '') . '>' . $country . '</option>';
					}
					echo '</select></td>';
					echo '</tr><tr>';
					echo '<td>Password</td><td><input type="text" class="form-control" name="password" value="Edit to Change Password" onblur="if(this.value==\'\') this.value=\'Edit to Change Password\';" onFocus="if(this.value==\'Edit to Change Password\') this.value=\'\';"></td>';
					echo '</tr>';
					echo '<tr><td>Notes</td><td colspan="3"><textarea name="notes" class="form-control">' . safe($user_row['notes']) . '</textarea></td></tr>';
					echo '</table>';
					echo '<table class="table table-striped"><tr><th colspan="4">Registration Information</th></tr>';
					echo '<tr><td style="width: 10%">User ID</td><td style="width: 40%">#' . $user_row['id'] . '</td>';
					echo '<td style="width: 10%">Date Registered</td><td style="width: 40%">' . date('j\<\s\u\p\>S\<\/\s\u\p\> F Y H:i', $user_row['datecreated']) . '</td></tr>';
					echo '<tr><td>Registered IP</td><td>' . $user_row['registered_ip'] . '</td>';
					echo '<td>Hostname</td><td>' . $user_row['registered_host'] . '</td></tr>';
					echo '</table>';
					echo '<table class="table table-striped"><tr><th colspan="2">Account Settings</th></tr>';
					if ($billic->module_exists('DiscountTiers')) {
						$billic->module('DiscountTiers');
						echo '<tr><td style="width: 15%">Discount Tier</td><td>' . $billic->modules['DiscountTiers']->calc_discount_tier($user_row) . '%</td></tr>';
					}
					echo '<tr><td>Discount Overide</td><td><div class="input-group"><input type="text" name="discount" class="form-control" value="' . safe($user_row['discount']) . '"><div class="input-group-addon">%</div></div></td></tr>';
					echo '<tr><td>Credit</td><td>' . get_config('billic_currency_prefix') . $user_row['credit'] . get_config('billic_currency_suffix') . '</td></tr>';
					echo '<tr><td>Status</td><td><select class="form-control" name="status">';
					echo '<option value="Active"' . ($user_row['status'] == 'Active' ? ' selected' : '') . '>Active</option>';
					echo '<option value="activation"' . ($user_row['status'] == 'activation' ? ' selected' : '') . '>Awaiting Activation</option>';
					echo '</select></td></tr>';
					echo '<tr><td>Verified?</td><td><select class="form-control" name="verified"><option value="0"' . ($user_row['verified'] == 0 ? ' selected' : '') . '>No, restrict payment gateways.</option><option value="1"' . ($user_row['verified'] == 1 ? ' selected' : '') . '>Yes, allow any payment.</option><option value="2"' . ($user_row['verified'] == 2 ? ' selected' : '') . '>If fraud risk low, allow any payment.</option></select></td></tr>';
					echo '<tr><td>Auto Renew</td><td><input type="checkbox" name="auto_renew" value="1"' . ($user_row['auto_renew'] == '1' ? ' checked' : '') . '> Automatically pay new invoices using your account credit.</td></tr>';
					echo '<tr><td>Opt-out Mass Emails</td><td><input type="checkbox" name="emailoptout" value="1"' . ($user_row['emailoptout'] == '1' ? ' checked' : '') . '> Prevents the client from receiving email from the Mass Email module. Unsubscribe method.</td></tr>';
					echo '<tr><td>Block Orders</td><td><input type="checkbox" name="blockorders" value="1"' . ($user_row['blockorders'] == '1' ? ' checked' : '') . '> Prevents the client from ordering any new services. Renewal invoices will not be generated either.</td></tr>';
					echo '<tr><td>Monthly Summary</td><td><input type="checkbox" name="monthly_summary" value="1"' . ($user_row['monthly_summary'] == '1' ? ' checked' : '') . '> Sends a report to the user which tells them the total value of invoices and tax paid every month.</td></tr>';
					echo '</table>';
					echo '<div align="center"><input type="submit" class="btn btn-success" name="account_update" value="Update Client Information &raquo;"></div></form>';
					echo '<h2>Credit History</h2>';
					echo '<table class="table table-striped"><tr><th>ID</th><th>Date</th><th>Description</th><th>Amount</th><th>Invoice</th><th>Actions</th></tr>';
					$credits = $db->q('SELECT * FROM `logs_credit` WHERE `clientid` = ? ORDER BY `date` DESC', $user_row['id']);
					if (empty($credits)) {
						echo '<tr><td colspan="20">No credit history.</td></tr>';
					}
					foreach ($credits as $credit) {
						echo '<tr><td>' . $credit['id'] . '</td><td>' . $billic->date_display($credit['date']) . '</td><td>' . $credit['description'] . '</td><td>' . get_config('billic_currency_prefix') . $credit['amount'] . get_config('billic_currency_suffix') . '</td><td>';
						if (empty($credit['invoiceid'])) {
							echo 'N/A';
						} else {
							echo '<a href="/Admin/Invoices/ID/' . $credit['invoiceid'] . '/">' . $credit['invoiceid'] . '</a>';
						}
						echo '</td><td>';
						if ($credit['removalid'] == 0 && $credit['invoiceid'] == 0 && $billic->user_has_permission($billic->user, 'Users_Remove_Credit')) {
							echo '<a href="/Admin/Users/ID/' . $user_row['id'] . '/Hook/RemoveCredit/CreditID/' . $credit['id'] . '/"><i class="icon-remove"></i> Remove</a>';
						}
						echo '</td></tr>';
					}
					echo '</table>';
					exit;
				}
				if (!$billic->user_has_permission($billic->user, $_GET['AjaxPage'])) {
					err('You do not have permission to view this page');
				}
				$billic->module($_GET['AjaxPage']);
				$billic->enter_module($_GET['AjaxPage']);
				$billic->modules[$_GET['AjaxPage']]->users_submodule(array(
					'user' => $user_row,
				));
				$billic->exit_module();
				exit;
			}
			$billic->set_title($user_row['firstname'] . ' ' . $user_row['lastname']);
			if ($_GET['Login'] == 'Yes' && $billic->user_has_permission($billic->user, 'Users_Login_As_User')) {
				if ($billic->user_has_permission($user_row, 'admin') || $billic->user_has_permission($user_row, 'superadmin')) {
					err('Unable to login to another user with admin permissions');
				}
				$_SESSION['userid'] = $user_row['id'];
				$_SESSION['GoogleAuthenticator'] = true;
				$_SESSION['adminid'] = $billic->user['id'];
				$billic->redirect('/Services/');
			}
			if ($_GET['Hook'] == 'NewInvoice' && $billic->user_has_permission($billic->user, 'Invoices_New')) {
				$id = $db->insert('invoices', array(
					'userid' => $user_row['id'],
					'date' => time() ,
					'duedate' => time() ,
					'status' => 'Unpaid',
				));
				$billic->redirect('/Admin/Invoices/ID/' . $id . '/');
			}
			if ($_GET['Hook'] == 'NewTicket' && $billic->user_has_permission($billic->user, 'Tickets_New')) {
				$title = 'New Ticket';
				$billic->set_title($title);
				echo '<h1>' . $title . '</h1>';
				$services = array();
				$servicestmp = $db->q('SELECT `id`, `username`, `domain`, `domainstatus` FROM `services` WHERE `userid` = ?', $user_row['id']);
				foreach ($servicestmp as $service) {
					$services[$service['id']] = $service['id'] . ' ' . $service['domainstatus'] . ' ' . $service['username'] . ' ' . $service['domain'];
				}
				unset($servicestmp);
				$billic->module('FormBuilder');
				$form = array(
					'title' => array(
						'label' => 'Title',
						'type' => 'text',
						'required' => true,
						'default' => '',
					) ,
					'serviceid' => array(
						'label' => 'Service',
						'type' => 'dropdown',
						'required' => false,
						'options' => $services,
					) ,
				);
				if (isset($_POST['Continue'])) {
					$billic->modules['FormBuilder']->check_everything(array(
						'form' => $form,
					));
					if (empty($billic->errors)) {
						$id = $db->insert('tickets', array(
							'userid' => $user_row['id'],
							'date' => time() ,
							'title' => $_POST['title'],
							'serviceid' => $_POST['serviceid'],
							'queue' => 'Support',
							'status' => 'In Progress',
							'clientunread' => 1,
							'adminunread' => 1,
						));
						$billic->redirect('/Admin/Tickets/ID/' . $id . '/');
					}
				}
				$billic->show_errors();
				$billic->modules['FormBuilder']->output(array(
					'form' => $form,
					'button' => 'Continue',
				));
				return;
			}
			if ($_GET['Hook'] == 'AddCredit' && $billic->user_has_permission($billic->user, 'Users_Assign_Credit')) {
				$title = 'Add Credit';
				$billic->set_title($title);
				$billic->module('FormBuilder');
				echo '<h1>' . $title . '</h1>';
				$form = array(
					'description' => array(
						'label' => 'Description',
						'type' => 'text',
						'required' => true,
						'default' => '',
					) ,
					'amount' => array(
						'label' => 'Amount',
						'type' => 'text',
						'required' => true,
						'default' => '',
					) ,
				);
				if (isset($_POST['Continue'])) {
					$billic->modules['FormBuilder']->check_everything(array(
						'form' => $form,
					));
					if (!is_numeric($_POST['amount'])) {
						$billic->error('Amount must be numeric', 'amount');
					}
					if (empty($billic->errors)) {
						$db->insert('logs_credit', array(
							'clientid' => $user_row['id'],
							'date' => time() ,
							'description' => $_POST['description'],
							'amount' => $_POST['amount'],
						));
						$db->q('UPDATE `users` SET `credit` = ? WHERE `id` = ?', ($user_row['credit'] + $_POST['amount']) , $user_row['id']);
						$user_row = $db->q('SELECT * FROM `users` WHERE `id` = ?', $user_row['id']);
						$user_row = $user_row[0];
						$billic->status = 'added';
					}
				}
				$billic->show_errors();
				$billic->modules['FormBuilder']->output(array(
					'form' => $form,
					'button' => 'Continue',
				));
				return;
			}
			if ($_GET['Hook'] == 'RemoveCredit' && $billic->user_has_permission($billic->user, 'Users_Remove_Credit')) {
				$credit = $db->q('SELECT * FROM `logs_credit` WHERE `id` = ?', $_GET['CreditID']);
				$credit = $credit[0];
				if (empty($credit)) {
					err('The credit you are trying to remove does not exist');
				}
				if ($credit['removalid'] > 0) {
					err('The credit has already been removed');
				}
				$removalid = $db->insert('logs_credit', array(
					'clientid' => $user_row['id'],
					'date' => time() ,
					'description' => 'Removal of credit #' . $credit['id'],
					'amount' => '-' . $credit['amount'],
				));
				$db->q('UPDATE `logs_credit` SET `removalid` = ? WHERE `id` = ?', $removalid, $credit['id']);
				$db->q('UPDATE `logs_credit` SET `removalid` = ? WHERE `id` = ?', $removalid, $removalid);
				$db->q('UPDATE `users` SET `credit` = ? WHERE `id` = ?', ($user_row['credit'] - $credit['amount']) , $user_row['id']);
				$user_row = $db->q('SELECT * FROM `users` WHERE `id` = ?', $user_row['id']);
				$user_row = $user_row[0];
				$billic->status = 'updated';
			}
			echo '<img src="' . $billic->avatar($user_row['email'], 100) . '" class="pull-left" style="margin: 5px 5px 5px 0"><h3>' . $user_row['firstname'] . ' ' . $user_row['lastname'] . '' . (empty($user_row['companyname']) ? '' : ' - ' . $user_row['companyname']) . '</h3><div class="btn-group" role="group" aria-label="Account Actions">';
			if ($billic->user_has_permission($billic->user, 'Users_Login_As_User')) {
				echo '<a class="btn btn-default" href="/Admin/Users/ID/' . $user_row['id'] . '/Login/Yes/"><i class="icon-zoom-in"></i> Login as User</a></button>';
			}
			if ($billic->user_has_permission($billic->user, 'Invoices_New')) {
				echo '<a class="btn btn-default" href="/Admin/Users/ID/' . $user_row['id'] . '/Hook/NewInvoice/" onclick="return confirm(\'This will generate a new empty invoice. Are you sure?\');"><i class="icon-tag"></i> New Invoice</a></button>';
			}
			if ($billic->user_has_permission($billic->user, 'Tickets_New')) {
				echo '<a class="btn btn-default" href="/Admin/Users/ID/' . $user_row['id'] . '/Hook/NewTicket/"><i class="icon-ticket"></i> New Ticket</a></button>';
			}
			if ($billic->user_has_permission($billic->user, 'Users_Assign_Credit')) {
				echo '<a class="btn btn-default" href="/Admin/Users/ID/' . $user_row['id'] . '/Hook/AddCredit/"><i class="icon-money-banknote"></i> Add Credit</a></button>';
			}
			echo '</div><div style="clear:both"></div>';
			if (isset($_POST['account_update'])) {
				if (empty($_POST['country']) || !array_key_exists($_POST['country'], $billic->countries)) {
					$billic->error('Country is invalid', 'country');
				}
				if (empty($errors)) {
					if (!empty($_POST['password']) && $_POST['password'] != 'Edit to Change Password') {
						$salt = $billic->rand_str(5);
						$password = md5($salt . $_POST['password']) . ':' . $salt;
						$db->q("UPDATE `users` SET `password` = ? WHERE `id` = ?", $password, $user_row['id']);
						echo '<br><font color="green"><b>Password Changed!</b></font><br>';
					}
					if (!empty($_POST['signature'])) {
						$_POST['signature'] = PHP_EOL . $_POST['signature'];
					}
					$db->q('UPDATE `users` SET `firstname` = ?, `lastname` = ?, `companyname` = ?, `vatnumber` = ?, `email` = ?, `address1` = ?, `address2` = ?, `city` = ?, `state` = ?, `postcode` = ?, `country` = ?, `phonenumber` = ?, `status` = ?, `discount` = ?, `verified` = ?, `emailoptout` = ?, `notes` = ?, `signature` = ?, `blockorders` = ?, `monthly_summary` = ?, `auto_renew` = ? WHERE `id` = ?', $_POST['firstname'], $_POST['lastname'], $_POST['companyname'], $_POST['vatnumber'], $_POST['email'], $_POST['address1'], $_POST['address2'], $_POST['city'], $_POST['state'], $_POST['postcode'], $_POST['country'], $_POST['phonenumber'], $_POST['status'], $_POST['discount'], $_POST['verified'], $_POST['emailoptout'], $_POST['notes'], $_POST['signature'], $_POST['blockorders'], $_POST['monthly_summary'], $_POST['auto_renew'], $user_row['id']);
					$user_row = $db->q('SELECT * FROM `users` WHERE `id` = ?', $_GET['ID']);
					$user_row = $user_row[0];
					$billic->status = 'updated';
				}
			}
			$billic->show_errors();
			echo '<style>#dashboardLoader{left:50%;font-size:25px;margin:5em auto;width:1em;height:1em;border-radius:50%;text-indent:-9999em;-webkit-animation:load4 1.3s infinite linear;animation:load4 1.3s infinite linear;-webkit-transform:translateZ(0);-ms-transform:translateZ(0);transform:translateZ(0)}@-webkit-keyframes load4{0%,100%{box-shadow:0 -3em 0 .2em #074f99,2em -2em 0 0 #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 0 #074f99}12.5%{box-shadow:0 -3em 0 0 #074f99,2em -2em 0 .2em #074f99,3em 0 0 0 #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}25%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 0 #074f99,3em 0 0 .2em #074f99,2em 2em 0 0 #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}37.5%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 0 #074f99,2em 2em 0 .2em #074f99,0 3em 0 0 #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}50%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 0 #074f99,0 3em 0 .2em #074f99,-2em 2em 0 0 #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}62.5%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 0 #074f99,-2em 2em 0 .2em #074f99,-3em 0 0 0 #074f99,-2em -2em 0 -.5em #074f99}75%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 0 #074f99,-3em 0 0 .2em #074f99,-2em -2em 0 0 #074f99}87.5%{box-shadow:0 -3em 0 0 #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 0 #074f99,-3em 0 0 0 #074f99,-2em -2em 0 .2em #074f99}}@keyframes load4{0%,100%{box-shadow:0 -3em 0 .2em #074f99,2em -2em 0 0 #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 0 #074f99}12.5%{box-shadow:0 -3em 0 0 #074f99,2em -2em 0 .2em #074f99,3em 0 0 0 #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}25%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 0 #074f99,3em 0 0 .2em #074f99,2em 2em 0 0 #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}37.5%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 0 #074f99,2em 2em 0 .2em #074f99,0 3em 0 0 #074f99,-2em 2em 0 -.5em #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}50%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 0 #074f99,0 3em 0 .2em #074f99,-2em 2em 0 0 #074f99,-3em 0 0 -.5em #074f99,-2em -2em 0 -.5em #074f99}62.5%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 0 #074f99,-2em 2em 0 .2em #074f99,-3em 0 0 0 #074f99,-2em -2em 0 -.5em #074f99}75%{box-shadow:0 -3em 0 -.5em #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 0 #074f99,-3em 0 0 .2em #074f99,-2em -2em 0 0 #074f99}87.5%{box-shadow:0 -3em 0 0 #074f99,2em -2em 0 -.5em #074f99,3em 0 0 -.5em #074f99,2em 2em 0 -.5em #074f99,0 3em 0 -.5em #074f99,-2em 2em 0 0 #074f99,-3em 0 0 0 #074f99,-2em -2em 0 .2em #074f99}}</style><script>function loadClientPage(page) { $( "#clientPage" ).html(\'<div id="dashboardLoader">Loading...</div>\'); $.get( "/Admin/Users/ID/' . $user_row['id'] . '/AjaxPage/"+encodeURIComponent(page)+"/", function( data ) { $( "#clientPage" ).html( data ); }); } addLoadEvent(function() { loadClientPage(\'Account\'); });</script><div class="tab-bg"><ul class="nav nav-tabs"><li class="active"><a href="#" data-toggle="tab" onClick="loadClientPage(\'Account\')"><i class="icon-user"></i> Account</a></li>';
			$modules = $billic->module_list_function('users_submodule');
			foreach ($modules as $module) {
				if ($billic->user_has_permission($billic->user, $module['id'])) {
					echo '<li><a href="#" data-toggle="tab" onClick="loadClientPage(\'' . $module['id'] . '\')">' . $module['id'] . '</a></li>';
				}
			}
			echo '</ul></div><div class="tab-content" style="background: #fff;padding: 0 20px 0 20px;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;text-align: justify;text-justify: inter-word"><div class="tab-pane active" id="clientPage" style="padding:10px"><div id="dashboardLoader">Loading...</div></div></div>';
			return;
		}
		$billic->module('ListManager');
		$billic->modules['ListManager']->configure(array(
			'search' => array(
				'id' => 'text',
				'email' => 'text',
				'firstname' => 'text',
				'lastname' => 'text',
				'companyname' => 'text',
			) ,
		));
		$where = '';
		$where_values = array();
		if (isset($_POST['search'])) {
			if (!empty($_POST['id'])) {
				$where.= '`id` = ? AND ';
				$where_values[] = $_POST['id'];
			}
			if (!empty($_POST['email'])) {
				$where.= '`email` LIKE ? AND ';
				$where_values[] = '%' . $_POST['email'] . '%';
			}
			if (!empty($_POST['firstname'])) {
				$where.= '`firstname` LIKE ? AND ';
				$where_values[] = '%' . $_POST['firstname'] . '%';
			}
			if (!empty($_POST['lastname'])) {
				$where.= '`lastname` LIKE ? AND ';
				$where_values[] = '%' . $_POST['lastname'] . '%';
			}
			if (!empty($_POST['companyname'])) {
				$where.= '`companyname` LIKE ? AND ';
				$where_values[] = '%' . $_POST['companyname'] . '%';
			}
		}
		$where = substr($where, 0, -4);
		$func_array_select1 = array();
		$func_array_select1[] = '`users`' . (empty($where) ? '' : ' WHERE ' . $where);
		foreach ($where_values as $v) {
			$func_array_select1[] = $v;
		}
		$func_array_select2 = $func_array_select1;
		$func_array_select1[0] = 'SELECT COUNT(*) FROM ' . $func_array_select1[0];
		$total = call_user_func_array(array(
			$db,
			'q'
		) , $func_array_select1);
		$total = $total[0]['COUNT(*)'];
		$pagination = $billic->pagination(array(
			'total' => $total,
			'list_manager' => $billic->module['ListManager'],
		));
		echo $pagination['menu'];
		$func_array_select2[0] = 'SELECT * FROM ' . $func_array_select2[0] . ' ORDER BY `datecreated` DESC LIMIT ' . $pagination['start'] . ',' . $pagination['limit'];
		$users = call_user_func_array(array(
			$db,
			'q'
		) , $func_array_select2);
		$billic->set_title('Admin/Users');
		echo '<h1><i class="icon-group"></i> Users</h1>';
		if (array_key_exists('Lu', $billic->lic)) {
			$lic_count = $db->q('SELECT COUNT(*) FROM `users` WHERE `status` = ?', 'Active');
			$lic_count = $lic_count[0]['COUNT(*)'];
			$lic_percent = ceil((100 / $billic->lic['Lu']) * $lic_count);
			echo '<div class="alert alert-';
			if ($lic_percent >= 80) {
				echo 'danger';
			} else if ($lic_percent >= 60) {
				echo 'warning';
			} else {
				echo 'info';
			}
			echo '" role="alert">Your Billic license limits you to ' . $billic->lic['Lu'] . ' active users. You are currently using ' . $lic_count . ' at ' . $lic_percent . '% capacity.</div>';
		}
		$billic->show_errors();
		echo $billic->modules['ListManager']->search_box();
		echo '<div style="float: right;padding-right: 40px;">Showing ' . $pagination['start_text'] . ' to ' . $pagination['end_text'] . ' of ' . $total . ' Users</div>' . $billic->modules['ListManager']->search_link();
		echo '<table class="table table-striped"><tr><th>Name</th><th>Company</th><th>Country</th><th>Services</th><th>Credit</th><th>Registered</th><th>Status</th></tr>';
		if (empty($users)) {
			echo '<tr><td colspan="20">No Users matching filter.</td></tr>';
		}
		foreach ($users as $user_row) {
			$services = '';
			$services_pending = $db->q('SELECT COUNT(*) FROM `services` WHERE `domainstatus` = ? AND `userid` = ?', 'Pending', $user_row['id']);
			$services_pending = $services_active[0]['COUNT(*)'];
			if ($services_pending > 0) {
				$services.= $services_pending . ' Pending, ';
			}
			$services_active = $db->q('SELECT COUNT(*) FROM `services` WHERE `domainstatus` = ? AND `userid` = ?', 'Active', $user_row['id']);
			$services_active = $services_active[0]['COUNT(*)'];
			if ($services_active > 0) {
				$services.= $services_active . ' Active, ';
			}
			$services_suspended = $db->q('SELECT COUNT(*) FROM `services` WHERE `domainstatus` = ? AND `userid` = ?', 'Suspended', $user_row['id']);
			$services_suspended = $services_suspended[0]['COUNT(*)'];
			if ($services_suspended > 0) {
				$services.= $services_suspended . ' Suspended, ';
			}
			$services_terminated = $db->q('SELECT COUNT(*) FROM `services` WHERE `domainstatus` = ? AND `userid` = ?', 'Terminated', $user_row['id']);
			$services_terminated = $services_terminated[0]['COUNT(*)'];
			if ($services_terminated > 0) {
				$services.= $services_terminated . ' Terminated, ';
			}
			$services = substr($services, 0, -2);
			if (empty($services)) {
				$services = 'None';
			}
			echo '<tr><td><a href="/Admin/Users/ID/' . $user_row['id'] . '/">' . $user_row['firstname'] . ' ' . $user_row['lastname'] . '</a></td><td>' . $user_row['companyname'] . '</td><td>' . $billic->flag_icon($user_row['country']) . '</td><td>' . $services . '</td><td>' . get_config('billic_currency_prefix') . $user_row['credit'] . get_config('billic_currency_suffix') . '</td><td>' . $billic->date_display($user_row['datecreated']) . '</td><td>' . $user_row['status'] . '</td></tr>';
		}
		echo '</table>';
	}
	function exportdata_submodule() {
		global $billic, $db;
		if (empty($_POST['date_start']) || empty($_POST['date_end'])) {
			echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/css/bootstrap-datepicker.min.css">';
			echo '<script>addLoadEvent(function() { $.getScript( "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/js/bootstrap-datepicker.min.js", function( data, textStatus, jqxhr ) { $( "#date_start" ).datepicker({ format: "yyyy-mm-dd" }); $( "#date_end" ).datepicker({ format: "yyyy-mm-dd" }); }); });</script>';
			echo '<form method="POST">';
			echo '<table class="table table-striped" style="width: 300px;"><tr><th colspan="2">Select registration date range</th></tr>';
			echo '<tr><td>From</td><td><input type="text" class="form-control" name="date_start" id="date_start" value="' . date('Y') . '-01-01"></td></tr>';
			echo '<tr><td>To</td><td><input type="text" class="form-control" name="date_end" id="date_end" value="' . date('Y') . '-12-' . date('t', mktime(0, 0, 0, 12, 1, date('Y'))) . '"></td></tr>';
			echo '<tr><td colspan="2" align="right"><input type="submit" class="btn btn-default" name="generate" value="Generate &raquo"></td></tr>';
			echo '</table>';
			echo '</form>';
			return;
		}
		$date = date_create_from_format('Y-m-d', $_POST['date_start']);
		$date->setTime(0, 0, 0);
		$date_start = $date->getTimestamp();
		$date = date_create_from_format('Y-m-d', $_POST['date_end']);
		$date->setTime(0, 0, 0);
		$date_end = ($date->getTimestamp() + 86399);
		ob_end_clean();
		ob_start();
		$cols = $db->q('SHOW COLUMNS FROM `users`');
		$cols_txt = '';
		foreach ($cols as $col) {
			$cols_txt.= $col['Field'] . ',';
		}
		$cols_txt = substr($cols_txt, 0, -1);
		echo $cols_txt . "\r\n";
		$users = $db->q('SELECT * FROM `users` WHERE `datecreated` >= ? AND `datecreated` <= ?', $date_start, $date_end);
		foreach ($users as $user) {
			$user['datecreated'] = date(DATE_ATOM, $user['datecreated']);
			echo str_replace("\n", '', str_replace("\r", '', implode(',', $user))) . "\r\n";
		}
		$output = ob_get_contents();
		ob_end_clean();
		header('Content-Disposition: attachment; filename=exported-' . strtolower($_GET['Module']) . '-' . time() . '.csv');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Length: ' . strlen($output));
		echo $output;
		exit;
	}
	function user_area() {
		global $billic, $db;
		if (array_key_exists('adminid', $_SESSION)) {
			$_SESSION['userid'] = $_SESSION['adminid'];
			unset($_SESSION['adminid']);
			$billic->redirect('/Admin/Dashboard/');
		}
	}
	function global_after_header() {
		global $billic, $db;
		if (array_key_exists('adminid', $_SESSION)) {
			echo '<br><div class="alert alert-info" role="alert">As an admin you have been logged in as ' . safe($billic->user['firstname']) . ' ' . safe($billic->user['lastname']) . '. <a href="/User/Users/AdminReturnLogin">Click here to return to your own account.</a></div><br>';
		}
	}
}
