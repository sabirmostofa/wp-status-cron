<?php
/*
  Plugin Name: WP-Posts-Status
  Plugin URI: http://sabirul-mostofa.blogspot.com
  Description: Send Email the number of scheduled Posts and published Posts
  Version: 1.0
  Author: Sabirul Mostofa
  Author URI: http://sabirul-mostofa.blogspot.com
 */


$wpPostsStat = new wpPostsStatCron();

class wpPostsStatCron {

    function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_dashboard_setup', array($this, 'add_widget'));
        add_action('wp_ajax_posts_stat', array($this, 'ajax_handle'));
//add_action('wp_loaded', array($this,'initialize_cron'));
        add_filter('cron_schedules', array($this, 'cron_scheds'));
//add_action('wptpe_cron', array($this, 'create_cron'));
        add_action('init', array($this, 'prestart_http_request'));
    }

    function prestart_http_request() {
        if (defined('DOING_PLUGIN_CRON') || defined('DOING_CRON') || defined('DOING_AJAX'))
            return;

        $url = plugins_url('/', __FILE__) . 'cr-cron.php';

        wp_remote_get($url, array('timeout' => 0.01,
            'blocking' => false,
            'sslverify' => apply_filters('https_local_ssl_verify', true)));
    }

    function kickstart() {
        add_filter('cron_schedules', array($this, 'cron_scheds'));
        $current = wp_get_schedule('wptpe_cron');
        $next = wp_next_scheduled('wptpe_cron');
        $scheds = wp_get_schedules();
        print_r($schehds);
        $now = time();
       echo $interval = $scheds[$current]['interval'];
        update_option('aaaa', $now . '#' . $next);
        
        if ( $now > $next) {
            update_option('aaaacrondone','done');
            $this->create_cron();
            wp_reschedule_event($now + $interval, $current, 'wptpe_cron');
            wp_unschedule_event($next, 'wptpe_cron');
        }
    }

    function admin_scripts() {
        wp_enqueue_script('tpe_admin_script', plugins_url('/', __FILE__) . 'js/script_admin.js');
    }

    function ajax_handle() {

        $mails = $_POST['mails'];
        $cron = $_POST['cron'];
        update_option('wptp_cron_opt', $cron);
        $this->initialize_cron();
        update_option('wptp_cron_mails', $mails);
//
// $mail = 'sabirmostofa@gmail.com';
//        $domain='aa.com';
// $mail_body = 'gg';
//        $b = mail($mail, 'test','yahoo');
// $a = wp_mail($mail, 'Number of all Scheduled Posts and Publisched Posts', $mail_body);
//var_dump($a);
        echo time(), '<br/>', wp_next_scheduled('wptpe_cron');
//$this->create_cron();
        exit;
    }

    function cron_scheds($cron_schedules) {
        $cron_schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __('every seven days')
        );
        $cron_schedules['every_minute'] = array(
            'interval' => 60,
            'display' => __('every minute')
        );

        return $cron_schedules;
    }

    function initialize_cron() {

        $opt = get_option('wptp_cron_opt');
        switch ($opt):
            case 'daily':
                if (wp_get_schedule('wptpe_cron') != 'daily') {
                    wp_clear_scheduled_hook('wptpe_cron');
                    wp_schedule_event(time(), 'daily', 'wptpe_cron');
                }
                break;
            case 'weekly':
                if (wp_get_schedule('wptpe_cron') != 'weekly') {
                    wp_clear_scheduled_hook('wptpe_cron');
                    wp_schedule_event(time(), 'weekly', 'wptpe_cron');
                }
                break;

            case 'minute':
                if (wp_get_schedule('wptpe_cron') != 'every_minute') {
                    wp_clear_scheduled_hook('wptpe_cron');
                    wp_schedule_event(time(), 'every_minute', 'wptpe_cron');
                }
                break;
            case 'hourly':
                if (wp_get_schedule('wptpe_cron') != 'hourly') {
                    wp_clear_scheduled_hook('wptpe_cron');
                    wp_schedule_event(time(), 'hourly', 'wptpe_cron');
                }
                break;


        endswitch;
    }

    function create_cron() {

//mail('sabirmostofa@gmail.com', 'test', 'again');
        if ($mails = $this->get_mails()):
            foreach ($mails as $mail):

                $this->send_mail($mail);
            endforeach;
        endif;
    }

    function send_mail($mail) {
        global $wpdb;
        $published = '';
        $scheduled = '';

        $res = $wpdb->get_results("
           select count(*) count
           from   $wpdb->posts 
           where  post_status='publish'          
        ");

        $published = $res[0]->count;

        $resu = $wpdb->get_results("
           select count(*) coun
           from   $wpdb->posts 
           where  post_status='future'          
        ");

        $scheduled = $resu[0]->coun;

        echo $domain = $_SERVER['SERVER_NAME'];

        $mail_body = "<h4>Domain: $domain</h4>";
        $mail_body .="<h4>Total posts published: $published</h4>";
        $mail_body .="<h4>Total posts scheduled: $scheduled</h4>";

        $headers = "MIME-Version: 1.0" . "\r\n" .
                "Content-type: text/html; charset=UTF-8" . "\r\n";
        $res_mail = wp_mail($mail, "$domain : Number of all Scheduled Posts and Publisched Posts", $mail_body, $headers);
        var_dump($res_mail);
        return $res_mail;
    }

    function get_mails() {
        if ($str = get_option('wptp_cron_mails')) {
            $ar = explode(',', trim($str, ','));
            return $ar;
        }
    }

    function add_widget() {
        wp_add_dashboard_widget('wptpcsv', 'Mail the scheduled and publisched posts number ', array($this, 'build_widget'));
    }

    function build_widget() {
//        //echo time(), '<br/>';
//        echo wp_next_scheduled('wptpe_cron');
//        var_dump(wp_get_schedule('wptpe_cron'));
//        print_r(get_option('cron'));
        $mails = get_option('wptp_cron_mails') ? get_option('wptp_cron_mails') : '';

        $cron = get_option('wptp_cron_opt') ? get_option('wptp_cron_opt') : '';
        ?>
        Insert email or more emails separated by comma:
        <input type="text" id="mails" value="<?php echo $mails; ?>" style="width:80%"/>
        <br/>
        Select the mail schedule:
        <br/>
        <select id="cronSelect">
            <option value='weekly' <?php if ($cron == 'weekly')
            echo 'selected="selected"' ?>>Every Week</option>
            <option value='daily' <?php if ($cron == 'daily')
            echo 'selected="selected"' ?>>Daily</option>
            <option value='hourly' <?php if ($cron == 'hourly')
            echo 'selected="selected"' ?>>Hourly</option>
            <option value='minute' <?php if ($cron == 'minute')
            echo 'selected="selected"' ?>>Every Minute</option>

        </select>
        <br/>
        <button class="button primary" id="tpSave">Save</button>

        <?php
    }

}