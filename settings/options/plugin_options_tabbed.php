<?php
namespace AntibotsWPSettings;
// $mypage = new Page('Anti Bots', array('type' => 'menu'));
$mypage = new antibot_Page('Settings Anti Bots', array('type' => 'submenu', 'parent_slug' => 'anti_bots_plugin'));
$settings = array();
require_once(ANTIBOTSPATH . "guide/guide.php");
$settings['Startup Guide']['Startup Guide'] = array('info' => $ah_help);
$fields = array();
$settings['Startup Guide']['Startup Guide']['fields'] = $fields;
$msg2 = '<b>' . __('Block all Bots Less Whitelisted?', 'antibots') . '</b>';
$msg2 .= '<br />';
$msg2 .= __('Mark Yes and the plugin will block bots right away.', 'antibots');
$msg2 .= '<strong>'.__('Please, read the StartUp Guide before mark yes.', 'antibots').'</strong>';
$msg2 .= '<br />';
$msg2 .= __('Mark Test Mode and the plugin will create a log of visitors and statistics but doesn\'t block any bot.', 'antibots');
$msg2 .= '<br />';
$msg2 .= __('Then click SAVE CHANGES.', 'antibots');
$msg2 .= '<br />';
$msg2 .= '<br />';
$settings['General Settings'][__('Instructions')] = array('info' => $msg2);
$fields = array();
$fields[] = array(
	'type' 	=> 'radio',
	'name' 	=> 'antibots_is_active',
	'label' => __('Block all Bots Less Whitelisted?'),
	'radio_options' => array(
		array('value' => 'yes', 'label' => __('yes')),
		array('value' => 'test', 'label' => __('Test mode'))
	)
);
// Select List
$fields[] = array(
	'type' 	=> 'select',
	'name' 	=> 'antibots_keep_data',
	'label' => __('Keep Visitor Record Max', 'antibots'),
	'id' => 'antibots_keep_data', // (optional, will default to name)
	'value' => '4', // (optional, will default to '')
	'select_options' => array(
		array('value' => '1', 'label' => __('1 Week', "antibots")),
		array('value' => '2', 'label' => __('2 Weeks', "antibots")),
		array('value' => '3', 'label' => __('3 Weeks', "antibots")),
		array('value' => '4', 'label' => __('4 Weeks', "antibots")),
		array('value' => '5', 'label' => __('5 Weeks', "antibots")),
		array('value' => '6', 'label' => __('6 Weeks', "antibots")),
		array('value' => '7', 'label' => __('7 Weeks', "antibots")),
		array('value' => '8', 'label' => __('8 Weeks', "antibots")),
	)
);
$settings['General Settings']['']['fields'] = $fields;
$msg2  = '<b>' . __('You can create 2 whitelist in this page: String and IP.', 'antibots') . '</b>';
$msg2 .= '<br />';
$msg2 .= __('Just add one string to unblock all User Agent that contain that string.', 'antibots');
$msg2 .= '<br />';
$msg2 .= __('For IP withelist, just add the IP to unblock it.', 'antibots');
$msg2 .= '<br />';
$msg2 .= __('Add only one for each line.', 'antibots');
$msg2 .= '<br />';
$settings['Whitelist'][__('Instructions about User Agent String and IP Whitelist.')] = array('info' => $msg2);
$fields = array();
$fields[] = array(
	'type' 	=> 'radio',
	'name' 	=> 'antibots_enable_whitelist',
	'label' => __('Enable Both Withelist?', 'antibots'),
	'radio_options' => array(
		array('value' => 'yes', 'label' => __('yes', "antibots")),
		array('value' => 'no', 'label' => __('no', "antibots"))
	)
);
$fields[] = array(
	'type' 	=> 'textarea',
	'name' 	=> 'antibots_string_whitelist',
	'label' => __('String whitelist (no case sensitive)', 'antibots'),
);
$fields[] = array(
	'type' 	=> 'textarea',
	'name' 	=> 'antibots_ip_whitelist',
	'label' => __('IP whitelist.', 'antibots') . ' ' . __('Your Current IP:', 'antibots') . ' ' . antibots_findip(),
);
$settings['Whitelist']['Whitelist Tables']['fields'] = $fields;
//
// $antibots_admin_email = get_option( 'admin_email' ); 
$msg_email = __('Fill out the email address to send messages.', 'antibots');
$msg_email .= '<br />';
$msg_email .= __('Left Blank to use your default WordPress email. Then, click save changes.', 'antibots');
$fields = array();
$fields[] = array(
	'type' 	=> 'text',
	'name' 	=> 'antibots_my_email_to',
	'label' => 'email'
);
$notificatin_msg = __('Do you want receive email alerts for each bot attempt?', 'antibots');
$notificatin_msg .= '<br />';
$notificatin_msg .= __('If you under brute force attack, you will receive a lot of emails.', 'antibots');
$notificatin_msg .= '<br />';
$notificatin_msg .= __('You can see the bots attacks info at Visitors Log Table. (column Num Blocked).', 'antibots');
$fields[] = array(
	'type' 	=> 'radio',
	'name' 	=> 'antibots_my_radio_report_all_visits',
	'label' => __('Alert me by email each Bots Attempts'),
	'radio_options' => array(
		array('value' => 'yes', 'label' => 'Yes.'),
		array('value' => 'no', 'label' => 'No.'),
	)
);
$settings['Email and Notifications']['Email and Notifications']['fields'] = $fields;
new OptionPageBuilderTabbed($mypage, $settings);