<?php 
session_start(); 
require 'config.php';
$con= mysqli_connect($database,$user,$password, 'bhsjacke_jackpack');
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  function getUser ($id, $c) { $u =mysqli_query($c,"SELECT first,last FROM Users WHERE pid='$id'");
  $user = mysqli_fetch_assoc($u);
  return $user['first'] . " " . $user['last'];
 
  }
  function statebump($id, $title, $state)
  {
      require 'PHPMailerAutoload.php';
        function send ($to, $tkn) {
            $mail = new PHPMailer;
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $guser;                            // SMTP username
            $mail->Password = $gpass;                           // SMTP password
            $mail->SMTPSecure = 'tls';    
            $mail->Port = 587;
            $mail->setFrom($guser, $brand);
            $mail->FromName = $brand;
            $mail->addAddress($to);   
            $mail->isHTML(true); 
            $mail->Subject = $brand . ': ' . $title. " is now " . $state;
            $mail->Body    = "View your documents <a href ='" . $public . "'>here</a>";

            if(!$mail->send()) {
               echo 'Message could not be sent.';
               echo 'Mailer Error: ' . $mail->ErrorInfo;
               return FALSE;
        }
           else {return TRUE; }


        }
  }
  $sql = "SELECT name FROM issues ORDER by PID";
  $res = mysqli_query($con, $sql);
  $row = mysqli_fetch_assoc($res);
  if ($_GET['message']!='') { echo "<div class = 'span16 alert-message success' data-alert='alert'>" . $_GET['message'] . "</div>"; }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>JackPack <?php if (isset($cur)){echo $cur;} ?></title>
      <link rel="stylesheet" href="css/bootstrap.css" type="text/css" media="screen" title="no title" charset="utf-8"/>
      <link rel="stylesheet" href="css/style.css" type="text/css" media="screen" title="no title" charset="utf-8"/>
      <script type="text/javascript" charset="utf-8" src="js/jquery-1.7.1.min.js"></script>
      <script type="text/javascript" charset="utf-8" src="js/bootstrap-modal.js"></script>
      <script type="text/javascript" charset="utf-8" src="js/bootstrap-dropdown.js"></script>
      <script type="text/javascript" charset="utf-8" src="js/bootstrap-alerts.js"></script>
      <script type="text/javascript" charset="utf-8" src="js/script.js"></script>
    </head>
    <body>
       <div class="topbar" data-scrollspy="scrollspy">
      <div class="topbar-inner">
        <div class="container">
          <a class="brand" href="<? echo $public; ?>">JackPack</a>
          <ul class="nav">
              <li><a href="<? echo $public; ?>">Home</a></li>
            <?php if ($_SESSION['role'] == 'editor'){ ?>
            <li data-dropdown="dropdown" class ="dropdown">
              <a href="document.php" class="menu">Assignments</a>
              <ul class="dropdown-menu">
                <?php $sql = "SELECT * FROM issues";
                        if ($result = $con->query($sql)) {
                            $issues = array();
                            /* fetch associative array */
                            while ($row = $result->fetch_assoc()) {
                                array_push($issues,$row['name']);
                                
                ?>
                <li><a href="document.php?issue=<?php echo $row['PID'] ?>"><?php echo $row['name']; ?></a></li>
                <?php } }?>
                <?php //if ($_SESSION['role'] == 'editor') ?>
                <li class="divider"></li>
                <li>
                  <form method="post" action="issue.php">
                    <input type="text" name="issue[name]" value="" id="issue-name" class="undefined">
                    <input type="submit" name="op" value="+" id="issue" class="btn">
               </form>
                </li>
              </ul>
            </li>
            <li class ="users"><a href="users.php">Users</a></li>
            <?php } if (isset($_SESSION['role'])) { ?>
            <li class ="me"><a href="me.php">My profile</a></li>
            <li class ="reference"><a href="reference.php">Reference</a></li>
            <li><a href="logout.php">Logout</a></li> <? } ?>
          </ul>
        </div>
      </div>
    </div>