<?php
 
 include('newlife_database_connection.php');

  $statement = $connect->prepare("
    SELECT * FROM tbl_order 
    ORDER BY order_id DESC
  ");

  $statement->execute();

  $all_result = $statement->fetchAll();

  $total_rows = $statement->rowCount();

  if(isset($_POST["create_invoice"]))
  { 
    $order_total_before_less = 0;
    $order_total_less = 0;
   // $order_total_less = 0;
    $order_total_after_less = 0;
    $statement = $connect->prepare("
      insert into tbl_order 
        (order_no, order_date, patient_name, age, sex, phn_no, doctor, department, refd_id, order_total_before_less, order_total_less, order_total_after_less, order_datetime)
        VALUES (:order_no, :order_date, :patient_name, :age, :sex, :phn_no, :doctor, :department, :refd_id, :order_total_before_less, :order_total_less, :order_total_after_less, :order_datetime)
    ");
    $statement->execute(
      array(
          ':order_no'               =>  trim($_POST["order_no"]),
          ':order_date'             =>  trim($_POST["order_date"]),
          ':patient_name'          =>  trim($_POST["patient_name"]),
          ':age'       =>  trim($_POST["age"]),
		  ':sex'       =>  trim($_POST["sex"]),
		  ':phn_no'       =>  trim($_POST["phn_no"]),
		  ':doctor'       =>  trim($_POST["doctor"]),
		  ':department'       =>  trim($_POST["department"]),
		  ':refd_id'       =>  trim($_POST["refd_id"]),
          ':order_total_before_less'       =>  $order_total_before_less,
          ':order_total_less'           =>  $order_total_less,
          ':order_total_after_less'        =>  $order_total_after_less,
          ':order_datetime'           =>  date("Y-m-d")
      )
    );
			$statement = $connect->query("SELECT LAST_INSERT_ID()");
			$order_id = $statement->fetchColumn();


      for($count=0; $count<$_POST["total_item"]; $count++)
      {
        $order_total_before_less = $order_total_before_less + floatval(trim($_POST["order_item_actual_amount"][$count]));

        $order_total_less = $order_total_less + floatval(trim($_POST["order_item_less_amount"][$count]));


        $order_total_after_less = $order_total_after_less + floatval(trim($_POST["order_item_final_amount"][$count]));

        $statement = $connect->prepare("
          insert into tbl_order_item
          (order_id, test_name, order_item_quantity, order_item_price, order_item_actual_amount, order_item_less_rate, order_item_less_amount, order_item_final_amount)
          VALUES (:order_id, :test_name, :order_item_quantity, :order_item_price, :order_item_actual_amount, :order_item_less_rate, :order_item_less_amount, :order_item_final_amount)
        ");

        $statement->execute(
          array(
            ':order_id'         =>  $order_id,
            ':test_name'        =>  trim($_POST["test_name"][$count]),
            ':order_item_quantity'          =>  trim($_POST["order_item_quantity"][$count]),
            ':order_item_price'           =>  trim($_POST["order_item_price"][$count]),
            ':order_item_actual_amount'       =>  trim($_POST["order_item_actual_amount"][$count]),
            ':order_item_less_rate'         =>  trim($_POST["order_item_less_rate"][$count]),
            ':order_item_less_amount'       =>  trim($_POST["order_item_less_amount"][$count]),
            ':order_item_final_amount'        =>  trim($_POST["order_item_final_amount"][$count])
          )
        );
      }
      $order_total_less = $order_total_less;

      $statement = $connect->prepare("
        UPDATE tbl_order 
        SET order_total_before_less = :order_total_before_less, 
        order_total_less = :order_total_less,  
        order_total_after_less = :order_total_after_less 
        WHERE order_id = :order_id 
      ");
      $statement->execute(
        array(
          ':order_total_before_less'     =>  $order_total_before_less,
          ':order_total_less'         =>  $order_total_less,
          ':order_total_after_less'      =>  $order_total_after_less,
          ':order_id'             =>  $order_id
        )
      );
      header("location:newlife_invoice.php");
  }

  if(isset($_POST["update_invoice"]))
  {
    $order_total_before_less = 0;
      $order_total_less = 0;
      $order_total_after_less = 0;
      
      $order_id = $_POST["order_id"];
      
      
      
      $statement = $connect->prepare("
                DELETE FROM tbl_order_item WHERE order_id = :order_id
            ");
            $statement->execute(
                array(
                    ':order_id'       =>      $order_id
                )
            );
      
      for($count=0; $count<$_POST["total_item"]; $count++)
      {
        $order_total_before_less = $order_total_before_less + floatval(trim($_POST["order_item_actual_amount"][$count]));
        $order_total_less = $order_total_less + floatval(trim($_POST["order_item_less_amount"][$count]));
        $order_total_after_less = $order_total_after_less - floatval(trim($_POST["order_item_final_amount"][$count]));
        $statement = $connect->prepare("
          insert into tbl_order_item 
          (order_id, test_name, order_item_quantity, order_item_price, order_item_actual_amount, order_item_less_rate, order_item_less_amount, order_item_final_amount) 
          VALUES (:order_id, :text_name, :order_item_quantity, :order_item_price, :order_item_actual_amount, :order_item_less_rate, :order_item_less_amount, :order_item_final_amount)
        ");
        $statement->execute(
          array(
            ':order_id'                 =>  $order_id,
            ':test_name'                =>  trim($_POST["test_name"][$count]),
            ':order_item_quantity'          =>  trim($_POST["order_item_quantity"][$count]),
            ':order_item_price'            =>  trim($_POST["order_item_price"][$count]),
            ':order_item_actual_amount'     =>  trim($_POST["order_item_actual_amount"][$count]),
            ':order_item_less_rate'         =>  trim($_POST["order_item_less_rate"][$count]),
            ':order_item_less_amount'       =>  trim($_POST["order_item_less_amount"][$count]),
            ':order_item_final_amount'      =>  trim($_POST["order_item_final_amount"][$count])
          )
        );
        $result = $statement->fetchAll();
      }
      $order_total_less = $order_total_less;
      
      $statement = $connect->prepare("
        UPDATE tbl_order 
        SET order_no = :order_no, 
        order_date = :order_date, 
        patient_name = :patient_name, 
        sex = :sex,
		phn_no = :phn_no,
		doctor = :doctor,
		department = :department,
		refd_id = :refd_id,
        order_total_before_less = :order_total_before_less, 
        order_total_less = :order_total_less,  
        order_total_after_less = :order_total_after_less 
        WHERE order_id = :order_id 
      ");
      
      $statement->execute(
        array(
          ':order_no'               =>  trim($_POST["order_no"]),
          ':order_date'             =>  trim($_POST["order_date"]),
          ':patient_name'        =>  trim($_POST["patient_name"]),
          ':age'     =>  trim($_POST["age"]),
		  ':sex'     =>  trim($_POST["sex"]),
		  ':phn_no'     =>  trim($_POST["phn_no"]),
		  ':doctor'     =>  trim($_POST["doctor"]),
		  ':department'     =>  trim($_POST["department"]),
		  ':refd_id'     =>  trim($_POST["refd_id"]),
          ':order_total_before_less'     =>  $order_total_before_less,
          ':order_total_less'          =>  $order_total_less,
          ':order_total_after_less'      =>  $order_total_after_less,
          ':order_id'               =>  $order_id
        )
      );
      
      $result = $statement->fetchAll();
            
      header("location:newlife_invoice.php");
  }

  if(isset($_GET["delete"]) && isset($_GET["id"]))
  {
    $statement = $connect->prepare("DELETE FROM tbl_order WHERE order_id = :id");
    $statement->execute(
      array(
        ':id'       =>      $_GET["id"]
      )
    );
    $statement = $connect->prepare(
      "DELETE FROM tbl_order_item WHERE order_id = :id");
    $statement->execute(
      array(
        ':id'       =>      $_GET["id"]
      )
    );
    header("location:newlife_invoice.php");
  }

  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/style.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/ac.js"></script>
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <style>
      /* Remove the navbar's default margin-bottom and rounded borders */ 
      .navbar {
      margin-bottom: 4px;
      border-radius: 0;
      }
      /* Add a gray background color and some padding to the footer */
      footer {
      background-color: #f2f2f2;
      padding: 25px;
      }
      .carousel-inner img {
      width: 100%; /* Set width to 100% */
      margin: auto;
      min-height:200px;
      }
      .navbar-brand
      {
      padding:5px 40px;
      }
      .navbar-brand:hover
      {
      background-color:#ffffff;
      }
      /* Hide the carousel text when the screen is less than 600 pixels wide */
      @media (max-width: 600px) {
      .carousel-caption {
      display: none; 
      }
      }
    </style>
  </head>
  <body>
    <style>
      .box
      {
      width: 100%;
      max-width: 1390px;
      border-radius: 5px;
      border:1px solid #ccc;
      padding: 15px;
      margin: 0 auto;                
      margin-top:50px;
      box-sizing:border-box;
      }
    </style>
    <link rel="stylesheet" href="css/datepicker.css">
    <script src="js/bootstrap-datepicker1.js"></script>
    <script>
      $(document).ready(function(){
        $('#order_date').datepicker({
          format: "yyyy-mm-dd",
          autoclose: true
        });
      });
    </script>
    <div class="container-fluid">
      <?php
      if(isset($_GET["add"]))
      {
      ?>
      <form method="post" id="invoice_form">
        <div class="table-responsive">
          <table class="table table-bordered">
            <tr>
              <td colspan="2" align="center"><h2 style="margin-top:10.5px">Create Invoice</h2></td>
            </tr>
            <tr>
                <td colspan="2">
                  <div class="row">
                    <div class="col-md-8">
                        <b>Patient's Name:</b><br />
                        <input type="text" name="patient_name" id="patient_name" class="form-control input-sm" placeholder="Enter Patient's Name" />
						<b>Age:</b><br />
						<input type="text" name="age" id="age" class="form-control input-sm" placeholder="Enter Age" />
						<b>Gender:</b><br />
                        <select name="sex" id="sex" class="form-control" placeholder="Enter sex">
							<option>M</option>
							<option>F</option>
						</select>
						<b>Phone Number:</b><br />
						<input type="text" name="phn_no" id="phn_no" class="form-control input-sm" placeholder="Enter Phone Number" />
						<b>Doctor's Name:</b><br />
                        <input type="text" name="doctor" id="doctor" class="form-control input-sm" placeholder="Enter Doctor's Name" />
						<b>Department:</b><br />
                        <input type="text" name="department" id="department" class="form-control input-sm" placeholder="Enter department's Name" />
						<b>References:</b><br />
						<input type="text" name="refd_id" id="refd_id" class="form-control input-sm" placeholder="Enter Refd Id" />
                    </div>
                    <div class="col-md-4">
                      Invoice No<br />
                      <input type="text" name="order_no" id="order_no" class="form-control input-sm" placeholder="Enter Invoice No." />
						Date
                      <input type="text" name="order_date" id="order_date" class="form-control input-sm" readonly placeholder="Select Invoice Date" />
                    </div>
                  </div>
                  <br />
                  <table id="invoice-item-table" class="table table-bordered">
                    <tr>
                      <th width="7%">Sr No.</th>
                      <th width="20%">Test Name</th>
                      <th width="5%">Quantity</th>
                      <th width="5%">Price</th>
                      <th width="10%">Actual Amt.</th>
                      <th width="12.5%" colspan="2">Less (%)</th>
                      <th width="12.5%" rowspan="2">Total</th>
                      <th width="3%" rowspan="2"></th>
                    </tr>
                    <tr>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th>Rate</th>
                      <th>Amt.</th>
                    </tr>
                    <tr>
                      <td><span id="sr_no">1</span></td>
                      <td><div class="test_name">
							<input type="text" name="test_name[]" id="test_name1" class="form-control input-sm" />
							<span class="close">Cancel</span>
							<div class="dialog">   
							</div>
							</div>
					</td>
                      <td><input type="text" name="order_item_quantity[]" id="order_item_quantity1" data-srno="1" class="form-control input-sm order_item_quantity" /></td>
                      <td><input type="text" name="order_item_price[]" id="order_item_price1" data-srno="1" class="form-control input-sm number_only order_item_price" /></td>
                      <td><input type="text" name="order_item_actual_amount[]" id="order_item_actual_amount1" data-srno="1" class="form-control input-sm order_item_actual_amount" readonly /></td>
                      <td><input type="text" name="order_item_less_rate[]" id="order_item_less_rate1" data-srno="1" class="form-control input-sm number_only order_item_less_rate" /></td>
                      <td><input type="text" name="order_item_less_amount[]" id="order_item_less_amount1" data-srno="1" readonly class="form-control input-sm order_item_less_amount" /></td>
                      <td><input type="text" name="order_item_final_amount[]" id="order_item_final_amount1" data-srno="1" readonly class="form-control input-sm order_item_final_amount" /></td>
                      <td></td>
                    </tr>
                  </table>
                  <div align="right">
                    <button type="button" name="add_row" id="add_row" class="btn btn-success btn-xs">+</button>
                  </div>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Total</td>
                <td align="right"><b><span id="final_total_amt"></span></b></td>
              </tr>
              <tr>
                <td colspan="2"></td>
              </tr>
			  <tr>
                <td colspan="2"></td>
              </tr>
              <tr>
                <td colspan="2" align="center">
                  <input type="hidden" name="total_item" id="total_item" value="1" />
                  <input type="submit" name="create_invoice" id="create_invoice" class="btn btn-info" value="Create" />
                </td>
              </tr>
          </table>
        </div>
      </form>
      <script>
      $(document).ready(function(){
        var final_total_amt = $('#final_total_amt').text();
        var count = 1;
        
        $(document).on('click', '#add_row', function(){
          count++;
          $('#total_item').val(count);
          var html_code = '';
          html_code += '<tr id="row_id_'+count+'">';
          html_code += '<td><span id="sr_no">'+count+'</span></td>';
          
          html_code += '<td><input type="text" name="test_name[]" id="test_name'+count+'" class="test_name form-control input-sm" /></td>';
          
          html_code += '<td><input type="text" name="order_item_quantity[]" id="order_item_quantity'+count+'" data-srno="'+count+'" class="form-control input-sm number_only order_item_quantity" /></td>';
          html_code += '<td><input type="text" name="order_item_price[]" id="order_item_price'+count+'" data-srno="'+count+'" class="form-control input-sm number_only order_item_price" /></td>';
          html_code += '<td><input type="text" name="order_item_actual_amount[]" id="order_item_actual_amount'+count+'" data-srno="'+count+'" class="form-control input-sm order_item_actual_amount" readonly /></td>';
          
          html_code += '<td><input type="text" name="order_item_less_rate[]" id="order_item_less_rate'+count+'" data-srno="'+count+'" class="form-control input-sm number_only order_item_less_rate" /></td>';
          html_code += '<td><input type="text" name="order_item_less_amount[]" id="order_item_less_amount'+count+'" data-srno="'+count+'" readonly class="form-control input-sm order_item_less_amount" /></td>';
          html_code += '<td><input type="text" name="order_item_final_amount[]" id="order_item_final_amount'+count+'" data-srno="'+count+'" readonly class="form-control input-sm order_item_final_amount" /></td>';
          html_code += '<td><button type="button" name="remove_row" id="'+count+'" class="btn btn-danger btn-xs remove_row">X</button></td>';
          html_code += '</tr>';
          $('#invoice-item-table').append(html_code);
        });
        
        $(document).on('click', '.remove_row', function(){
          var row_id = $(this).attr("id");
          var total_item_amount = $('#order_item_final_amount'+row_id).val();
          var final_amount = $('#final_total_amt').text();
          var result_amount = parseFloat(final_amount) - parseFloat(total_item_amount);
          $('#final_total_amt').less(result_amount);
          $('#row_id_'+row_id).remove();
          count--;
          $('#total_item').val(count);
        });

        function cal_final_total(count)
        {
          var final_item_total = 0;
          for(j=1; j<=count; j++)
          {
            var quantity = 0;
            var price = 0;
            var actual_amount = 0;
            var less_rate = 0;
			var less_amount = 0;
            var item_total = 0;
            quantity = $('#order_item_quantity'+j).val();
            if(quantity > 0)
            {
              price = $('#order_item_price'+j).val();
              if(price > 0)
              {
                actual_amount = parseFloat(quantity) * parseFloat(price);
                $('#order_item_actual_amount'+j).val(actual_amount);
                less_rate = $('#order_item_less_rate'+j).val();
                if(less_rate > 0)
                {
                  less_amount = parseFloat(actual_amount)*parseFloat(less_rate)/100;
                  $('#order_item_less_amount'+j).val(less_amount);
                }
                
                item_total = parseFloat(actual_amount) - parseFloat(less_amount);
                final_item_total = parseFloat(final_item_total) + parseFloat(item_total);
                $('#order_item_final_amount'+j).val(item_total);
              }
            }
          }
          $('#final_total_amt').text(final_item_total);
        }
		

        $(document).on('blur', '.order_item_price', function(){
          cal_final_total(count);
        });

        $(document).on('blur', '.order_item_less_rate', function(){
          cal_final_total(count);
        });

        $('#create_invoice').click(function(){
          if($.trim($('#patient_name').val()).length == 0)
          {
            alert("Please Enter Patient Name");
            return false;
          }

          if($.trim($('#order_no').val()).length == 0)
          {
            alert("Please Enter Invoice Number");
            return false;
          }

          if($.trim($('#order_date').val()).length == 0)
          {
            alert("Please Select Invoice Date");
            return false;
          }

          for(var no=1; no<=count; no++)
          {
            if($.trim($('#test_name'+no).val()).length == 0)
            {
              alert("Please Enter Test Name");
              $('#test_name'+no).focus();
              return false;
            }

            if($.trim($('#order_item_quantity'+no).val()).length == 0)
            {
              alert("Please Enter Quantity");
              $('#order_item_quantity'+no).focus();
              return false;
            }

            if($.trim($('#order_item_price'+no).val()).length == 0)
            {
              alert("Please Enter Price");
              $('#order_item_price'+no).focus();
              return false;
            }

          }

          $('#invoice_form').submit();

        });
		
      });
      </script>
      <?php
      }
      elseif(isset($_GET["update"]) && isset($_GET["id"]))
      {
        $statement = $connect->prepare("
          SELECT * FROM tbl_order 
            WHERE order_id = :order_id
            LIMIT 1
        ");
        $statement->execute(
          array(
            ':order_id'       =>  $_GET["id"]
            )
          );
        $result = $statement->fetchAll();
        foreach($result as $row)
        {
        ?>
        <script>
        $(document).ready(function(){
          $('#order_no').val("<?php echo $row["order_no"]; ?>");
          $('#order_date').val("<?php echo $row["order_date"]; ?>");
          $('#patient_name').val("<?php echo $row["patient_name"]; ?>");
          $('#age').val("<?php echo $row["age"]; ?>");
		  $('#sex').val("<?php echo $row["sex"]; ?>");
		  $('#phn_no').val("<?php echo $row["phn_no"]; ?>");
		  $('#doctor').val("<?php echo $row["doctor"]; ?>");
		  $('#refd_id').val("<?php echo $row["refd_id"]; ?>");
        });
        </script>
        <form method="post" id="invoice_form">
        <div class="table-responsive">
          <table class="table table-bordered">
            <tr>
              <td colspan="2" align="center"><h2 style="margin-top:10.5px">Edit Invoice</h2></td>
            </tr>
            <tr>
                <td colspan="2">
                  <div class="row">
                    <div class="col-md-8">
                        <b>Patient's Name</b><br />
                        <input type="text" name="patient_name" id="patient_name" class="form-control input-sm" placeholder="Enter Patient's Name" />
                        <b>Age:</b><br />
						<input type="text" name="age" id="age" class="form-control input-sm" placeholder="Enter Age" />
						<b>Gender:</b><br />
						<select name="sex" id="sex" class="form-control" placeholder="Enter sex">
							<option>M</option>
							<option>F</option>
						</select>

						<b>Phone Number:</b><br />
						<input type="text" name="phn_no" id="phn_no" class="form-control input-sm" placeholder="Enter Phone Number" />
						
						<b>Doctor's Name:</b><br />
                        <input type="text" name="doctor" id="doctor" class="form-control input-sm" placeholder="Enter Doctor's Name" />
						<b>Department:</b><br />
                        <input type="text" name="department" id="department" class="form-control input-sm" placeholder="Enter department's Name" />
						<b>References:</b><br />
						<input type="text" name="refd_id" id="refd_id" class="form-control input-sm" placeholder="Enter Refd Id" />
                    </div>
                    <div class="col-md-4">
                      Invoice No<br />
                      <input type="text" name="order_no" id="order_no" class="form-control input-sm" placeholder="Enter Invoice No." />
					  Date
                      <input type="text" name="order_date" id="order_date" class="form-control input-sm" readonly placeholder="Select Invoice Date" />
                    </div>
                  </div>
                  <br />
                  <table id="invoice-item-table" class="table table-bordered">
                    <tr>
                      <th width="7%">Sr No.</th>
                      <th width="20%">Test Name</th>
                      <th width="5%">Quantity</th>
                      <th width="5%">Price</th>
                      <th width="10%">Actual Amt.</th>
                      <th width="12.5%" colspan="2">Less (%)</th>
                      <th width="12.5%" rowspan="2">Total</th>
                      <th width="3%" rowspan="2"></th>
                    </tr>
                    <tr>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th>Rate</th>
                      <th>Amt.</th>
                    </tr>
                    <?php
                    $statement = $connect->prepare("
                      SELECT * FROM tbl_order_item 
                      WHERE order_id = :order_id
                    ");
                    $statement->execute(
                      array(
                        ':order_id'       =>  $_GET["id"]
                      )
                    );
                    $item_result = $statement->fetchAll();
                    $m = 0;
                    foreach($item_result as $sub_row)
                    {
                      $m = $m + 1;
                    ?>
                    <tr>
                      <td><span id="sr_no"><?php echo $m; ?></span></td>
                      <td><input type="text" name="test_name[]" id="test_name<?php echo $m; ?>" class="form-control input-sm" value="<?php echo $sub_row["test_name"]; ?>" /></td>
                      <td><input type="text" name="order_item_quantity[]" id="order_item_quantity<?php echo $m; ?>" data-srno="<?php echo $m; ?>" class="form-control input-sm order_item_quantity" value = "<?php echo $sub_row["order_quantity"]; ?>"/></td>
                      <td><input type="text" name="order_item_price[]" id="order_item_price<?php echo $m; ?>" data-srno="<?php echo $m; ?>" class="form-control input-sm number_only order_item_price" value="<?php echo $sub_row["order_item_price"]; ?>" /></td>
                      <td><input type="text" name="order_item_actual_amount[]" id="order_item_actual_amount<?php echo $m; ?>" data-srno="<?php echo $m; ?>" class="form-control input-sm order_item_actual_amount" value="<?php echo $sub_row["order_item_actual_amount"];?>" readonly /></td>
                      <td><input type="text" name="order_item_less_rate[]" id="order_item_less_rate<?php echo $m; ?>" data-srno="<?php echo $m; ?>" class="form-control input-sm number_only order_item_less_rate" value="<?php echo $sub_row["order_item_less_rate"]; ?>" /></td>
                      <td><input type="text" name="order_item_less_amount[]" id="order_item_less_amount<?php echo $m; ?>" data-srno="<?php echo $m; ?>" readonly class="form-control input-sm order_item_less_amount" value="<?php echo $sub_row["order_item_less_amount"];?>" /></td>
                      <td><input type="text" name="order_item_final_amount[]" id="order_item_final_amount<?php echo $m; ?>" data-srno="<?php echo $m; ?>" readonly class="form-control input-sm order_item_final_amount" value="<?php echo $sub_row["order_item_final_amount"]; ?>" /></td>
                      <td></td>
                    </tr>
                    <?php
                    }
                    ?>
                  </table>
                </td>
              </tr>
              <tr>
                <td align="right"><b>Total</td>
                <td align="right"><b><span id="final_total_amt"><?php echo $row["order_total_after_less"]; ?></span></b></td>
              </tr>
              <tr>
                <td colspan="2"></td>
              </tr>
              <tr>
                <td colspan="2" align="center">
                  <input type="hidden" name="total_item" id="total_item" value="<?php echo $m; ?>" />
                  <input type="hidden" name="order_id" id="order_id" value="<?php echo $row["order_id"]; ?>" />
                  <input type="submit" name="update_invoice" id="create_invoice" class="btn btn-info" value="Edit" />
                </td>
              </tr>
          </table>
        </div>
      </form>
      <script>
      $(document).ready(function(){
        var final_total_amt = $('#final_total_amt').text();
        var count = "<?php echo $m; ?>";
        
        $(document).on('click', '#add_row', function(){
          count++;
          $('#total_item').val(count);
          var html_code = '';
          html_code += '<tr id="row_id_'+count+'">';
          html_code += '<td><span id="sr_no">'+count+'</span></td>';
          
          html_code += '<td><input type="text" name="test_name[]" id="test_name'+count+'" class="test_name form-control input-sm" /></td>';
          
          html_code += '<td><input type="text" name="order_item_quantity[]" id="order_item_quantity'+count+'" data-srno="'+count+'" class="form-control input-sm number_only order_item_quantity" /></td>';
          html_code += '<td><input type="text" name="order_item_price[]" id="order_item_price'+count+'" data-srno="'+count+'" class="form-control input-sm number_only order_item_price" /></td>';
          html_code += '<td><input type="text" name="order_item_actual_amount[]" id="order_item_actual_amount'+count+'" data-srno="'+count+'" class="form-control input-sm order_item_actual_amount" readonly /></td>';
          
          html_code += '<td><input type="text" name="order_item_less_rate[]" id="order_item_less_rate'+count+'" data-srno="'+count+'" class="form-control input-sm number_only order_item_less_rate" /></td>';
          html_code += '<td><input type="text" name="order_item_less_amount[]" id="order_item_less_amount'+count+'" data-srno="'+count+'" readonly class="form-control input-sm order_item_less_amount" /></td>';
          html_code += '<td><input type="text" name="order_item_final_amount[]" id="order_item_final_amount'+count+'" data-srno="'+count+'" readonly class="form-control input-sm order_item_final_amount" /></td>';
          html_code += '<td><button type="button" name="remove_row" id="'+count+'" class="btn btn-danger btn-xs remove_row">X</button></td>';
          html_code += '</tr>';
          $('#invoice-item-table').append(html_code);
        });
        
        $(document).on('click', '.remove_row', function(){
          var row_id = $(this).attr("id");
          var total_item_amount = $('#order_item_final_amount'+row_id).val();
          var final_amount = $('#final_total_amt').text();
          var result_amount = parseFloat(final_amount) - parseFloat(total_item_amount);
          $('#final_total_amt').text(result_amount);
          $('#row_id_'+row_id).remove();
          count--;
          $('#total_item').val(count);
        });

        function cal_final_total(count)
        {
          var final_item_total = 0;
          for(j=1; j<=count; j++)
          {
            var quantity = 0;
            var price = 0;
            var actual_amount = 0;
            var less_rate = 0;
            var less_amount = 0;
            var item_total = 0;
            quantity = $('#order_item_quantity'+j).val();
            if(quantity > 0)
            {
              price = $('#order_item_price'+j).val();
              if(price > 0)
              {
                actual_amount = parseFloat(quantity) * parseFloat(price);
                $('#order_item_actual_amount'+j).val(actual_amount);
                less_rate = $('#order_item_less_rate'+j).val();
                if(less_rate > 0)
                {
                  less_amount = parseFloat(actual_amount)*parseFloat(less_rate)/100;
                  $('#order_item_less_amount'-j).val(less_amount);
                }
                
                item_total = parseFloat(actual_amount) - parseFloat(less_amount);
                final_item_total = parseFloat(final_item_total) + parseFloat(item_total);
                $('#order_item_final_amount'+j).val(item_total);
              }
            }
          }
          $('#final_total_amt').text(final_item_total);
        }

        $(document).on('blur', '.order_item_price', function(){
          cal_final_total(count);
        });

        $(document).on('blur', '.order_item_less_rate', function(){
          cal_final_total(count);
        });

        $('#create_invoice').click(function(){
          if($.trim($('#patient_name').val()).length == 0)
          {
            alert("Please Enter Reciever Name");
            return false;
          }

          if($.trim($('#order_no').val()).length == 0)
          {
            alert("Please Enter Invoice Number");
            return false;
          }

          if($.trim($('#order_date').val()).length == 0)
          {
            alert("Please Select Invoice Date");
            return false;
          }

          for(var no=1; no<=count; no++)
          {
            if($.trim($('#test_name'+no).val()).length == 0)
            {
              alert("Please Enter Test Name");
              $('#test_name'+no).focus();
              return false;
            }

            if($.trim($('#order_item_quantity'+no).val()).length == 0)
            {
              alert("Please Enter Quantity");
              $('#order_item_quantity'+no).focus();
              return false;
            }

            if($.trim($('#order_item_price'+no).val()).length == 0)
            {
              alert("Please Enter Price");
              $('#order_item_price'+no).focus();
              return false;
            }

          }

          $('#invoice_form').submit();

        });

      });
      </script>
        <?php 
        }
      }
      else
      {
      ?>
      <h3 align="center">Invoice Of New Life Diagnostic & Diabetis Center</h3>

      <br />
      <div align="right">
        <a href="newlife_invoice.php?add=1" class="btn btn-info btn-xs">Create</a>
      </div>
      <br />
      <table id="data-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Invoice No.</th>
            <th>Invoice Date</th>
            <th>Patient's Name</th>
			<th>Age</th>
			<th>Sex</th>
			<th>Phone Number</th>
			<th>Doctor's Name</th>
			<th>Department</th>
			<th>References</th>
            <th>Invoice Total</th>
            <th>Print</th>
            <th>Edit</th>
            <th>Delete</th>
          </tr>
        </thead>
        <?php
        if($total_rows > 0)
        {
          foreach($all_result as $row)
          {
            echo '
              <tr>
                <td>'.$row["order_no"].'</td>
                <td>'.$row["order_date"].'</td>
                <td>'.$row["patient_name"].'</td>
				<td>'.$row["age"].'</td>
				<td>'.$row["sex"].'</td>
				<td>'.$row["phn_no"].'</td>
				<td>'.$row["doctor"].'</td>
				<td>'.$row["department"].'</td>
				<td>'.$row["refd_id"].'</td>
                <td>'.$row["order_total_after_less"].'</td>
                <td><a href="newlife_print_invoice.php?pdf=1&id='.$row["order_id"].'">Print</a></td>
                <td><a href="newlife_invoice.php?update=1&id='.$row["order_id"].'"><span class="glyphicon glyphicon-edit"></span></a></td>
                <td><a href="#" id="'.$row["order_id"].'" class="delete"><span class="glyphicon glyphicon-remove"></span></a></td>
              </tr>
            ';
          }
        }
        ?>
      </table>
      <?php
      }
      ?>
    </div>
    <br>
    <footer class="container-fluid text-center">
      <p>@copyright Shahariar Kabir 2019, phone: +8801797630201</p>
	  <p>Gmail: shahariar.kabir.sagor@gmail.com</p>
    </footer>
  </body>
</html>
<script type="text/javascript">
  $(document).ready(function(){
    var table = $('#data-table').DataTable({
          "order":[],
          "columnDefs":[
          {
            "targets":[4, 5, 6],
            "orderable":false,
          },
        ],
        "pageLength": 25
        });
    $(document).on('click', '.delete', function(){
      var id = $(this).attr("id");
      if(confirm("Are you sure you want to remove this?"))
      {
        window.location.href="newlife_invoice.php?delete=1&id="+id;
      }
      else
      {
        return false;
      }
    });
  });

</script>

<script>
$(document).ready(function(){
$('.number_only').keypress(function(e){
return isNumbers(e, this);      
});
function isNumbers(evt, element) 
{
var charCode = (evt.which) ? evt.which : event.keyCode;
if (
(charCode != 46 || $(element).val().indexOf('.') != -1) &&     
(charCode < 48 || charCode > 57))
return false;
return true;
}
});
</script>
