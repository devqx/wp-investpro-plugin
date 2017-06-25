<?php
$attributes['errors'] = array();
if(isset($_REQUEST['register-errors'])){
  $error_codes = explode(',', $_REQUEST['register-errors']);
  foreach($error_codes as $code ){
    $attributes['errors'] = $this->get_error_messages($code);
  }
}?>

<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
    <?php foreach ( $attributes['errors'] as $error ) : ?>
        <p>
            <?php echo $error; ?>
        </p>
    <?php endforeach; ?>
<?php endif; ?>

<div id="register-form" class="widecolumn">
    <?php if ( $attributes['show_title'] ) : ?>
        <h3><?php _e( 'Register To Become A Memeber Today', 'personalize-login' ); ?></h3>
    <?php endif; ?>

    <form id="signupform" action="<?php echo wp_registration_url(); ?>" method="post">
        <p class="form-row form-control">
            <label for="email"><?php _e( 'Email', 'personalize-login' ); ?> <strong>*</strong></label>
            <input type="text" name="email" id="email" class="form-control">
        </p>

        <p class="form-row">
            <label for="first_name"><?php _e( 'First name', 'personalize-login' ); ?></label>
            <input type="text" name="first_name" id="first-name" class="form-control">
        </p>

        <p class="form-row">
            <label for="last_name"><?php _e( 'Last name', 'personalize-login' ); ?></label>
            <input type="text" name="last_name" id="last-name" class="form-control">
        </p>

        <p class="form-row">
            <label for="user_phone"><?php _e( 'Phone Number', 'personalize-login' ); ?></label>
            <input type="text" name="user_phone" id="user_phone" class="form-control">
        </p>
        <h3>Provide Bank Account Details</h3>
        <p class="form-row">
            <label for="account_name"><?php _e( 'Account Name', 'personalize-login' ); ?></label>
            <input type="text" name="account_name" id="account_name" class="form-control">
        </p>
        <p class="form-row">
            <label for="account_number"><?php _e( 'Account Number', 'personalize-login' ); ?></label>
            <input type="text" name="account_number" id="account_number" class="form-control">
        </p>
        <p class="form-row">
            <label for="bank_name"><?php _e( 'Bank Name', 'personalize-login' ); ?></label>
            <input type="text" name="bank_name" id="bank_name" class="form-control">
        </p>

        <p class="form-row">
            <?php _e( 'Note: Your password will be generated automatically and sent to your email address.', 'personalize-login' ); ?>
        </p>

        <p class="signup-submit">
            <input type="submit" name="submit" class="register-button form-control"
                   value="<?php _e( 'Register', 'personalize-login' ); ?>"/>
        </p>
    </form>
</div>
