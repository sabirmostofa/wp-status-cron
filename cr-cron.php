<?php
ignore_user_abort(true);
define('DOING_PLUGIN_CRON',true);
require_once '../../../wp-load.php';
//var_dump(wp_clear_scheduled_hook('wptpe_cron'));

global $wpPostsStat;

$wpPostsStat-> kickstart();

//echo wp_reschedule_event($now+$interval, $current, 'wptpe_cron');
//echo wp_unschedule_event($next,'wptpe_cron');

exit;
 
?>
