<div class="container">
  <?php $current_user = wp_get_current_user();?>
  <h1>Welcome To Your Account Dashboard : <?php echo $current_user->first_name. ' '.$current_user->last_name;?></h1>
  <?php $logged_in_id = $current_user->ID;
  //echo $logged_in_id;
  ?>
</div>
  <div class="container" id="dashboard">
  <ul class="nav nav-pills">
    <li class="active"><a href="#info" data-toggle="tab">Account Info</a></li>
    <li><a href="#fund" data-toggle="tab">Fund Account </a></li>
    <li><a href="#earnings" data-toggle="tab">Withdrawl Earnings</a></li>
    <li><a href="#update" data-toggle="tab">Update Account</a></li>
    <li><a href="#logs" data-toggle="tab">Transaction History</a></li>
  </ul>

  <div class="tab-content">
    <div  class="tab-pane active" id="info">
      <div class="row">
        <div class="col-md-3">
      <h3>Profile Info</h3>
      <p>Email:<?php echo  $current_user->user_email;?> </p>
      <p>First Name: <?php echo  $current_user->user_firstname;?> </p>
      <p>Last Name: <?php echo  $current_user->user_lastname;?> </p>
      <p>Phone Number : <?php echo get_user_meta($current_user->ID, 'phone_number', true);?></p>
      </div>

      <div class="col-md-3">
      <h3>Payment Info</h3>
      <p> Account Number: <?php echo get_user_meta($current_user->ID, 'account_number',true)?> </p>
      <p> Account Name: <?php echo get_user_meta($current_user->ID, 'account_name',true)?>  </p>
      <p> Bank Name: <?php echo get_user_meta($current_user->ID, 'bank_name',true)?> </p>
      </div>
      </div>
    </div>

    <div class="tab-pane"  id="fund" style="width:80%;" >

    <?php echo get_option('investpro_payment_details', null);?>
  </div>

    <div class="tab-pane"  id="earnings" style="width:40%;">
        <h3 style="margin-top:10px;">You can request for your money after 30 days.</h3>
        <small> The Minimum Amount You can Withdrawl is:</small>
        <h3 style="margin-top:10px;">Your Current Account Balance: &#8358;<?php echo get_user_meta($current_user->ID, 'account_balance',true);?> </h3>
        <form class="" action="" method="post">
          <input type="hidden" name="withdrawal_amount" value="<?php echo get_user_meta($current_user->ID, 'account_balance', true);?>">
          <input type="submit" name="payment" value="Request For Withdrawal" class="btn-success">
        </form>

        <?php if(!empty($_POST['withdrawal_amount'])){
          global $wpdb;
          $wpdb->show_errors();
          $request_amount = $_POST['withdrawal_amount'];
          $payment_requests = array(
            'request_amount'=>$request_amount,
            'user_id'=>$current_user->ID,
            'user_email'=>$current_user->user_email,
            'first_name'=>$current_user->first_name,
            'last_name'=>$current_user->last_name,
            'account_balance'=>$current_user->account_balance

          );
          $format = array(
            '%d','%s','%s','%s','%s'
          );
          $log_request = $wpdb->insert('investpro_payment_requests', $payment_requests, $format);
          if($log_request){
            echo "You Payment Request has been sent, you will get a notification shortly";
          }
        }
        //set investment time to add interest
        $date = new DateTime();
        $interets_date = $date->modify('+ 31 days');
        $interest_date_string = $date->format('Y-m-d');
        //echo $interest_date_string.'<br>';
        global $wpdb;
        //get the time he started the investment
        $start_date = $wpdb->get_var("SELECT MAX(trans_date) FROM investpro_trans_log");
        //$now = time(); // or your date as well
        $invest_start_date = strtotime($start_date);
        //echo $invest_start_date.'<br>';
        $interest_time = strtotime($interest_date_string);
        //echo $interest_time.'<br>';
        $datediff = $interest_time - $invest_start_date;

        $days_diff = floor($datediff / (60 * 60 * 24));

        $now = date('d');

        if($days_diff == $now ){
          $curr_bal = get_user_meta($current_user->ID, 'account_balance',true);

          //get interest percentage
          $percentage_interest = get_option('investpro_interest_percentage', '');

          //calcaulate the interest for the period of 1 month continously
          $interest_comission = $percentage_interest / 100 * $curr_bal;
          //echo $interest_comission;
          $new_bal = $curr_bal + $interest_comission;
          update_user_meta($current_user->ID,'account_balance',$new_bal);

        }




        ?>


    </div>

        <div  class="tab-pane" id="update" style="width:40%;">
            <h3 style="margin-bottom:2px;">You can update your details below</h3>
            <small>Update Your Payment Details:</small>
            <form class="" action="" method="POST">
              <div class="row">
              <div class="form-group col-md-6">
                <label for="account_number">Account Number</label>
                <input type="text" name="account_number" class="form-control " id="" placeholder="" value="<?php echo get_user_meta(3, 'account_number',true)?>">

              </div>

              <div class="form-group col-md-6">
                <label for="account_name">Account Name</label>
                <input type="text" name="account_name" class="form-control " id="" placeholder="" value="<?php echo get_user_meta(3, 'account_name',true)?>">
              </div>


          <div class="form-group col-md-12">
            <label for="bank_name">Bank Name</label>
            <input type="text" name="bank_name" class="form-control " id="" placeholder="" value="<?php echo get_user_meta(3, 'bank_name',true)?>">

            <input type="submit" class="btn btn-success btn-block btn-active" style="margin-top:15px;" value="Update Details">
          </div>

            </form>

            <?php
            //var_dump($_POST);

            $updated = false;
              if(!empty($_POST['account_number'])){
                $update_ac_number = sanitize_text_field($_POST['account_number']);
                $update_ac_name =   sanitize_text_field($_POST['account_name']);
                $update_bank_name = sanitize_text_field($_POST['bank_name']);
                //save the updates

                $user_update = array(
                  'account_name'=>$update_ac_name,
                  'account_number'=>$update_ac_number,
                  'bank_name'=>$update_bank_name
                );
                foreach($user_update as $key=>$value){
                  $update = update_user_meta($user_id, $key, $value );
                  $updated = true;
                }
                if($updated == "true"){
                  echo "<script>alert('Your Payment Information has been updated')</script>";
                }
              }?>
              </div>
        </div>

        <div class="tab-pane" id="logs" style="width:75%;">
          <?php
          $sn = 1;
          global $wpdb;
          $query ="SELECT * FROM investpro_trans_log WHERE user_id = $logged_in_id ";
          $get_logs = $wpdb->get_results($query, OBJECT);
          //var_dump($get_logs);
          ?>


          <table class="table">
            <thead>
              <tr><th>S/N</th><th>Transaction Type</th><th>Transaction Date</th><th>Transaction Amount ( ₦ )</th><th>Account Balance( ₦ )</th><th>Status</tr>
              <?php foreach($get_logs as $logs){
                echo "<tr><td>".$sn++."</td><td>".$logs->trans_type."</td><td>".$logs->trans_date."</td><td>".$logs->amount."</td><td>".$logs->account_balance_after."</td><td>".$logs->trans_remark."</td></tr>";
              }?>

          </table>
        </div>

  </div>
</div>





<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
