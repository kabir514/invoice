<?php
//print_invoice.php
if(isset($_GET["pdf"]) && isset($_GET["id"]))
{
 require_once ('newlife_pdf.php');
 include("newlife_database_connection.php");
 $output = '';
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
  $output .= '
   <table width="100%" border="0" cellpadding="5" cellspacing="0">
    <tr>
     <td colspan="2" align="center" style="font-size:20px" font-color="green"><b>New Life Diagnostic & Diagnostic Centre</b></td>
    </tr>
	<tr>
     <td colspan="2" align="center" style="font-size:15px" font-color="green"><b>Ashulia New Market, Ashulia, Dhaka</b></td>
    </tr>
	<tr>
     <td colspan="2" align="center" style="font-size:12px" font-color="green"><b>Phone Number: 01787-414311, 01840-894019</b></td>
    </tr>
	
    <tr>
     <td colspan="2">
      <table width="100%" cellpadding="5">
       <tr>
        <td width="65%">
         Patient Name : '.$row["patient_name"].'<br /> 
         Age : '.$row["age"].'<br />
		 Sex : '.$row["sex"].'<br/>
		 Phone Number : '.$row["phn_no"].'<br/>
		 Doctors Name : '.$row["doctor"].'<br/>
		 Department : '.$row["department"].'<br/>
		 
        </td>
        <td width="35%">
         <br />
         Memo No. : '.$row["order_no"].'<br />
         Date & Time : '.$row["order_date"].'<br />
        </td>
       </tr>
      </table>
      <br />
	  <table width="100%" border="0" cellpadding="6" cellspacing="0">
       <tr>
        <th>Sr No.</th>
        <th>Test Name</th>
        <th>Amount</th>
       </tr>
	   <tr>
        <th></th>
        <th></th>
		</tr>
		';
  $statement = $connect->prepare(
   "SELECT * FROM tbl_order_item 
   WHERE order_id = :order_id"
  );
  $statement->execute(
   array(
    ':order_id'       =>  $_GET["id"]
   )
  );
  $item_result = $statement->fetchAll();
  $count = 0;
  foreach($item_result as $sub_row)
  {
   $count++;
   $output .= '
   <tr>
    <td>'.$count.'</td>
    <td>'.$sub_row["test_name"].'</td>
    <td>'.$sub_row["order_item_actual_amount"].'</td>
   </tr>
   ';
  }
  $output .= '

  <tr>
   <td colspan="2" ><b>Total Bill</b></td>
   <td align="left">'.$row["order_total_before_less"].'</td>
  </tr>
 
  <tr>
   <td colspan="2"><b>Discunt</b></td>
   <td align="left">'.$row["order_total_less"].'</td>
  </tr>
  <tr>
   <td colspan="2"><b>Net Bill</b></td>
   <td align="left">'.$row["order_total_after_less"].'</td>
  </tr>
  <tr>
	<td colspan="2" margin-right="30"><b>Paid Amount :</b></td>
  </tr>
  <tr>
	<td colspan="2"><b>Due Balance :</b></td>
  </tr>
  ';
  $output .= '
      </table>
     </td>
    </tr>
   </table>
  ';
 }
 $pdf = new Pdf();
 $file_name = 'Invoice-'.$row["order_no"].'.pdf';
 $pdf->loadHtml($output);
 $pdf->render();
 $pdf->stream($file_name, array("Attachment" => false));
}
?>
