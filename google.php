<?php
    require_once 'config.php';
    session_start();
    require_once 'Zend/Loader.php';

    $title = $_GET['title'];
    $id = $_GET['id'];
    $gdoc = $_GET['gdoc'];
    $uid = $_SESSION['uid'];
    Zend_Loader::loadClass('Zend_Gdata');
    Zend_Loader::loadClass('Zend_Gdata_AuthSub');
    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
    Zend_Loader::loadClass('Zend_Gdata_Docs');

        $service = Zend_Gdata_Docs::AUTH_SERVICE_NAME;
        $client = Zend_Gdata_ClientLogin::getHttpClient($guser, $gpass, $service);
        $AuthToken = $client->getClientLoginToken();
        //echo $AuthToken;
       $con= mysqli_connect($database,$user,$password, 'bhsjacke_jackpack');
    // Check connection
    if (mysqli_connect_errno())
      {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      }
      $sql = "SELECT * FROM document WHERE id=" . $_GET['id'];
      $res = mysqli_query($con, $sql);
      $doc = mysqli_fetch_assoc($res);
      
      $sql = "SELECT * FROM document_sharing WHERE doc=" . $_GET['id'];
      $res = mysqli_query($con, $sql);
      while ($share = mysqli_fetch_assoc($res)) {
          if ($share['user'] == $_SESSION['id']) { header("Location: https://docs.google.com/document/d/" . $doc['google_docs'] . "'/edit'"); }
      }

    
    if (!$doc['google_docs']) 
        {
    $url = 'https://docs.google.com/feeds/documents/private/full/';
    $ch = curl_init($url);
    $xml = "<?xml version='1.0' encoding='UTF-8'?>
    <atom:entry xmlns:atom='http://www.w3.org/2005/Atom' xmlns:docs='http://schemas.google.com/docs/2007'>
      <atom:category scheme='http://schemas.google.com/g/2005#kind'
          term='http://schemas.google.com/docs/2007#document' label='document'/>
      <atom:title>" . $title . "</atom:title>
      <docs:writersCanInvite value='false' />
    </atom:entry>";

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/atom+xml',
                        'Authorization: GoogleLogin auth=' . $AuthToken,
                        'GData-Version: 2.0'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

   $xml = curl_exec($ch);
   $xml = preg_replace('~(</?|\s)([a-z0-9_]+):~is', '$1$2_', $xml);
   $parse = new SimpleXMLElement($xml);
   $docs = explode(':', $parse->gd_resourceId[0]);
   $doc = $docs[1];
   //echo $doc;
   curl_close($ch);
   $doc = $docs[1];
      
     
  $sql = "UPDATE document SET google_docs='$doc', state='In Progress' WHERE id='$id'";
  mysqli_query($con, $sql);
  $sql = "INSERT INTO document_sharing (doc, user) VALUES ('$doc', '$uid') ";
  mysqli_query($con, $sql);
  header('Location: https://docs.google.com/document/d/' . $doc . '/edit' );

  
}

else
{
   
                $url = 'https://docs.google.com/feeds/acl/private/full/document%3A' . $gdoc . '/batch'; 
                $xml = '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:gAcl="http://schemas.google.com/acl/2007" xmlns:batch="http://schemas.google.com/gdata/batch">
<category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/acl/2007#accessRule"/>\
<entry><id>' . $url . '/user:' . $guser . '</id><batch:operation type="query"/></entry>'; $index = 1;
            //$sql = "SELECT email FROM Users WHERE pid= $uid";
            //$res = mysqli_query($con, $sql);
            //while ($row = mysqli_fetch_assoc($res)) {
            $xml .= '<entry><batch:id>' . ($index++) .'</batch:id><batch:operation type="insert"/><gAcl:role value="writer"/><gAcl:scope type="user" value="' . $_SESSION['email'] . '"/></entry>';
            $xml .= '</feed>';
            //echo $xml;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/atom+xml',
                                'Authorization: GoogleLogin auth=' . $AuthToken,
                                'GData-Version: 2.0'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch); 
            $sql = "INSERT INTO document_sharing (doc, user) VALUES ('$gdoc', '$uid') ";
            mysqli_query($con, $sql);
            header('Location: https://docs.google.com/document/d/' . $gdoc . '/edit' );
}

?>
