<?php
error_reporting(1);
require_once 'rpc.php';

$max_request_per_day = "1";
$send_amount = "0.0001";

$db_host = "HOST";
$db_user = "USERNAME";
$db_pass = "PASSWORD";
$db_name = "DATABASE";

$coinrpc_server = "HOST";
$coinrpc_user = "USERNAME";
$coinrpc_password = "PASSWORD";
$coinrpc_port = "PORT";

$dbconn = mysqli_connect($db_host,$db_user,$db_pass)or die("<br /><b style=\"color:red;\"> >>> cannot connect to server - Sorry <<< </b>");
mysqli_select_db($dbconn, $db_name)or die("<br /><b style=\"color:red;\"> cannot select DB </b>");

if(isset($_POST['addr'])) { 
   $User_Address = addslashes(strip_tags($_POST['addr']));
   $date = date("n/j/Y");;
   $ip = $_SERVER['REMOTE_ADDR'];
   $datec = date('G');
   $sql1 = "SELECT * FROM riecoinfaucet WHERE datec='$datec'";
   $result = mysqli_query($dbconn, $sql1)or die("<br /><b style=\"color:red;\"> ERROR 1 </b>");
   $num_rows = mysqli_num_rows($result);
   if($num_rows>$max_request_per_day) {
      $onloader = ' onload="alert(\'It seams someone is trying to abuse us. Try again a later today.\')"';
   } else {
      if($User_Address!="") {
		 $sql2 = "SELECT * FROM riecoinfaucet WHERE date='$date' and address='$User_Address'";
         $result = mysqli_query($dbconn, $sql2)or die("<br /><b style=\"color:red;\"> ERROR 2 </b>");
         $num_rows = mysqli_num_rows($result);
         if($num_rows==0) {
			$sql3 = "SELECT * FROM riecoinfaucet WHERE ip='$ip' and date='$date'";
            $result = mysqli_query($dbconn, $sql3)or die("<br /><b style=\"color:red;\"> ERROR 3 </b>");
            $num_rows = mysqli_num_rows($result);
            if($num_rows==0) {
			   $sql4 = "SELECT * FROM riecoinfaucet WHERE date='$date'";
               $result = mysqli_query($dbconn, $sql4)or die("<br /><b style=\"color:red;\"> ERROR 4 </b>");
               $num_rows = mysqli_num_rows($result);
               if($num_rows==0) {
                  $coind = new RPCClient($coinrpc_user, $coinrpc_password, $coinrpc_server, $coinrpc_port);
                  $amount = floatval($send_amount);
                  $getbalance = $coind->getbalance();
                  if($getbalance > $amount) {
                     $txid = $coind->sendtoaddress($User_Address, $amount);
					 $sql5 = "INSERT INTO riecoinfaucet (date, datec, ip, address, txid, amount, paid) VALUES ('$date', '$datec', '$ip', '$User_Address', '$txid', '$amount', '1')";
                     $sql = mysqli_query($dbconn, $sql5)or die("<br /><b style=\"color:red;\"> ERROR cannot registered in DB </b>");
                     $onloader = ' onload="alert(\'Success, Riecoins sent. '.$txid.'\')"';
                  } else {
                     $onloader = ' onload="alert(\'The faucet has insufficient funds.\')"';
                  }
               } else {
                  $onloader = ' onload="alert(\'You already requested coins today. Try again tomorrow.\')"';
               }
            } else {
               $onloader = ' onload="alert(\'You already requested coins today. Try again tomorrow.\')"';
            }
         } else {
            $onloader = ' onload="alert(\'You already requested coins today. Try again tomorrow.\')"';
         }
      } else {
         $onloader = ' onload="alert(\'You did not enter an address. Try again!\')"';
      }
   }
}

$timestamp_now = strtotime('now');
$timestamp_tomorrow = strtotime('tomorrow');
$day_today_day = date('l',$timestamp_now);
$date_today_date = date('dS',$timestamp_now);
$day_today_time = date('g:i a',$timestamp_now);
$day_today = $day_today_time.' on '.$day_today_day.', the '.$date_today_date;
$date_tomorrow_date = date('dS',$timestamp_tomorrow);
$day_tomorrow_day = date('l',$timestamp_tomorrow);
$day_tomorrow = $day_tomorrow_day.', on the '.$date_tomorrow_date;
?>
<html>
<head>
   <title>Riecoin faucet</title>
   <script type="text/javascript" src="jquery/jquery-1.9.1.js"></script>
   <script type="text/javascript" src="jquery/jquery-ui.js"></script>
   <script type="text/javascript" src="jquery/jquery.timers-1.1.2.js"></script>
   <script type="text/javascript">
      $(document).ready(function(){
         $("#coina").everyTime(10, function(){
            $("#coina").animate({left:"700px"}, 5000).animate({left:"10"}, 5000);
         });
         $("#coinb").everyTime(10, function(){
            $("#coinb").animate({left:"700px"}, 4000).animate({left:"10"}, 4000);
         });
         $("#coinc").everyTime(10, function(){
            $("#coinc").animate({left:"700px"}, 3000).animate({left:"10"}, 3000);
         });
      });
   </script>
   <script type="text/javascript">
      function setaddr() {
         document.getElementById('addr').value = document.getElementById('setaddr').value;
      }
   </script>
   <style>
      .coin_box_rail {
         width: 800px;
         border-top: 4px solid #828790;
         height: 125px;
      }
      .coin_box {
         width: 800px;
         height: 0px;
         margin: 0px;
      }
      .coin {
         width: 88px;
         height: 105px;
         position: relative;
         top: -5px;
         left: 10px;
      }
      .targetmec {
         width: 88px;
         height: 105px;
         background: url('target_mec.png');
         border: 0px solid #FFFFFF;
      }
   </style>
</head>
<body<?php if(isset($onloader)) { echo $onloader; } ?>>
   <center>
   <h1>Riecoin Faucet</h1>
   <h3>1 - Put your Riecoin address</h3>
   <h3>2 - Click on Riecoin target</h3>
   <table style="width: 800px; height: 100px;">
      <tr>
         <td align="center">
            <table>
               <tr>
                  <td nowrap>Riecoin Address:</td>
                  <td style="padding-left: 10px;" nowrap><input type="text" name="setaddr" id="setaddr" placeholder="ric1qmkk3s074wnja8phhplv8uw7d76tql2f0qrvv4x" onclick="setaddr()" onkeyup="setaddr()" onkeydown="setaddr()" onchange="setaddr()" style="width: 400px; height: 22px;"></td>
               </tr>
            </table>
         </td>
      </tr>
   </table>
   <form method="POST" action="faucet.php">
   <input type="hidden" id="addr" name="addr" value="">
   <div align="left" class="coin_box_rail">
      <div class="coin_box">
         <div id="coina" class="coin"><input type="submit" name="submit" value="" class="targetmec"></div>
      </div>
      <div class="coin_box">
         <div id="coinb" class="coin"><input type="submit" name="submit" value="" class="targetmec"></div>
      </div>
      <div class="coin_box">
         <div id="coinc" class="coin"><input type="submit" name="submit" value="" class="targetmec"></div>
      </div>
   </div>
   </form>
   <p>It is <b><?php echo $day_today; ?></b>. Request again <b><?php echo $day_tomorrow; ?></b></p></center>
</body>
<footer>
<center>
<a href="https://riecoin.dev">Riecoin</a> - <a href="https://forum.riecoin.dev">Forum</a><br />
By Dekon
</center>
</footer>
</html>