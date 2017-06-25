<?php $is_admin_role = wp_get_current_user()->roles;
if(!in_array('administrator', $is_admin_role )){
   _e("You can't access this page", 'investpro');
  exit;
}
?>
<div class="container" style="width:100%;">
    <ul class="nav nav-tabs">
      <li class="active"><a href="#users" data-toggle="tab">All Users</a></li>
      <li><a href="#fund_user" data-toggle="tab">Fund User</a></li>
      <li><a href="#find_user" data-toggle="tab">Find A User</a></li>
      <li><a href="#payment_requests" data-toggle="tab">Payment Requests</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" id="users">
        <h3>All Users are listed Below</h3>
        <table class="table-hover">
          <tr>
          <th>S/N</th><th>Email Address</th><th>Phone Number</th><th>FullName</th><th>Account Name</th><th>Account Number</th><th>Bank</th>
        </tr>
        <tbody>
          <tr>

        <?php
        $args = array(
          'meta_query'=>array(
            'phone_number',
            'account_name',
            'account_number',
          )
        );

        $users = new WP_User_Query($args);
        if(!empty($users->results)){
          $sn = 1;
          foreach ($users->results as $user) {
            echo "<tr><td>".$sn++."</td><td>".$user->user_email."</td><td>".$user->phone_number."</td><td>".$user->first_name. " ". $user->last_name."</td><td>".$user->account_name."</td><td>".$user->account_number.".00"."</td><td>".$user->bank_name."</td></tr>";
          }
          echo "</tbody></table>";
        }

        else{
          echo "No Users found";
        }
        //var_dump($users->results);
        ?>
      </div>

      <div class="tab-pane" id="fund_user">
        <h3 style="margin-top:15px;"> Fund A user -> Use The form Below </h3>
        <small> Fund User After Payment to Bank using the form below, provide their email and amount to fund </small>
        <form class="" action="" method="post">
          <div class="row">
          <div class="form-group col-md-4">
            <!--<label for="user_email">User Email</label>-->
            <input type="text" name="user_id" class="form-control" id="" placeholder="Provide User ID">
          </div>

          <div class="form-group col-md-4">
            <!--<label for="user_email">User Email</label>-->
            <input type="text" name="user_amount" class="form-control" id="" placeholder="Amount">
          </div>
            <div class="col-md-4">
                  <input type="submit" class="btn btn-success btn-block" value="Fund User">
            </div>
            <input type="hidden" name="action" value="fund_user">
        </form>
        <?php
        global $wpdb;
        $wpdb->show_errors();
        $trans_updated_logged = false;
        $date = current_time('Y-m-d');
        if(!empty($_POST['user_amount'])){
          $fund_amount = $_POST['user_amount'];
          $user_id = $_POST['user_id'];

            //get current_user account balance
            $old_account_bal = get_user_meta($user_id, 'account_balance', true);
            $new_account_bal = $old_account_bal + $fund_amount ;
            $log_details = array(
            'amount'=>$fund_amount,
            'account_balance_after'=>$new_account_bal,
            'trans_type'=>'Deposit',
            'trans_date'=>$date,
            'user_id'=>$user_id,
            'trans_remark'=>'SUCCESS'
          );
          $insert_format = array(
            '%d',
            '%s',
             '%s',
             '%s',
             '%s',
             '%s',
          );
          //save the update account balance
          update_user_meta($user_id, 'account_balance', $new_account_bal);

          //record/log the transactions
          //$wpdb->insert($table_name, $data, $data_format);
          if($wpdb->insert('investpro_trans_log', $log_details, $insert_format)){
            echo "<script>alert('User account was successfully funded')</script>";
          }



        }
        ?>
    </div>
      </div>

      <div class="tab-pane" id="find_user">
        <h3> Provide User Email To Find Him / Her </h3>
        <div class="row">
        <div class="col-md-8">
        <form class="" action="" method="post">
        <div class="form-group">
        <input type="text" name="find_user_id" class="form-control" id="" placeholder="Provide User ID">
      </div>
      <div class="form-group ">
      <input type="submit" name="find_user" class="form-control btn btn-info btn-active" value="Find User">
        </div>
      </div>

      </div>
        </form>
        <?php if(!empty($_POST['find_user_id'])){
            $meta_id = $_POST['find_user_id'];
          $args = array('include'=>$_POST['find_user_id'],

        'meta_query'=>array(
          'phone_number',
          'last_name',
          'first_name',
          'account_name',
          'account_number',
          'bank_name',
        )
      );

        echo "<h2 class='text-center'>USER'S PROFILE</h2>";
        echo "<table><thead><th>Full Name</th><th>Phone Number</th><th>Email Address</th><th>Account Number</th><th>Account Name</th><th>Bank Name</th><th>Account Balance";


          $find_user = new WP_User_Query($args);
          $user_info = $find_user->results;
          foreach($user_info as $user_i){
            echo "<tr><td>".$user_i->first_name . " ". $user_i->last_name."</td><td>".$user_i->phone_number."</td><td>".$user_i->user_email."</td><td>".$user_i->account_number."</td><td>".$user_i->account_name."</td><td>".$user_i->bank_name."</td><td>".$user_i->account_balance."</td></tr>";
          }


        ?>
      </table>
      <?php
      $sn = 1;
      global $wpdb;
      $query = "SELECT * FROM investpro_trans_log WHERE user_id = $meta_id ";
      $get_logs = $wpdb->get_results($query, OBJECT);
      //var_dump($get_logs);
      ?>
      <h3 class="text-center"> USER'S TRANSACTION HISTORY</h3>
      <table class="table">
        <thead>
          <tr><th>S/N</th><th>Transaction Type</th><th>Transaction Date</th><th>Transaction Amount ( ₦ )</th><th>Account Balance( ₦ )</th><th>Status</tr>
          <?php foreach($get_logs as $logs){
            echo"<tr><td>".$sn++."</td><td>".$logs->trans_type."</td><td>".$logs->trans_date."</td><td>".$logs->amount."</td><td>".$logs->account_balance_after."</td><td>".$logs->trans_remark."</td></tr>";
          }?>

      </table>
      <?php } ?>
      </div>

    <div class="tab-pane " id="payment_requests">
      <h3> User's Payment Request List </h3>
    <?php   echo "<table><thead><th>S/N<th>Full Name</th><th>Email Address</th><th>Request Amount</th><th>Account Balance</th><th>User ID</th><tbody>"; ?>

      <?php
      $sn = 1;
      global $wpdb;
      $payment_requests = $wpdb->get_results("SELECT * FROM investpro_payment_requests", OBJECT);
      foreach($payment_requests as $pay_request){  echo "<tr><td>".$sn++."</td><td>".$pay_request->first_name." ".$pay_request->last_name.
        "</td><td>".$pay_request->user_email."</td><td>".$pay_request->request_amount."</td><td>".$pay_request->account_balance."</td><td>".$pay_request->user_id."</td><tr>";
      }

      ?>
    </tbody>
  </table>
    </div>

    </div>
  </div>



<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
