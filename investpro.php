<?php
/*** Plugin Name: Invest Pro
Plugin URI: http://www.devxtech.com.ng
Version: 0.0.1
Description: Wordpress plugin to manage investments for users
Author: Oluwaseun Paul ( DEVX )
Author URI: http://www.devxtech.com.ng
Text Domain: invespro
License: GPL2 ***/

class InvestPro {

public function __construct(){
  add_shortcode('custom-login-form', array($this, 'render_login_form'));
  add_action('login_form_login', array($this,'redirect_login_form' ));
  add_filter('authenticate', array($this, 'authenticate_login'), 101, 3);
  add_action( 'wp_logout', array( $this, 'redirect_after_logout' ) );
  add_filter( 'login_redirect', array( $this, 'redirect_after_login' ), 10, 3 );
  //user registration
  add_shortcode('custom-register_form', array($this, 'render_register_form'));
  add_action('login_form_register', array($this, 'redirect_to_custom_register'));

//do the registration by calling register();
  add_action( 'login_form_register', array( $this, 'do_register' ) );
  add_action( 'login_form_lostpassword', array( $this, 'redirect_to_custom_lostpassword' ) );
  add_shortcode( 'custom-password-lost-form', array( $this, 'render_password_lost_form' ) );
  add_shortcode('admin-dashboard', array($this, 'render_admin_dashboard' ));

  //member dashboard
  add_shortcode('member-dashboard', array($this, 'member_dashboard'));

  //load the plugin's styles
  wp_enqueue_style('investpro',plugin_dir_url(__FILE__).'/css/investpro.css', false);
  wp_enqueue_style('styles_bts',plugin_dir_url(__FILE__).'/bower_components/bootstrap/dist/css/bootstrap.min.css', false);
  add_action('wp_enqueue_scripts', array($this,'wpd_load_scripts'));

  //hook settings fields
  add_filter('admin_init', array($this,'register_settings_options' ));
 }
 //load scripts
 public function wpd_load_scripts(){
   wp_enqueue_script( 'jquery' );
   wp_enqueue_script('scripts_bts',plugin_dir_url(__FILE__).'/bower_components/bootstrap/dist/js/bootstrap.min.js');
 }


 public function register_settings_options(){
   register_setting('general', 'investpro_payment_details');
   register_setting('general', 'investpro_interest_percentage');
   register_setting('general', 'investpro_afterpayment_details');

   //payment details instruction field
   add_settings_field(
     'investpro_payment_details',
     '<label for="investpro_payment_details">'.__('Account Funding Instructions', 'investpro').'</label>',
     array($this, 'render_funding_instructions'),
     'general'
   );

   add_settings_field(
     'investpro_interest_percentage',
     '<label for="investpro_interest_percentage">'.__('Interest Percentage','investpro').'</label>',
     array($this, 'render_interest_percentage'),
     'general'
   );

   add_settings_field(
     'investpro_afterpayment_details',
     "<label for='investpro_afterpayment_details'>".__('After Payment Instructions', 'investpro').'</label>',
     array($this, 'render_after_payment_instructions'),
     'general'
   );


 }
 //render payment instructions field
 public function render_funding_instructions(){
   $value = get_option('investpro_payment_details', '');
   echo "<textarea name='investpro_payment_details' rows='8' cols='80'>$value</textarea>";
 }
 //render Percentage settings field
 public function render_interest_percentage(){
   $value = get_option('investpro_interest_percentage', '');
   echo "<input type='text' name='investpro_interest_percentage' value='$value'>";
 }
 //render after payment instructions
 public function render_after_payment_instructions(){
   $value= get_option('investpro_afterpayment_details', '');
   echo "<textarea name='investpro_afterpayment_details' rows='8' cols='80'>$value</textarea>";

 }

public function create_db_table(){
  require_once(ABSPATH .'/wp-admin/includes/upgrade.php');
  $created = dbDelta(
"CREATE TABLE investpro_trans_log(
ID int(255) unsigned NOT NULL AUTO_INCREMENT,
trans_type varchar(255) NOT NULL DEFAULT '',
amount varchar(255) NOT NULL DEFAULT '',
account_balance_after varchar(255) NOT NULL DEFAULT '',
user_id varchar(255) NOT NULL DEFAULT '',
trans_date varchar(255) NOT NULL DEFAULT '',
trans_remark varchar(255) NOT NULL DEFAULT '',
PRIMARY KEY (ID),
KEY user_id (user_id)
)CHARACTER SET utf8 COLLATE utf8_general_ci;"
  );
}

public function create_table_payments(){
  $payments = dbDelta(
    "CREATE TABLE investpro_payment_requests(
      ID int(255) unsigned NOT NULL AUTO_INCREMENT,
      user_id varchar(255) NOT NULL DEFAULT '',
      user_email varchar(255) NOT NULL DEFAULT '',
      first_name varchar(255) NOT NULL DEFAULT '',
      last_name  varchar(255) NOT NULL DEFAULT '',
      request_amount varchar(255) NOT NULL DEFAULT '',
      account_balance varchar(255) NOT NULL DEFAULT '',
      PRIMARY KEY (ID),
      KEY user_id (user_id)
    )CHARACTER SET utf8 COLLATE utf8_general_ci;"
  );
}


 public function investpages(){
//information needed for creating the plugins pages
 $pages_definitions = array(
  'member-login'=>array(
    'title'=>'Login To Your Account',
    'content'=>'[custom-login-form]'
  ),
  'member-account'=>array(
    'title'=>'Your Account',
    'content'=>'[investpro-account]'
  ),
  'member-register'=>array(
    'title'=>'Register',
    'content'=>'[custom-register_form]'
  ),
  'member-password-lost' => array(
        'title' => __( 'Forgot Your Password?', 'investpro' ),
        'content' => '[custom-password-lost-form]'
    ),
    'member-password-reset' => array(
        'title' => __( 'Pick a New Password', 'investpro' ),
        'content' => '[custom-password-reset-form]'
    ),
    'member-dashboard'=>array(
      'title'=>__('Member Dashboard', 'investpro'),
      'content'=>'[member-dashboard]'
    ),

    'admin-dashboard'=>array(
      'title'=>__('Admin Dashboard','investpro'),
      'content'=>'[admin-dashboard]'
    ),
);

//creating the pages using wp_insert_post()
foreach($pages_definitions as $slug=>$page){
  //check that the page does not exists
  $query = new WP_Query('pagename='.$slug);
  if(!$query->have_posts()){
    $page_id = wp_insert_post(array(
      'post_title'=>$page['title'],
      'post_content'=>$page['content'],
      'post_name'=>$slug,
      'post_status'=>'publish',
      'comment_status'=>'closed',
      'ping_status'=>'closed',
      'post_type'=>'page'
    )
  );
  }
}
 }

// function to handle the shortcode investpro-login
public function render_login_form ($attributes, $content = null){
  $default_attributes = array('show_title'=>false);
  $attributes = shortcode_atts($default_attributes, $attributes);
  $show_title = $attributes['show_title'];

  //check if user is logged in and render appropriate message
  if( is_user_logged_in() ){
    return __('You are already logged In', 'invespro');
  }
  //set a redirect if none is set to default wordpress redirect
  $attributes['redirect']= '';
  if(isset($_REQUEST['redirect_to'])){
    $attributes['redirect'] = wp_validate_redirect($_REQUEST['redirect_to'], $attributes['redirect']);
  }

  //render the login form
  return $this->get_template_html('login_form', $attributes);
}

public function get_template_html($template_name, $attributes = null ){
  if( ! $attributes ){
    $attributes = array();
  }

  ob_start();

  do_action('investpro_before_login'.$template_name);

  require('templates/'.$template_name.'.php' );

  do_action('investpro_after_login'.$template_name);

  $html = ob_get_contents();

  ob_end_clean();

  return $html;
}

public function redirect_login_form(){
  if($_SERVER['REQUEST_METHOD']=="GET"){
    //redirect user to custom page instead of wp-login.php
    $redirect_to = isset($_REQUEST['redirect_to'])? $_REQUEST['redirect_to']: null;
    if(is_user_logged_in()){
      $this->redirect_logged_in_user($redirect_to);
      exit;
    }
    //the rest are redirected to the login page

    //collect the errors
    if(isset($_REQUEST['login'])){
      $errors = array();
      $error_codes = explode(',', $_REQUEST['login']);
      foreach($error_codes as $code){
        $errors[] = $this->get_error_messages($code);
      }
      $attributes['error'] = $errors;
    }

    $login_url = home_url('member-login');
    if(!empty($redirect_to)){
    $login_url = add_query_arg('redirect_to', $redirect_to, $login_url );
  }
  wp_redirect($login_url);
  exit;
  }
}

public function redirect_logged_in_user( $redirect_to ){
  $user = wp_get_current_user();
  if(user_can($user, 'manage_options')){
    //$redirect_to = admin_url();
    $redirect_to = home_url('member-account');
  }
  else{
    $redirect_to = home_url();
    //$redirect_to = home_url('member-account');
  }
    return wp_safe_redirect($redirect_to, home_url());
  }
public function authenticate_login($user, $username,$password){
  //check for errors during user login
  if($_SERVER['REQUEST_METHOD']=="POST"){
    if(is_wp_error($user)){
      $error_codes = join(',', $user->get_error_codes() );
      $login_url = home_url('member-login');
      $login_url = add_query_arg('login',$error_codes,$login_url);
      wp_redirect($login_url);
      exit;
    }
  }
  return $user;
}
private function get_error_messages($error_code){
  switch($error_code){
    case 'empty_username':
    return __('Username cannot be empty', 'investpro');
    case 'empty_password':
            return __( 'You need to enter a password to login.', 'personalize-login' );

        case 'invalid_username':
            return __(
                "We don't have any users with that email address. Maybe you used a different one when signing up?",
                'personalize-login'
            );
            case 'incorrect_password':
                 $err = __(
                     "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
                     'personalize-login'
                 );
                 return sprintf( $err, wp_lostpassword_url() );
                 case 'email':
                 return __( 'The email address you entered is not valid.', 'personalize-login' );

             case 'email_exists':
                 return __( 'An account exists with this email address.', 'personalize-login' );

             case 'closed':
                 return __( 'Registering new users is currently not allowed.', 'personalize-login' );
            default:
            break;
  }
  return __( 'An unknown error occurred. Please try again later.', 'investpro' );
}

public function redirect_after_logout()
{
    $redirect_url = home_url('member-login?logged_out=true');
    wp_safe_redirect($redirect_url);
    exit;
}

public function redirect_after_login($redirect_to, $requested_redirect_to, $user){
if(!$user->ID){
  $requested_redirect_to = home_url();
}
$requested_redirect_to = home_url('member-dashboard');

return $requested_redirect_to;

}

//USER'S REGISTRATION

public function render_register_form($attributes, $content = null ){
  $default_attributes = array('show_title'=>false);
  $attributes = shortcode_atts($default_attributes,$attributes);

  if(is_user_logged_in()){
    return __('You are already signed in ', 'investpro');
  }
  elseif(!get_option('users_can_register')){
    return __('registration is currently closed', 'investpro');
  }
  else{
    return $this->get_template_html('register_form', $attributes);
  }
}

public function redirect_to_custom_register(){
  if("GET"==$_SERVER['REQUEST_METHOD']){
    if(is_user_logged_in()){
      $this->redirect_logged_in_user();
    }
    else{
      wp_redirect(home_url('member-register'));
    }
    exit;
  }
}

public function register_user($email, $firstname,$lastname){

  $errors = new WP_Error();

  if(!is_email($email)){
    $errors->add('email', $this->get_error_messages('email'));
    return $errors;
  }
if(username_exists($email) || email_exists($email)){
  $errors->add('email_exists', $this->get_error_messages('email_exists'));
}

//generate password so that the user will have to check their mail

$password = wp_generate_password(12, false);

$user_data = array(
        'user_login'    => $email,
        'user_email'    => $email,
        'user_pass'     => $password,
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'nickname'      => $first_name,
);

$user_id = wp_insert_user($user_data);
wp_new_user_notification($user_id);
return $user_id;

}


public function do_register(){
  if($_SERVER['REQUEST_METHOD']=="POST"){
    $redirect_url = home_url('member-register');

    if(! get_option('users_can_register')){
      //registration closed;
      $redirect_url = add_query_arg('register_errors', 'closed',$redirect_url );
    }
    else {
      $email = $_POST['email'];
      $first_name = sanitize_text_field($_POST['first_name']);
      $last_name = sanitize_text_field($_POST['last_name']);
      $phone_number = sanitize_text_field($_POST['user_phone']);

      //user banking details
      $account_name = sanitize_text_field($_POST['account_name']);
      $account_number = sanitize_text_field($_POST['account_number']);
      $bank_name = sanitize_text_field($_POST['bank_name']);



      $result = $this->register_user($email, $first_name, $last_name);
      if(is_wp_error($result)){
        $errors = join(',', $result->get_error_codes());
        $redirect_url = add_query_arg('register_errors', $errors, $redirect_url);
      }
      else{

        //save user's other dat
        update_user_meta($result, 'phone_number',$phone_number );
        update_user_meta($result, 'account_name',$account_name );
        update_user_meta($result, 'account_number',$account_number );
        update_user_meta($result, 'bank_name',$bank_name );
        update_user_meta($result, 'first_name',$first_name );
        update_user_meta($result, 'last_name',$last_name );
        update_user_meta($result, 'account_balance',00 );



        $redirect_url = home_url('member-login');
        $redirect_url = add_query_arg('registered',$email, $redirect_url );

        //add user meta informations

      }
    }
  }
  wp_redirect($redirect_url);
  exit;
}
public function redirect_to_custom_lostpassword(){
  if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        if ( is_user_logged_in() ) {
            $this->redirect_logged_in_user();
            exit;
        }

        wp_redirect( home_url( 'member-password-lost' ) );
        exit;
    }
}

public function render_password_lost_form( $attributes, $content = null ) {
    // Parse shortcode attributes
    $default_attributes = array( 'show_title' => false );
    $attributes = shortcode_atts( $default_attributes, $attributes );

    if ( is_user_logged_in() ) {
        return __( 'You are already signed in.', 'personalize-login' );
    } else {
        return $this->get_template_html( 'password_lost_form', $attributes );
    }
}
public function member_dashboard($attributes , $content = null ){
$default_attributes = array('show_title'=>false);
$attributes = shortcode_atts($default_attributes, $attributes);
$user = wp_get_current_user();
//var_dump($user);
$attributes['current_user']= $user;
  $login_url = home_url('member-login');
  if(is_user_logged_in()){
    //return __('You are now logged in', 'investpro');
    return $this->get_template_html('member_dashboard', $attributes);
  }
  else{
    wp_redirect($login_url);
    exit;

  }
}

public function render_admin_dashboard($attributes, $content = null ){
  $default_attributes = array('show_title'=>false);
  $attributes = shortcode_atts($default_attributes, $attributes);
  if(!is_user_logged_in()){
    $login_url = home_url('member-login');
    return __('You dont have access to this page, please login as admin', 'investpro');
    wp_redirect($login_url);
    exit;
  }
  else{
    return $this->get_template_html('admin_dashboard', $attributes );
  }

}

}


$investpro = new investpro();


/** Register an activation hook
 *
 * hook fired when plugin is activated to create the plugin pages
 *
 */

register_activation_hook( __FILE__, array( 'InvestPro', 'investpages' ) );
register_activation_hook( __FILE__, array('InvestPro', 'create_db_table') );
register_activation_hook( __FILE__, array('InvestPro', 'create_table_payments') );
