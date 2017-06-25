<div class="container">
<?php //var_dump($attributes);?>
  <?php if($attributes['show_title']){
    __('<h2>Sign In</h2>', 'investpro');
  }?>
  <?php $errors = array();
if ( isset( $_REQUEST['login'] ) ) {
    $error_codes = explode( ',', $_REQUEST['login'] );

    foreach ( $error_codes as $code ) {
        $errors []= $this->get_error_messages( $code );
    }
    $attributes['errors'] = $errors;
}
?>
  <?php if(count($attributes['errors'])> 0){
    foreach($attributes['errors'] as $errors ){?>
      <p class="error">
        <?php echo $errors;?>
      </p>
    <?php

  }

  }?>

  <?php if ( $attributes['registered'] ) : ?>
    <p class="login-info">
        <?php
            printf(
                __( 'You have successfully registered to <strong>%s</strong>. We have emailed your password to the email address you entered.', 'personalize-login' ),
                get_bloginfo( 'name' )
            );
        ?>
    </p>
<?php endif; ?>


<?php // Check if the user just registered
$attributes['registered'] = isset( $_REQUEST['registered'] );?>

  <?php if ( $attributes['logged_out'] ) : ?>
    <p class="login-info">
        <?php _e( 'You have signed out. Would you like to sign in again?', 'investpro' ); ?>
    </p>
<?php endif; ?>
  <?php if ( $attributes['logged_out'] ) : ?>
    <p class="login-inf">
        <?php _e( 'You have signed out. Would you like to sign in again?', 'investpro' ); ?>
    </p>
<?php endif; ?>
  <?php wp_login_form(array(
    'label_user_name'=>__('Your Email', 'investpro'),
    'label_log_in'=>__('Sign In', 'investpro'),
    'redirect'=>$attributes['redirect']
  )
);
  ?>

  <a class="forgot-password" href="<?php echo wp_lostpassword_url();?>"><?php _e('Forgot Your Password ?', 'investpro');?></a>

</div>
