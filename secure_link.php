<?php

include_once("../Includes/template.inc.php");
//------------------------------------------------------------------------------------
global $logged_in;
if ($logged_in == 0) {
  $_SESSION['PageTo'] = $_SERVER['SCRIPT_NAME'];
  exit("<script>window.location.href='../Home/index.php?err=ERR_NOT_LOGGEDIN';</script>");
}

/*
global $SecureLinkKey,$SecureLinkLocation,$SecureLinkLocations,$FreeEmail,$ManualPassword;
global $ErrorTempalate,$HTMLEmail,$YourCompany,$YourEmail;
global $SecureLinkLog,$mt,$ipaddr,$thispage,$thisurl,$NoExtraPath;
global $securelinkauth,$securelinkform,$password,$DownloadURL,$DownloadURLs;
$DownloadURL="../SecureLink/secure_link.php";
*/

// ---------------------------------------- General Settings -------------------------------------------------------------
$SecureLinkKey="finfisher!@#$%^";   												// Set to random string used to encrypt links

// Location where files are securely stored
switch($VisitedPage)
{
	case "product_documents.php":
	case "product_info.php":
		$SecureLinkLocation = $config['PRODUCT_DOCUMENTS_PATH'];
		break;
	case "product_update_info.php":
	case "product_update_download.php":
		$SecureLinkLocation = $config['PRODUCT_UPDATES_PATH'];
		break;
	case "my_support_requests.php":
		$SecureLinkLocation = $config['SUPPORT_REQUESTS_ATTACHMENTS_PATH'];
		break;
	case "home.php":
	case "contents1.php":
	case "products.php":
	case "news_events.php":
		$SecureLinkLocation = $config['NEWSLETTER_DOCUMENTS_PATH'];
		break;
	default:
		$SecureLinkLocation = "../UserFiles/File/";
}

// Location where files are securely stored
/*$DocumentsArray = array("product_documents.php", "product_info.php");
$SecureLinkLocation = (in_array($VisitedPage, $DocumentsArray))
										? $config['PRODUCT_DOCUMENTS_PATH']
										: (($VisitedPage == "my_support_requests.php") ? $config['SUPPORT_REQUESTS_ATTACHMENTS_PATH'] : $config['PRODUCT_UPDATES_PATH']);
*/

$ManualPassword="";                                  				// password used to access manual entry form
$ErrorTemplate="../Home/error.php";													// Optional error page template (html)

// The following variables must be set if you wish to use the email links feature
$YourCompany="FinFisher";																		// Your company name
$YourEmail="so@finfisher.com";          	                  // Your email address
$CopyEmail="support@finfisher.com";                            // Set an email address if you want to receive copies of emails sent to users
$EmailTemplate="";                                          // Optional html or text format email template
$HTMLEmail="Y";                                             // Set to Y to send the default email in HTML formator N to send in text format
$SecureLinkLog="";                                          // Optional text log file to store form entry fields
$NotifyTemplate="";                                         // Optional email template sent to admin. Leave blank for default template

$SecureLinkDownloadLog="";                                  // Optional text log to store downloads
$RequireTuring=1;                                           // Set to 1 to use Turing code. Set to 0 to disable
$NotifyDownloadEmail="";                                    // Email address to receive download notificaton email

// Optional list of email addresses / services to block if required. You can add delete from this list
$FreeEmail[]="yahoo.";
$FreeEmail[]="hotmail.";
$FreeEmail[]="gmail.";
$FreeEmail[]="altavista.";
$FreeEmail[]="prontomail.";
$FreeEmail[]="talk21.";
$FreeEmail[]="address.";
$FreeEmail[]="@mail.";
$FreeEmail[]="@australia.";
$FreeEmail[]="boardermail.";
$FreeEmail[]="@canada.";
$FreeEmail[]="bolt.";
$FreeEmail[]="dbzmail.";
$FreeEmail[]="etoast.";
$FreeEmail[]="fastmail.";
$FreeEmail[]="freemail.";
$FreeEmail[]="icqmail.";
$FreeEmail[]="jaydemail.";
$FreeEmail[]="keromail.";
$FreeEmail[]="linuxmail.";
$FreeEmail[]="lycos.";
$FreeEmail[]="myrealbox.";
$FreeEmail[]="netscape.";
$FreeEmail[]="popmail.";
$FreeEmail[]="themail.";
$FreeEmail[]="toast.";
$FreeEmail[]="webcity.";

// Supported mime types. These are only required for displaying these file types inline.
// SecureLink can download any file type using the Save As dialog box in the browser.
// You can add to this list as required.
$mt['.jpg']="image/jpeg";
$mt['.gif']="image/gif";
$mt['.cgm']="image/cgm";
$mt['.flv']="video/x-flv";
$mt['.gif']="image/gif";
$mt['.htm']="text/html";
$mt['.html']="text/html";
$mt['.txt']="text/plain";
$mt['.pdf']="application/pdf";
$mt['.mpg']="video/mpeg";
$mt['.mpeg']="video/mpeg";
$mt['.rm']="audio/x-pn-realaudio";
$mt['.wmv']="application/x-ms-wmv";
$mt['.swf']="application/x-shockwave-flash";
$mt['.mov']="video/quicktime";
$mt['.asf']="video/x-ms-asf";
$mt['.asx']="video/x-ms-asf";
$mt['.rm']="audio/x-realaudio";
$mt['.ram']="audio/x-pn-realaudio";
$mt['.rar']="application/x-rar-compressed";
$mt['.zip']="application/zip";

//------------------------------------------------------------------------------------------------------------------
@error_reporting (E_ALL ^ E_NOTICE);
if (!function_exists('get_headers'))
{
	function get_headers($url, $format=0)
	{
    $headers = array();
    $url = parse_url($url);
    $host = isset($url['host']) ? $url['host'] : '';
    $port = isset($url['port']) ? $url['port'] : 80;
    $path = (isset($url['path']) ? $url['path'] : '/') . (isset($url['query']) ? '?' . $url['query'] : '');
    $fp = fsockopen($host, $port, $errno, $errstr, 3);
    if ($fp)
    {
    	$hdr = "GET $path HTTP/1.1\r\n";
      $hdr .= "Host: $host \r\n";
      $hdr .= "Connection: Close\r\n\r\n";
      fwrite($fp, $hdr);
      while (!feof($fp) && $line = trim(fgets($fp, 1024)))
      {
      	if ($line == "\r\n") break;
        list($key, $val) = explode(': ', $line, 2);
        if ($format)
        	if ($val) $headers[$key] = $val; else $headers[] = $key;
        else $headers[] = $line;
      }
      fclose($fp);
     	return $headers;
    }
    return false;
	}
}

if ($YourEmail!="")
{
  if (!isset($EmailHeaderNoSlashR))
    $EmailHeaderNoSlashR=1;
  if ((!isset($ExtraMailParam)) && (strtolower(@ini_get("safe_mode")) != 'on') && (@ini_get("safe_mode") != '1'))
    $ExtraMailParam="-f ".$YourEmail;
  @ini_set(sendmail_from,$YourEmail);
}

if (!isset($ServerTimeAdjust)) $ServerTimeAdjust=300;
if (!empty($_GET)) while(list($name, $value) = each($_GET)) $$name = $value;
if (!empty($_POST)) while(list($name, $value) = each($_POST)) $$name = $value;
$found = false;
if (!empty($_REQUEST))
{
  reset($_REQUEST);
  while (list($namepair, $valuepair) = each($_REQUEST))
  {
    if ($namepair == "SecureLinkKey") $found = true;
    if ($namepair == "ManualPassword") $found = true;
    if ($namepair == "EmailTemplate") $found = true;
    if ($namepair == "SecureLinkLog") $found = true;
    if ($namepair == "SecureLinkDownloadLog") $found = true;
    if ($namepair == "YourEmail") $found = true;
    if ($namepair == "YourCompany") $found = true;
    if ($namepair == "RequireTuring") $found = true;
    if ($namepair == "AllowEmailOnce") $found = true;
    if ($namepair == "AllowIPOnce") $found = true;
    if (substr($namepair, 0, 15) == "SecureLinkLocation") $found = true;
    if (substr($namepair, 0, 9) == "FreeEmail") $found = true;
    if ($namepair == "ServerTimeAdjust") $found = true;
  }
}

if ($found)
{
  SecureLinkShowMessage($ErrorTemplate, "Access Denied.", $ErrorEmail);
  exit;
}

if (isset($_REQUEST['securelinkauth'])) $securelinkauth=$_REQUEST['securelinkauth'];
if (isset($_REQUEST['securelinkauthe'])) $securelinkauthe=$_REQUEST['securelinkauthe'];
if (isset($_REQUEST['securelinkform'])) $securelinkform=$_REQUEST['securelinkform'];
$ipaddr=$_SERVER['REMOTE_ADDR'];
$thispage=$_SERVER['PHP_SELF'];
$thisurl="http://".$_SERVER['HTTP_HOST'].$thispage;
// If ?orderform then request password
if ((isset($manualentry)) || (isset($MANUALENTRY)))
{
  print "<html><head><title>SecureLink URL Manual Entry Form</title></head><body>\n";
  print "<script language=\"JavaScript\">\n";
  print "<!-- JavaScript\n";
  print "function validateform(form)\n";
  print "{\n";
  print "  if (form.securelinkpassword.value==\"\")\n";
  print "  {\n";
  print "    alert(\"Please enter the password\")\n";
  print "    form.securelinkpassword.focus()\n";
  print "    return false\n";
  print "  }\n";
  print "  return true;\n";
  print "}\n";
  print "// - JavaScript - -->\n";
  print "</script>\n";
  print "<form name=\"form1\" method=\"post\" action=\"$thisurl\" onSubmit=\"return validateform(this)\">\n";
  print "<p align=\"left\"><font face=\"Arial\" color=\"#333399\"><span style=\"font-size:16pt;\"><b>SecureLink Manual Entry Form</b></span></font></p>\n";
  print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"10\" bgcolor=\"#DDE3F0\">\n";
  print "<tr><td><p><font face=\"Arial\" size=\"2\">Password</font></p></td>\n";
  print "<td><p><input type=\"password\" name=\"securelinkpassword\" maxlength=\"50\" size=\"30\"></p></td></tr><tr><td><p>&nbsp;</p></td>\n";
  print "<td align=\"right\"><p><input type=\"submit\" name=\"button1\" value=\"Login\"></p></td>\n";
  print "</tr></table></form><script language=\"JavaScript\">document.forms[0].securelinkpassword.focus()</script></body></html>\n";
  exit;
}

if (($ManualPassword!="") && (isset($securelinkpassword)) && ($ManualPassword!=$securelinkpassword))
{
  SecureLinkShowMessage($ErrorTemplate,"Incorrect password.");
  exit;
}

if (isset($securelinkauthe))
{
  if ($DownloadBackground!="")
  {
    if (($fh = @fopen($DownloadBackground, "r")))
    {
      $page = fread ($fh, 200000);
      fclose($fh);
      $page = eregi_replace("!!!link!!!","?securelinkauth=".$securelinkauthe, $page);
      $page = eregi_replace("<body", "<body onLoad=\"download()\"", $page);
      $redirectcode ="<script language=\"JavaScript\" type=\"text/javascript\">\n";
      $redirectcode.="function download()\n";
      $redirectcode.="{\n";
      $redirectcode.="  window.location=\"?securelinkauth=".$securelinkauthe."\"\n";
      $redirectcode.="}\n";
      $redirectcode.="</script>\n";
      $redirectcode.="</body>\n";
      $page = eregi_replace("</body>", $redirectcode, $page);
      print $page;
      exit;
    }
    else
      $securelinkauth=$securelinkauthe;
  }
  else
    $securelinkauth=$securelinkauthe;
}

if ($securelinkauth!="")
{
  // Request to access file
  SecureLinkGetFile($securelinkauth);
}

if ($securelinkform!="")
{
  // Request to send email with links
  SecureLinkEmailLinks($FreeEmail);
}

// If password entered and correct then display manual order form
if (($ManualPassword!="") && ($ManualPassword==$securelinkpassword))
{
  print "<html><head><title>SecureLink URL Manual Entry Form</title></head><body>\n";
  print "<script language=\"JavaScript\">\n";
  print "<!-- JavaScript\n";
  print "function validateform(form)\n";
  print "{\n";
  print "  if (form.f0.value==\"\")\n";
  print "  {\n";
  print "    alert(\"Please enter at least one file\")\n";
  print "    form.f0.focus()\n";
  print "    return false\n";
  print "  }\n";
  print "  if (form.newexpiry2.value!=\"\")\n";
  print "  {\n";
  print "    if (ValidChars(form.newexpiry2.value,\"0123456789\")==false)\n";
  print "    {\n";
  print "      alert(\"Please enter a valid expiry time or select from the menu\")\n";
  print "      form.newexpiry2.focus()\n";
  print "      return(false)\n";
  print "    }\n";
  print "    form.x.value=form.newexpiry2.value\n";
  print "  }\n";
  print "  else\n";
  print "  {\n";
  print "    form.x.value=form.newexpiry1.value\n";
  print "  }\n";

  print "  if (form.email.value!=\"\")\n";
  print "  {\n";
  print "    if (ValidateEmail(form.email.value)==false)\n";
  print "    {\n";
  print "      alert(\"Please enter a valid email address\")\n";
  print "      form.email.focus()\n";
  print "      return false\n";
  print "    }\n";
  print "  }\n";
  print "  if (ValidateIP(form.i.value)==false)\n";
  print "  {\n";
  print "    alert(\"Please enter a valid IP address or leave blank\")\n";
  print "    form.i.focus()\n";
  print "    return false\n";
  print "  }\n";
  print "  if (form.i.value==\"\")\n";
  print "    form.i.value=\"0.0.0.0\";\n";
  print "  if (form.email.value==\"\")\n";
  print "    form.securelinkform.value=\"\";\n";
  print "  return true\n";
  print "}\n";
  print "\n";
  print "function ValidateEmail(str)\n";
  print "{\n";
  print "    // are regular expressions supported?\n";
  print "    var supported = 0;\n";
  print "    if (window.RegExp) {\n";
  print "      var tempStr = \"a\";\n";
  print "      var tempReg = new RegExp(tempStr);\n";
  print "      if (tempReg.test(tempStr)) supported = 1;\n";
  print "    }\n";
  print "    if (!supported)\n";
  print "      return (str.indexOf(\".\") > 2) && (str.indexOf(\"@\") > 0);\n";
  print "    var r1 = new RegExp(\"(@.*@)|(\\\.\\\.)|(@\\\.)|(^\\\.)\");\n";
  print "    var r2 = new RegExp(\"^.+\\\@(\\\[?)[a-zA-Z0-9\\\-\\\.]+\\\.([a-zA-Z]{2,3}|[0-9]{1,3})(\\\]?)$\");\n";
  print "    return (!r1.test(str) && r2.test(str));\n";
  print "}\n";
  print "function ValidateIP(ip)\n";
  print "{\n";
  print "  if (ip!=\"\")\n";
  print "  {\n";
  print "    var ni\n";
  print "    if (ValidChars(ip,\"0123456789.\")==false)\n";
  print "      return(false)\n";
  print "    var ipparts=ip.split(\".\")\n";
  print "    if (ipparts.length!=4)\n";
  print "      return(false)\n";
  print "    for (var k=0; k<4; k++)\n";
  print "    {\n";
  print "      if (ipparts[k].length<1)\n";
  print "      return(false)\n";
  print "      if ((ipparts[k].charAt(0)==\"0\") && (ipparts[k].length>1))\n";
  print "      return(false)\n";
  print "    ni=parseInt(ipparts[k],10)\n";
  print "    if ((ni<0) || (ni>255))\n";
  print "      return (false)\n";
  print "    }\n";
  print "  }\n";
  print "    return(true)\n";
  print "}\n";
  print "\n";
  print "function ValidChars(str,valid)\n";
  print "{\n";
  print "  var v=true\n";
  print "  for (i=0;i<str.length;i++)\n";
  print "  {\n";
  print "    if (valid.indexOf(str.charAt(i))==-1)\n";
  print "    {\n";
  print "      v=false\n";
  print "      break\n";
  print "    }\n";
  print "  }\n";
  print "  return(v)\n";
  print "}\n";
  print "// - JavaScript - -->\n";
  print "</script>\n";
  print "<form name=\"form1\" method=\"post\" action=\"$thisurl\" onSubmit=\"return validateform(this)\">\n";
  print "<input type=\"hidden\" name=\"securelinkpassword\" value=\"$securelinkpassword\">\n";
  print "<input name=\"m\" type=\"hidden\" value=\"0\">\n";
  print "<input name=\"a\" type=\"hidden\" value=\"$securelinkpassword\">\n";
  print "<input name=\"g\" type=\"hidden\" value=\"\">\n";
  print "<input name=\"securelinkform\" type=\"hidden\" value=\"1\">\n";
  print "<p align=\"left\"><font face=\"Arial\" color=\"#333399\"><span style=\"font-size:16pt;\"><b>SecureLink Manual Entry Form</b></span></font></p>\n";
  print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"10\" bgcolor=\"#DDE3F0\">\n";
  for ($k=0;$k<=19;$k++)
  {
    print "<tr>\n";
    print "<td><p><font face=\"Arial\" size=\"2\">File ".($k+1)."</font></p></td>\n";
    $fnum="f".$k;
    $loc="l".$k;
    print "<td><p>";
    if ($$fnum!="")
    {
      print "<input name=\"f$k\" type=\"text\" value=\"".$$fnum."\">";
      if (!empty($SecureLinkLocations))
      {
        print "<font face=\"Arial\" size=\"2\">&nbsp;in location&nbsp;</font><select name=\"l$k\" size=\"1\">\n";
        if (($SecureLinkLocation!="") && ($$loc==""))
          print "<option selected value=\"\">Default</option>\n";
        if (($SecureLinkLocation!="") && ($$loc!=""))
          print "<option selected value=\"\">Default</option>\n";
        reset($SecureLinkLocations);
        while(list($namepair, $valuepair) = each($SecureLinkLocations))
        {
          $$namepair = $valuepair;
          if ($$loc==$namepair)
            print "<option selected value=\"".$namepair."\">".$namepair."</option>\n";
          else
            print "<option value=\"".$namepair."\">".$namepair."</option>\n";
        }
        print "</select>\n";
      }
      if ($email=="")
      {
        if ($x != 0)
        {
          if (strlen($x) == 12)
            $expirytime = mktime(substr($x, 8, 2), substr($x, 10, 2), 0, substr($x, 4, 2), substr($x, 6, 2), substr($x, 0, 4), -1);
          else
            $expirytime = time() + ($x * 60);
        }
        else
          $expirytime = 0;
        $plink=GetSecureLink($$fnum, $$loc,$expirytime, "LL_1", $i, $l, $SecureLinkKey, "1", $thisurl);
        $fnameonly=SecureLinkFileName($$fnum);
        print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$plink\" target=\"_securelink\">$fnameonly</a>";
      }
    }
    else
    {
      print "<input name=\"f$k\" type=\"text\" value=\"\">";
      if (!empty($SecureLinkLocations))
      {
        print "<font face=\"Arial\" size=\"2\">&nbsp;in location&nbsp;</font><select name=\"l$k\" size=\"1\">\n";
        if (($SecureLinkLocation!="") && ($$loc==""))
          print "<option selected value=\"\">Default</option>\n";
        if (($SecureLinkLocation!="") && ($$loc!=""))
          print "<option selected value=\"\">Default</option>\n";
        reset($SecureLinkLocations);
        while(list($namepair, $valuepair) = each($SecureLinkLocations))
        {
          $$namepair = $valuepair;
          if ($$loc==$namepair)
            print "<option selected value=\"".$namepair."\">".$namepair."</option>\n";
          else
            print "<option value=\"".$namepair."\">".$namepair."</option>\n";
        }
        print "</select>\n";
      }
    }
    print"</p></td>\n";
    print "</tr>\n";
  }
  print "<tr><td><p><font face=\"Arial\" size=\"2\">Expiry Time</font></p></td>\n";
  print "<input type=\"hidden\" name=\"x\" value=\"\">\n";
  print "<td><p><select name=\"newexpiry1\" size=\"1\">\n";
  if ($newexpiry1=="10")
    print "<option selected value=\"10\">10 minutes</option>\n";
  else
    print "<option value=\"10\">10 minutes</option>\n";
  if ($newexpiry1=="60")
    print "<option selected value=\"60\">1 Hour</option>\n";
  else
    print "<option value=\"60\">1 Hour</option>\n";
  if ($newexpiry1=="180")
    print "<option selected value=\"180\">3 Hours</option>\n";
  else
    print "<option value=\"180\">3 Hours</option>\n";
  if (($newexpiry1=="1440") || (!isset($x)))
    print "<option selected value=\"1440\">24 Hours</option>\n";
  else
    print "<option value=\"1440\">24 Hours</option>\n";
  if ($newexpiry1=="43200")
    print "<option selected value=\"43200\">30 Days</option>\n";
  else
    print "<option value=\"43200\">30 Days</option>\n";
  if ($newexpiry1=="525600")
    print "<option selected value=\"525600\">1 Year</option>\n";
  else
    print "<option value=\"525600\">1 Year</option>\n";
  if ($newexpiry1=="0")
    print "<option selected value=\"0\">No Expiry</option>\n";
  else
    print "<option value=\"0\">No Expiry</option>\n";
  print "</select>\n";
  print "<font face=\"Arial\" size=\"2\">&nbsp;or&nbsp;<input type=\"text\" name=\"newexpiry2\" value=\"$newexpiry2\" maxlength=\"12\" size=\"15\">\n";
  print "&nbsp;minutes</font></p></td></tr>\n";
  print "<tr><td><p><font face=\"Arial\" size=\"2\">IP address</font></p></td><td><p>\n";
  print "<font face=\"Arial\"><span style=\"font-size:10pt;\"><input type=\"text\" name=\"i\" maxlength=\"15\" size=\"30\">\n";
  print "<select name=\"l\" size=\"1\">\n";
  print "  <option selected value=\"0\">Level 0 (off)</option>\n";
  print "  <option value=\"1\">Level 1</option>\n";
  print "  <option value=\"2\">Level 2</option>\n";
  print "  <option value=\"3\">Level 3</option>\n";
  print "  <option value=\"4\">Level 4</option>\n";
  print "</select>\n";
  print "</span></font></p></td></tr>\n";
  print "<tr><td><p><font face=\"Arial\" size=\"2\">Email address</font></p></td><td><p>\n";
  print "<font face=\"Arial\"><span style=\"font-size:10pt;\"><input type=\"text\" name=\"email\" size=\"30\">&nbsp; Leave blank to display links only</span></font></p></td></tr>\n";
  if ($Emails!="")
  {
    print "<tr><td><p><font face=\"Arial\" size=\"2\">Email Template</font></p></td>\n";
    print "<td><p><select name=\"t\" size=\"1\">\n";
    if ($EmailTemplate!="")
    {
      if ($t=="")
        print "<option selected value=\"\">".SecureLinkFileName($EmailTemplate)."</option>\n";
      else
        print "<option value=\"\">".SecureLinkFileName($EmailTemplate)."</option>\n";
    }
    $hDirectory=opendir("$Emails");
    if ($hDirectory!=false)
    {
      while($entryname=readdir($hDirectory))
      {
        if (($entryname!=".") && ($entryname!="..") && ($entryname!=""))
          print "<option value=\"$entryname\">$entryname</option>";
      }
      closedir($hDirectory);
    }
    print "</select></p></td></tr>\n";
  }
  print "<tr><td><p><font face=\"Arial\"><span style=\"font-size:10pt;\">\n";
  print "&nbsp;</span></font></p></td>\n";
  print "<td><p align=\"right\"><font face=\"Arial\"><span style=\"font-size:10pt;\">\n";
  if ($email!="")
    print "Download links sent to $email&nbsp;&nbsp;";
  print "<input type=\"submit\" name=\"submit\" value=\"Submit\">\n";
  print "</span></font></p></td></tr></table></form>\n";
  print "<script language=\"JavaScript\">document.forms[0].f0.focus()</script>\n";
  print "</body></html>\n";
  exit;
}

function SecureLinkURL($fname,$expiry,$dialog,$iplevel=0)
{
  global $SecureLinkKey,$thispage,$ipaddr;
  if ($expiry != 0)
  {
    if (strlen($expiry) == 12)
      $expirytime = mktime(substr($expiry, 8, 2), substr($expiry, 10, 2), 0, substr($expiry, 4, 2), substr($expiry, 6, 2), substr($expiry, 0, 4), -1);
    else
      $expirytime = time() + ($expiry * 60);
  }
  else
    $expirytime = 0;
  $locs=explode(":",$fname);
  $plink=GetSecureLink($locs[0], $locs[1], $expirytime, "LL_0", $ipaddr, $iplevel, $SecureLinkKey, $dialog, $thispage);
  // print($plink);
  return $plink;
}

function SecureLinkURL_API($fname,$expiry,$dialog,$ip="",$iplevel=0)
{
  global $SecureLinkKey,$thispage,$NoExtraPath,$ipaddr;
  if ($ip=="")
    $ip=$ipaddr;
  if ($expiry != 0)
  {
    if (strlen($expiry) == 12)
      $expirytime = mktime(substr($expiry, 8, 2), substr($expiry, 10, 2), 0, substr($expiry, 4, 2), substr($expiry, 6, 2), substr($expiry, 0, 4), -1);
    else
      $expirytime = time() + ($expiry * 60);
  }
  else
    $expirytime = 0;
  $locs=explode(":",$fname);
  $plink=GetSecureLink($locs[0], $locs[1], $expirytime, "LL_0", $ip, $iplevel, $SecureLinkKey, $dialog, $thispage);
  return($plink);
}

function SecureLinkEmail($files,$expiry,$filter,$goto,$iplevel=0,$temp="")
{
	global $SecureLinkKey,$ipaddr;
	$created=(string)time();
	$fnames=explode(",",$files);
	$tohash=$SecureLinkKey;
	for ($k=0;$k<count($fnames);$k++)
	{
	 $locs=explode(":",$fnames[$k]);
	 print("<input name=\"f$k\" type=\"hidden\" value=\"$locs[0]\" \>\n");
	 print("<input name=\"l$k\" type=\"hidden\" value=\"$locs[1]\" \>\n");
	 $tohash.=$locs[0].$locs[1];
	}
	print("<input name=\"x\" type=\"hidden\" value=\"$expiry\" \>\n");
	$tohash.=$expiry;
	print("<input name=\"m\" type=\"hidden\" value=\"$filter\" \>\n");
	$tohash.=$filter;
	print("<input name=\"l\" type=\"hidden\" value=\"$iplevel\" \>\n");
	$tohash.=$iplevel;
	print("<input name=\"t\" type=\"hidden\" value=\"$temp\" \>\n");
	$tohash.=$temp;
	print("<input name=\"c\" type=\"hidden\" value=\"$created\" \>\n");
	$tohash.=$created;
	$hash=md5($tohash);
	print("<input name=\"g\" type=\"hidden\" value=\"$goto\" \>\n");
	print("<input name=\"a\" type=\"hidden\" value=\"$hash\" \>\n");
	print("<input name=\"securelinkform\" type=\"hidden\" value=\"1\" \>\n");
}

function SecureLinkEmail_API($email,$files,$expiry,$ip="",$iplevel=0,$template="")
{
	global $SecureLinkKey,$ipaddr,$Emails,$EmailTemplate;
	$fnames=explode(",",$files);
  for ($k=0;$k<count($fnames);$k++)
  {
   $loc=explode(":",$fnames[$k]);
   $fnames[$k]=$loc[0];
   $floc[$k]=$loc[1];
  }
  if ($ip=="")
    $ip=$ipaddr;
	if ($template!="")
	  $template=$Emails.$template;
	if (($template=="") && ($EmailTemplate!=""))
	  $template=$EmailTemplate;
	if ($template=="")
	{
	  $res=SecureLinkSendEmail($email, $email, $expiry, $ip, $iplevel, $fnames, $floc);
	  return($res);
	}
	else
	{
	  $res=SecureLinkSendEmailUsingTemplate($email, $template, $email, $expiry, $ip, $iplevel, $fnames, $floc);
	  return($res);
	}
}

function GetSecureLink($fname, $loc, $expirytime, $id, $ip, $ipl, $lkey, $dlog, $lurl)
{
  global $NoFilename, $DownloadURL, $ExtraPathFilename,$DownloadURLs;
  $auth=md5($lkey.$fname.$expirytime.$ip.$ipl.$id.$loc);
  $plink=$fname.",".$expirytime.",".$ip.",".$ipl.",".$dlog.",".$id.",".$loc.",".$auth;
  $plink=base64_encode($plink);
  $plink=rawurlencode($plink);
  if ($DownloadURL!="")
    $lurl=$DownloadURL;
  if ($DownloadURLs[$loc]!="")
    $lurl=$DownloadURLs[$loc];
  // Get filename only
  $fnameonly=SecureLinkAltFileName($fname);
  $fnameonly=basename($fnameonly);
  if($ExtraPathFilename==1)
    $plink=$lurl."/".$fnameonly."?securelinkauth=".$plink;
  else
    $plink=$lurl."?securelinkauth=".$plink;
  if ($NoFilename!=1)
    $plink .= "/".$fnameonly;
  return($plink);
}

function SecureLinkGetFile($securelinkauth)
{
  global $SecureLinkKey, $SecureLinkLocation,$ErrorTemplate,$ipaddr,$SecureLinkLocations,$NotifyDownloadEmail,$SecureLinkDownloadLog;
  global $ServerTimeAdjust;
  // Remove any /filename from end
  $pos = strrpos($securelinkauth, "/");
  if (is_integer($pos))
    $securelinkauth = substr($securelinkauth, 0, $pos);
  // Split securelinkauth into its parts
  $securelinkauth=rawurldecode($securelinkauth);
  $oldlink=false;
  if (is_integer(strpos($securelinkauth,",")))
    $oldlink=true;
  if ($oldlink==false)
  {
    $securelinkauth=base64_decode($securelinkauth);
  }
  $ip="";
  $iplevel="";
  $id="";
  $loc="";
  $fields=explode(",",$securelinkauth);
  $fname=$fields[0];
  $expirytime=$fields[1];
  if (count($fields)==4)
  {
    $dialog=$fields[2];
    $auth=$fields[3];
  }
  if (count($fields)==6)
  {
    $ip=$fields[2];
    $iplevel=$fields[3];
    $dialog=$fields[4];
    $auth=$fields[5];
  }
  if (count($fields)==8)
  {
    $ip=$fields[2];
    $iplevel=$fields[3];
    $dialog=$fields[4];
    $id=$fields[5];
    $loc=$fields[6];
    $auth=$fields[7];
  }
  // Verify hash value to ensure nothing tampered wth
  if (md5($SecureLinkKey.$fname.$expirytime.$ip.$iplevel.$id.$loc)!=$auth)
  {
     SecureLinkShowMessage($ErrorTemplate,"URL authentication failed");
     exit;
  }
  // Check link hasn't expired
  if ($expirytime!=0)
  {
    $curtime=time();
    if ($curtime>$expirytime)
    {
      SecureLinkShowMessage($ErrorTemplate,"Sorry but this link has been expired.\n");
      exit;
    }
  }
  if (($iplevel!=0) && ($ip!="0.0.0.0"))
  {
    $ipo[1]=strtok($ip,".");
    $ipo[2]=strtok(".");
    $ipo[3]=strtok(".");
    $ipo[4]=strtok(".");
    $ipn[1]=strtok($ipaddr,".");
    $ipn[2]=strtok(".");
    $ipn[3]=strtok(".");
    $ipn[4]=strtok(".");
    for ($k=1;$k<=$iplevel;$k++)
    {
      if ($ipo[$k]!=$ipn[$k])
      {
        SecureLinkShowMessage($ErrorTemplate,"This link IP address is not valid.\n");
        exit;
      }
    }
  }
  // Make full path or url to file
  $actualfname=SecureLinkAltFileName($fname);
  $fquery=SecureLinkFileQuery($fname);
  $ext=SecureLinkFileExtension(SecureLinkFileNamepath($fname));
  // If $loc blank then use default location
  if ($loc=="")
    $link=$SecureLinkLocation.SecureLinkFileNamepath($fname);
  else
    $link=$SecureLinkLocations[$loc].SecureLinkFileNamepath($fname);
  // If download path is for S3 then handle it now
  if (substr(trim(strtolower($link)),0,3)=="s3|")
  {
    if ($NotifyDownloadEmail != "")
      SecureLinkEmailDownloadNotify($actualfname, $id, $_SERVER['REMOTE_ADDR'],$expirytime);
    if ($SecureLinkDownloadLog!="")
      SecureLinkLogDownload($SecureLinkDownloadLog,$actualfname,$id);
    $url=SecureLinkGetS3URL($link,time()+$ServerTimeAdjust,"GET");
    header("Location: ".$url);
    exit;
  }
  // If download link is html or php page then just include it.
  if (($ext==".php") || ($ext==".html") || ($ext==".htm"))
  {
    if ($NotifyDownloadEmail != "")
      SecureLinkEmailDownloadNotify($actualfname, $id, $_SERVER['REMOTE_ADDR'],$expirytime);
    if ($SecureLinkDownloadLog!="")
      SecureLinkLogDownload($SecureLinkDownloadLog,$actualfname,$id);
    // If there are any GET variables in the filename then set those in $_GET and $_REQUEST
    if ($fquery!="")
    {
      $fvars=explode("&",$fquery);
      for ($k=0;$k<count($fvars);$k++)
      {
        $fvar=strtok($fvars[$k],"=");
        $fval=strtok("=");
        if ($fvar!="")
        {
          $_GET[$fvar]=$fval;
          $_REQUEST[$fvar]=$fval;
        }

      }
    }
    include ($link);
    exit;
  }
  // See if link is local path or URL
  $pos=strpos(strtolower($link),"http://");
  $pos2=strpos(strtolower($link),"ftp://");
  if ((!is_integer($pos)) && (!is_integer($pos2)))
  {
    // If link is a local path then get local path and handle resume & download managers
    $fsize=@filesize($link);
    /* is resume requested? */
    $headers = SecureLinkXGetAllHeaders();
    if(isset($headers["Range"]))
    {
      header("HTTP/1.1 206 Partial content");
      $val=split("=",$headers["Range"]);
      if(ereg("^-",$val[1]))
      {
        $slen = ereg_replace("-","",$val[1]);
        $sfrom = $fsize - $slen;
        header("Content-Length: ".$slen);
      }
      else if(ereg("-$",$val[1]))
      {
        $sfrom = ereg_replace("-","",$val[1]);
        $slen = $fsize - $sfrom;
        header("Content-Length: ".(string)((int)$fsize-(int)$sfrom));
      }
      else if(is_integer(strpos($val[1],"-")))
      {
        $ranges=split("-",$val[1]);
        $sfrom = $ranges[0];
        $slen = $ranges[1]-$ranges[0];
        header("Content-Length: ".(string)((int)$fsize-(int)$sfrom));
      }
      $br = $sfrom."-".(string)($fsize-1)."/".$fsize;
      header("Content-Range: bytes $br");
      $mimetype=SecureLinkGetMIMEType($link);
      if ($mimetype!="")
        header("Content-type: $mimetype\n");
      else
        header("Content-type: application/octet-stream\n");
      header("Connection: close");
      if (!($fh=@fopen($link,"rb")))
      {
        SecureLinkShowMessage($ErrorTemplate,"Sorry this file could not be opened.");
        exit;
      }
      if ($NotifyDownloadEmail != "")
      {
        if ($sfrom == 0)
          SecureLinkEmailDownloadNotify($actualfname, $id, $_SERVER['REMOTE_ADDR'],$expirytime);
      }
      if ($SecureLinkDownloadLog!="")
      {
        if ($sfrom == 0)
          SecureLinkLogDownload($SecureLinkDownloadLog,$actualfname,$id);
			}
      fseek($fh, $sfrom);
      @SecureLinkXFPassThru($fh);
    }
    else
    {
      $size=@filesize($link);
      if (!($fh=@fopen($link,"rb")))
      {
        SecureLinkShowMessage($ErrorTemplate,"Sorry this file could not be opened.");
        exit;
      }
      header("Pragma: cache\n");
      header("Cache-Control: private\n");
      header("Expires: 0\n");

      $mimetype=SecureLinkGetMIMEType($link);
      if ($dialog==1)
        header("Content-Disposition: attachment; filename=\"".basename($actualfname)."\"\n");
      if ($mimetype!="")
        header("Content-type: $mimetype\n");
      else
        header("Content-type: application/octet-stream\n");
      header("Content-transfer-encoding: binary\n");
      header("Content-Length: ".$size."\n");
      if ($NotifyDownloadEmail != "")
        SecureLinkEmailDownloadNotify($actualfname, $id, $_SERVER['REMOTE_ADDR'],$expirytime);
      if ($SecureLinkDownloadLog!="")
        SecureLinkLogDownload($SecureLinkDownloadLog,$actualfname,$id);
      @SecureLinkXFPassThru($fh);
    }
  }
  else
  {
    // link is a URL rather than local path so do simple download
	  $link=str_replace(" ","%20",$link);
    if (is_integer($pos))
		  $size=SecureLinkFileSizeRemote($link);
    else
		  $size=@filesize($link);
    if (!($fh=@fopen($link,"rb")))
    {
      SecureLinkShowMessage($ErrorTemplate,"Sorry this file could not be opened.");
      exit;
    }
    if ($NotifyDownloadEmail != "")
      SecureLinkEmailDownloadNotify($actualfname, $id, $_SERVER['REMOTE_ADDR'],$expirytime);
    if ($SecureLinkDownloadLog!="")
      SecureLinkLogDownload($SecureLinkDownloadLog,$actualfname,$id);
    $mimetype=SecureLinkGetMIMEType($link);
    if ($dialog==1)
      header("Content-disposition: attachment; filename=\"".basename($actualfname)."\"\n");
    if ($mimetype!="")
      header("Content-type: $mimetype\n");
    else
      header("Content-type: application/octet-stream\n");
    header("Content-transfer-encoding: binary\n");
    if ((int)$size>0)
      header("Content-Length: ".$size."\n");
    @SecureLinkXFPassThru($fh);
  }
  exit;
}

function SecureLinkEmailLinks($freem)
{
	global $SecureLinkKey,$EmailTemplate,$YourCompany,$YourEmail,$SecureLinkLog,$NoExtraPath,$ManualPassword;
	global $SecureLinkLocation,$SecureLinkLocations,$ErrorTemplate,$ipaddr,$Emails;
	global $NotifyEmail,$NotifyTemplate,$FormExpire;
	global $AllowEmailOnce, $AllowIPOnce, $RequireTuring, $turing, $manualentryused;
	$tohash=$SecureLinkKey;
	// Get list of allowed files
	for ($k=0;$k<100;$k++)
	{
	  $fvar="f".$k;
	  $lvar="l".$k;
	  if ((isset($_REQUEST[$fvar])) && ($_REQUEST[$fvar]!=""))
	  {
	    $fname[]=$_REQUEST[$fvar];
	    $tohash.=$fname[$k];
	    $floc[]=$_REQUEST[$lvar];
	    $tohash.=$floc[$k];
	  }
	}
	// See if only certain files were selected
	$selectedfiles=false;
	for ($k=0;$k<100;$k++)
	{
	  $fvar="file".$k;
	  if (isset($_REQUEST[$fvar]))
	  {
	    $locs=explode(":",$_REQUEST[$fvar]);
	    // Check if filename is in approved list
	    $index=array_search($locs[0],$fname);
	    if (is_integer($index))
	    {
	      if ($floc[$index]==$locs[1])
	      {
	        $selectedfiles=true;
	        $fnametosend[]=$locs[0];
	        $floctosend[]=$locs[1];
	      }
	    }
	  }
	}
	if (!$selectedfiles)
	{
	  for ($k=0;$k<count($fname);$k++)
	  {
	     $fnametosend[]=$fname[$k];
	     $floctosend[]=$floc[$k];
	  }
	}

	if (isset($_REQUEST['x']))
	{
	  $expiry=$_REQUEST['x'];
	  $tohash.=$expiry;
	}
	if (isset($_REQUEST['m']))
	{
	  $filter=$_REQUEST['m'];
	  $tohash.=$filter;
	}
	if (isset($_REQUEST['l']))
	{
	  $iplevel=$_REQUEST['l'];
	  $tohash.=$iplevel;
	}
	if (isset($_REQUEST['t']))
	{
	  $template=$_REQUEST['t'];
	  $tohash.=$template;
	}
	if (isset($_REQUEST['c']))
	{
	  $created=$_REQUEST['c'];
	  $tohash.=$created;
	}
	if (isset($_REQUEST['email']))
	  $clientemail=$_REQUEST['email'];
	if (isset($_REQUEST['turing']))
	  $turing=$_REQUEST['turing'];
	$goto="";
	if (isset($_REQUEST['g']))
	  $goto=$_REQUEST['g'];
	if (isset($_REQUEST['a']))
	  $auth=$_REQUEST['a'];
	// See if called from the manual entry form
	$manualentryused=false;
	if (($auth==$ManualPassword) && ($ManualPassword!=""))
	{
	  $manualentryused=true;
	}
	// Check email address entered is valid
	if (!SecureLinkValidEmail($clientemail))
	{
	    SecureLinkShowMessage($ErrorTemplate,"Please enter a valid email address.<BR><BR>Click your browsers BACK button and try again.");
	    exit;
	}
	// if filter set to 1 then block free email services listed above
	if ($filter=="1")
	{
	  for ($k=0; $k<count($freem);$k++)
	  {
	    $pos=strpos(strtolower($clientemail),strtolower($freem[$k]));
	    if (is_integer($pos))
	    {
	      SecureLinkShowMessage($ErrorTemplate,"Sorry free email addresses are not supported.<BR><BR>Click your browsers BACK button and try again.");
	      exit;
	    }
	  }
	}
	// If required check turing code (unless called from the manual entry form
	if (($RequireTuring==1) && ($manualentryused==false))
	{
	  session_start();
	  $turingmatch=false;
	  if ((strtolower($_SESSION['ses_llurlturingcode'])==strtolower(trim($turing))) && ($_SESSION['ses_llurlturingcode']!=""))
	  {
	    $turingmatch=true;
	    $_SESSION['ses_llurlturingcode']="";
	  }
	  else if ((strtolower($_SESSION['ses_llurlpreviousturingcode'])==strtolower(trim($turing))) && ($_SESSION['ses_llurlpreviousturingcode']!=""))
	  {
	    $turingmatch=true;
	    $_SESSION['ses_llurlpreviousturingcode']="";
	  }
	  if (!$turingmatch)
	  {
	    SecureLinkShowMessage($ErrorTemplate,"The turing (CAPTCHA) code entered was not correct.<BR><BR>Click your browsers BACK button and try again.");
	    exit;
	  }
	}
	// See if template specified in function call to overide default
	if ($template!="")
	  $template=$Emails.$template;
	if (($template=="") && ($EmailTemplate!=""))
	  $template=$EmailTemplate;
	// if required block if email address is already listed in log file within the days specified
	if  (($SecureLinkLog!="") && ((isset($AllowEmailOnce)) || (isset($AllowIPOnce))))
	{
		$limitemailtimestamp=mktime()-($AllowEmailOnce*24*60*60);
		$limitiptimestamp=mktime()-($AllowIPOnce*24*60*60);
		$testemail=strtolower($clientemail);
	  $fh=@fopen($SecureLinkLog,"r");
	  if ($fh)
	  {
		  while (!feof($fh))
		  {
		    $lne = fgets($fh, 2048);
		    $lne = strtolower($lne);
		    if (is_integer(strpos($lne,$testemail)))
		    {
		      $lnearray=explode(",",$lne);
		      $entrytimestamp=mktime(substr($lnearray[3],0,2),substr($lnearray[3],3,2),substr($lnearray[3],6,2),substr($lnearray[1],3,2),substr($lnearray[1],0,2),substr($lnearray[1],6,2));
		      if ((isset($AllowEmailOnce)) && ($entrytimestamp>$limitemailtimestamp))
		      {
	          SecureLinkShowMessage($ErrorTemplate,"This email address has already been used.");
	          exit;
					}
				}
		    if (is_integer(strpos($lne,$ipaddr)))
		    {
		      $lnearray=explode(",",$lne);
		      $entrytimestamp=mktime(substr($lnearray[3],0,2),substr($lnearray[3],3,2),substr($lnearray[3],6,2),substr($lnearray[1],3,2),substr($lnearray[1],0,2),substr($lnearray[1],6,2));
		      if ((isset($AllowIPOnce)) && ($entrytimestamp>$limitiptimestamp))
		      {
	          SecureLinkShowMessage($ErrorTemplate,"This IP address has already been used.");
	          exit;
					}
				}
		  }
		  fclose($fh);
		}
	}
	// See if Promotion  limits are being used
	global $PromotionExpiry, $PromotionLimit, $PromotionPeriod, $PromotionRedirect, $PromotionEmailTemplate, $PromotionFile;
	if (isset($PromotionLimit))
	{
	  // See if promotion period has expired. If so update email template and redirect page
	  if ($PromotionExpiry!="")
	  {
	    $promexpts=mktime(substr($PromotionExpiry,8,2),substr($PromotionExpiry,10,2),0,substr($PromotionExpiry,4,2),substr($PromotionExpiry,6,2),substr($PromotionExpiry,0,4));
	    if (time()>$promexpts)
	    {
	      $template=$PromotionEmailTemplate;
	      $goto=$PromotionRedirect;
	    }
	  }
	  // See how many entries in log for $PromotionFile for previous $PromotionPeriod days including today
	  if (($PromotionPeriod>0) && ($PromotionLimit>0) && ($PromotionFile!=""))
	  {
	    $earliestts=mktime(0,0,1,date("n"),date("j"),date("Y"))-(($PromotionPeriod-1)*86400);
	    $promcount=0;
	    $fh=@fopen($SecureLinkLog,"r");
	    if ($fh)
	    {
	  	  while (!feof($fh))
	  	  {
	  	    $lne = fgets($fh, 2048);
	  	    $lne = strtolower($lne);
	  	    if (is_integer(strpos($lne,$PromotionFile)))
	  	    {
	  	      $lnearray=explode(",",$lne);
	  	      $entrytimestamp=mktime(substr($lnearray[3],0,2),substr($lnearray[3],3,2),substr($lnearray[3],6,2),substr($lnearray[1],3,2),substr($lnearray[1],0,2),substr($lnearray[1],6,2));
	  	      if ($entrytimestamp>=$earliestts)
	  	        $promcount++;
	  	      if ($promcount==$PromotionLimit)
	  	        break;
	  	    }
	  	  }
	    }
	    if ($promcount>=$PromotionLimit)
	    {
	      $template=$PromotionEmailTemplate;
	      $goto=$PromotionRedirect;
	    }
	  }
	}
	// End of promotion limit check
	$hash=md5($tohash);
	// If manual order password then override hash check
	if ($manualentryused)
	{
	  $hash=$auth;
	  if (isset($_REQUEST['i']))
	    $ipaddr=$_REQUEST['i'];
	}
	if ($hash!=$auth)
	{
	  SecureLinkShowMessage($ErrorTemplate,"Form authentication failed!");
	  exit;
	}
	if ($FormExpire>0)
	{
	  if (time()>($created+60*$FormExpire))
	  {
	    SecureLinkShowMessage($ErrorTemplate,"Form has been expired!");
	    exit;
	  }
	}
	// Now send email with download links to client
	if ($template=="")
	{
	  $res=SecureLinkSendEmail($clientemail, $clientemail, $expiry, $ipaddr, $iplevel, $fnametosend, $floctosend);
	  if ($res!=true)
	  {
		  SecureLinkShowMessage($ErrorTemplate,"Email could not be sent!");
		  exit;
	  }
	}
	else
	{
	  $res=SecureLinkSendEmailUsingTemplate($clientemail, $template, $clientemail, $expiry, $ipaddr, $iplevel, $fnametosend, $floctosend);
	  if ($res!=true)
	  {
		  SecureLinkShowMessage($ErrorTemplate,"Email could not be sent");
		  exit;
	  }
	}
	// If manual form entry then don't send email to site or log entry.
	if ($manualentryused==True)
	  return;
	// Send email to site with form details
	if ($NotifyEmail=="")
	  $NotifyEmail=$YourEmail;
	if ($NotifyEmail!="")
	{
	  if ($NotifyTemplate=="")
	  {
	    $subject = "Website download request";
	    $mailBody="The following files were requested.\n\n";
	    for($k=0;$k<count($fnametosend);$k++)
	    {
	      $mailBody.=$fnametosend[$k];
	      if ($floctosend[$k]!="")
	        $mailBody.=" from ".$floctosend[$k];
	      $mailBody.="\n";
	    }
	    $mailBody.="\nData from other form fields:-\n\n";
	    if (!empty($_GET))
	    {
	      reset($_GET);
	      while(list($namepair, $valuepair) = each($_GET))
	      {
	        $$namepair = $valuepair;
	        if ((strlen($namepair)>2) && ($namepair!="securelinkform") && ($namepair!="turing"))
	          $mailBody.=$namepair." : ".$valuepair."\n";
	      }
	    }
	    if (!empty($_POST))
	    {
	      reset($_POST);
	      while(list($namepair, $valuepair) = each($_POST))
	      {
	        $$namepair = $valuepair;
	        if ((strlen($namepair)>2) && ($namepair!="securelinkform") && ($namepair!="turing"))
	          $mailBody.=$namepair." : ".$valuepair."\n";
	      }
	    }
	    $mailBody.="IP : ".$ipaddr."\n";
	    $mailBody.="\n";
	 	  SecureLinkSendEmailOut($NotifyEmail,$NotifyEmail,$YourCompany,$subject,$mailBody,"N");
	  }
	  else
	  {
	    $res=SecureLinkSendEmailUsingTemplate($NotifyEmail, $NotifyTemplate, $clientemail, $expiry, $ipaddr, $iplevel, $fnametosend, $floctosend);
		  if ($res!=true)
		  {
		    SecureLinkShowMessage($ErrorTemplate,"Email could not be sent");
		    exit;
		  }
	  }
	}
	// if Logfile required add a line to it containing details of this request
	if ($SecureLinkLog!="")
	{
	  if (is_writeable($SecureLinkLog))
	  {
	    $fh=@fopen($SecureLinkLog,"a");
	    if ($fh)
	    {
	      $logstr="Date,".date("d/m/y").",Time,".date("H:i:s").",IP,".$ipaddr; // Date,time,IP
	      // Add filenames File0,test.zip,File1,demo.pdf etc
	      for($k=0;$k<count($fnametosend);$k++)
	      {
	        $logstr.=",File".$k.",".$fnametosend[$k];
	        if ($floctosend[$k]!="")
	          $logstr.=":".$floctosend[$k];
	      }
	      // Add other form fields Name,Adrian,Email,test@mysite.com etc
	      if (!empty($_GET))
	      {
	        reset($_GET);
	        while(list($namepair, $valuepair) = each($_GET))
	        {
	          $$namepair = $valuepair;
	          if ((strlen($namepair)>2) && ($namepair!="securelinkform") && ($namepair!="turing"))
	            $logstr.=",".$namepair.",".$valuepair;
	        }
	      }
	      if (!empty($_POST))
	      {
	        reset($_POST);
	        while(list($namepair, $valuepair) = each($_POST))
	        {
	          $$namepair = $valuepair;
	          if ((strlen($namepair)>2) && ($namepair!="securelinkform") && ($namepair!="turing"))
	            $logstr.=",".$namepair.",".$valuepair;
	        }
	      }
	      $logstr.="\n";
	      fputs($fh,$logstr);
	      fclose($fh);
	    }
	  }
	}
	if ($goto!="")
	{
	  header("Location: ".$goto);
	}
}

function SecureLinkSendEmailUsingTemplate($toemail,$template,$clientemail, $expiry, $ipaddr, $iplevel, $fname,$floc)
{
  global $thisurl,$SecureLinkLocation,$SecureLinkLocations,$EmailHeaderNoSlashR,$SecureLinkKey;
  global $YourCompany,$YourEmail,$DownloadBackground,$CopyEmail;
  $usehtmlformat="Y";
  // See if template exists as a file. If not assume it is a buffer
  if (is_file($template))
  {
    $ext=SecureLinkFileExtension($template);
    if ($ext == ".php")
    {
      ob_start();
      include $template;
      $mailBody = ob_get_contents();
      ob_end_clean();
    }
    else
    {
      if (!($fh = @fopen($template, "r")))
        return(false);
      $mailBody = fread ($fh, 200000);
      fclose($fh);
    }
    if ($ext==".txt")
      $usehtmlformat="N";
  }
  else
  {
    $mailBody=$template;
    if ((!is_integer(strpos($mailBody,"<html"))) && (!is_integer(strpos($mailBody,"<HTML"))))
      $usehtmlformat="N";
  }
  if ($expiry != 0)
  {
    if (strlen($expiry) == 12)
      $expirytime = mktime(substr($expiry, 8, 2), substr($expiry, 10, 2), 0, substr($expiry, 4, 2), substr($expiry, 6, 2), substr($expiry, 0, 4), -1);
    else
      $expirytime = time() + ($expiry * 60);
  }
  else
    $expirytime = 0;
  // Create secure links and get size and filename for each file
  for ($k=0;$k<count($fname);$k++)
  {
    $ProdLink[$k]=GetSecureLink($fname[$k], $floc[$k], $expirytime, $clientemail, $ipaddr, $iplevel, $SecureLinkKey, "1", $thisurl);
    $ProdFile[$k]=SecureLinkFileName($fname[$k]);
    if ($floc[$k]=="")
      $reallocation=$SecureLinkLocation.$fname[$k];
    else
      $reallocation=$SecureLinkLocations[$floc[$k]].$fname[$k];
    // See if link is local path or URL
    if (substr(trim(strtolower($reallocation)),0,3)=="s3|")
    {
      $size = SecureLinkFileSizeS3($reallocation);
    }
    else
    {
      $pos=strpos(strtolower($reallocation),"http://");
      if (!is_integer($pos))
        $fsize=@filesize($reallocation);
      else
  		  $fsize=SecureLinkFileSizeRemote($reallocation);
		}
    $ProdSize[$k]=$fsize;
  }
  $max=30;
  if (count($fname)>$max)
    $max=count($fname);
  // First deal with any !!!link_n!!! that is part of hyperlink
  $start=0;
  do
  {
    $pos=strpos($mailBody,"<a",$start);
    if (!is_integer($pos))
      $pos=strpos($mailBody,"<A",$start);
    $pos2=strpos($mailBody,"</a>",$pos);
    if (!is_integer($pos2))
      $pos2=strpos($mailBody,"</A>",$pos);
    $found=0;
    if ((is_integer($pos)) && (is_integer($pos2)))
    {
      $found=1;
      for ($k=1; $k<=$max; $k++)
      {
        if ($ProdLink[$k-1]=="")
        {
          // See if !!!link_k!!! is within the hyperlink
          $hl=substr($mailBody,$pos,$pos2-$pos);
          $pos3=strpos($hl,"!!!link_".$k."!!!");
          if (!is_integer($pos3))
            $pos3=strpos($hl,"!!!link_".$k."!!!");
          if (is_integer($pos3))
          {
            $start=$pos;
            $mailBody=substr_replace($mailBody,"",$pos,$pos2-$pos);
            break;
          }
          else
            $start=$pos2;
        }
        else
          $start=$pos2;
      }
    }
  }
  while($found==1);
  // Now replace all other variables
  for ($k=1; $k<=$max; $k++)
  {
    if ($ProdLink[$k-1]!="")
    {
      $mailBody=eregi_replace("!!!filename_".$k."!!!",$ProdFile[$k-1],$mailBody);
      $mailBody=eregi_replace("!!!link_".$k."!!!",$ProdLink[$k-1],$mailBody);
      $mailBody=eregi_replace("!!!size_".$k."!!!",SecureLinkFriendlyFileSize($ProdSize[$k-1]),$mailBody);
      $mailBody=eregi_replace("!!!expires_".$k."!!!",SecureLinkFriendlyExpiryTime($expiry),$mailBody);
      if($usehtmlformat=="Y")
      {
        $tot="To download ".$ProdFile[$k-1]." click the link below:<BR>";
        $tot.="<a href=\"".$ProdLink[$k-1]."\">".$ProdFile[$k-1]."</a>";
        if ($ProdSize[$k-1]!="0")
          $tot.=" (".SecureLinkFriendlyFileSize($ProdSize[$k-1]).")";
        if ($expiry!="0")
          $tot.=" ~ Download link will expire in ".SecureLinkFriendlyExpiryTime($expiry);
      }
      else
      {
        $tot="To download ".$ProdFile[$k-1]." click the link below:\n".$ProdLink[$k-1]."\n";
        $tot.=$ProdFile[$k-1];
        if ($ProdSize[$k-1]!="0")
          $tot.=" (".SecureLinkFriendlyFileSize($ProdSize[$k-1]).")";
        if ($expiry!="0")
          $tot.=" ~ Download link will expire in ".SecureLinkFriendlyExpiryTime($expiry);
      }
      $mailBody=eregi_replace("!!!download_".$k."!!!",$tot,$mailBody);
    }
    else
    {
      $mailBody=eregi_replace("!!!filename_".$k."!!!","",$mailBody);
      $mailBody=eregi_replace("!!!size_".$k."!!!","",$mailBody);
      $mailBody=eregi_replace("!!!expires_".$k."!!!","",$mailBody);
      $mailBody=eregi_replace("!!!download_".$k."!!!","",$mailBody);
      $mailBody=eregi_replace("!!!link_".$k."!!!","",$mailBody);
    }
  }
  // Replace form variables
  if (!empty($_GET))
  {
    reset($_GET);
    while(list($namepair, $valuepair) = each($_GET))
    {
      $$namepair = $valuepair;
      if ((strlen($namepair)>2) && ($namepair!="securelinkform") && ($namepair!="turing"))
        $mailBody=eregi_replace("!!!".$namepair."!!!",$valuepair,$mailBody);
    }
  }
  if (!empty($_POST))
  {
    reset($_POST);
    while(list($namepair, $valuepair) = each($_POST))
    {
      $$namepair = $valuepair;
      if ((strlen($namepair)>2) && ($namepair!="securelinkform"))
        $mailBody=eregi_replace("!!!".$namepair."!!!",$valuepair,$mailBody);
    }
  }
  $mailBody=eregi_replace("!!!ip!!!",$ipaddr,$mailBody);
  // Now we should see if !!!eachfilestart!!! sections exists
  $start=0;
  do
  {
    $found=0;
    $pos=strpos($mailBody,"<!--eachfilestart-->");
    $pos2=strpos($mailBody,"<!--eachfileend-->");
    if ((is_integer($pos)) && (is_integer($pos2)))
    {
      $found=1;
      $buf=substr($mailBody,$pos+20,$pos2-$pos-20);
      // Now remove this section
      $mailBody1=substr($mailBody,0,$pos);
      $mailBody2=substr($mailBody,$pos2+18,strlen($mailBody)-$pos2-18);
      $mailBody=$mailBody1;
      for ($k=1; $k<=count($ProdLink); $k++)
      {
        $repeatbuf=$buf;
        $repeatbuf=eregi_replace("!!!filename!!!",$ProdFile[$k-1],$repeatbuf);
        $repeatbuf=eregi_replace("!!!link!!!",$ProdLink[$k-1],$repeatbuf);
        $repeatbuf=eregi_replace("!!!size!!!",SecureLinkFriendlyFileSize($ProdSize[$k-1]),$repeatbuf);
        $repeatbuf=eregi_replace("!!!expires!!!",SecureLinkFriendlyExpiryTime($expiry),$repeatbuf);
        if($usehtmlformat=="Y")
        {
          $tot="To download ".$ProdFile[$k-1]." click the link below:<BR>";
          $tot.="<a href=\"".$ProdLink[$k-1]."\">".$ProdFile[$k-1]."</a>";
          if ($ProdSize[$k-1]!="0")
            $tot.=" (".SecureLinkFriendlyFileSize($ProdSize[$k-1]).")";
          if ($expiry!="0")
            $tot.=" ~ Download link will expire in ".SecureLinkFriendlyExpiryTime($expiry);
        }
        else
        {
          $tot="To download ".$ProdFile[$k-1]." click the link below:\n".$ProdLink[$k-1]."\n";
          $tot.=$ProdFile[$k-1];
          if ($ProdSize[$k-1]!="0")
            $tot.=" (".SecureLinkFriendlyFileSize($ProdSize[$k-1]).")";
          if ($expiry!="0")
            $tot.=" ~ Download link will expire in ".SecureLinkFriendlyExpiryTime($expiry);
        }
        $repeatbuf=eregi_replace("!!!download!!!",$tot,$repeatbuf);
        $mailBody.=$repeatbuf;
      }
      $mailBody.=$mailBody2;
    }
  }
  while($found==1);
  // Now handle any !!!link(filename,expiry)!!! template variables
  $itemids=SecureLinkGetItemVars($mailBody,"link");
  $items=explode("|",$itemids);
  for ($k=0;$k<count($items);$k++)
  {
    // Split item into filename and expiry time.
    $filename=strtok($items[$k],",");
    $exp=strtok(",");
    // Now split filename and file location if used
    $filename=strtok($filename,":");
    $flocation=strtok(":");
    if ($exp != 0)
    {
      if (strlen($exp)==12)
        $expiry=mktime(substr($exp,8,2),substr($exp,10,2),0,substr($exp,4,2),substr($exp,6,2),substr($exp,0,4),-1);
      else
        $expiry = time() + ($exp * 60);
    }
    $plink=GetSecureLink($filename, $flocation, $expiry, $clientemail, $ipaddr, $iplevel, $SecureLinkKey, "1", $thisurl);
    $mailBody = str_replace("!!!link(".$items[$k].")!!!",$plink, $mailBody);
  }
  // Now handle any !!!size(filename)!!! template variables
  $itemids=SecureLinkGetItemVars($mailBody,"size");
  $items=explode("|",$itemids);
  for ($k=0;$k<count($items);$k++)
  {
    $filename=strtok($items[$k],":");
    $flocation=strtok(":");
    if ($flocation=="")
      $fullpath=$SecureLinkLocation.$filename;
    else
      $fullpath=$SecureLinkLocations[$flocation].$filename;
    if (substr(trim(strtolower($fullpath)),0,3)=="s3|")
    {
      $s = SecureLinkFileSizeS3($fullpath);
      if (is_numeric($s))
        $size = $s;
    }
    else
    {
      $pos = strpos(strtolower($fullpath), "http://");
      if (is_integer($pos))
      {
        $s=SecureLinkFileSizeRemote($fullpath);
        if (is_integer($s))
        	$size=$s;
      }
      else
        $size = @filesize($fullpath);
    }
    $mailBody = str_replace("!!!size(".$items[$k].")!!!",SecureLinkFriendlyFileSize($size), $mailBody);
  }
  // Get subject for email
  if ($usehtmlformat!="Y")
  {
    $pos=strpos($mailBody,"\n");
    $subject=substr($mailBody,0,$pos);
    $mailBody=substr($mailBody,$pos+1,strlen($mailBody)-$pos-1);
  }
  else
  {
    $subject="Download Links";
    $pos=strpos($mailBody,"<TITLE>");
    if (!is_integer($pos))
      $pos=strpos($mailBody,"<title>");
    $pos2=strpos($mailBody,"</TITLE>");
    if (!is_integer($pos2))
      $pos2=strpos($mailBody,"</title>");
    if ((is_integer($pos)) &&  (is_integer($pos2)))
    {
      $subject=substr($mailBody,$pos+7,$pos2-$pos-7);
    }
  }
  // If using a download background page replace auth with authe
  if ($DownloadBackground!="")
    $mailBody = str_replace("?securelinkauth=", "?securelinkauthe=", $mailBody);
  if($usehtmlformat=="Y")
    $mailBody="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD W3 HTML//EN\">\n".$mailBody;
  $result=SecureLinkSendEmailOut($toemail,$YourEmail,$YourCompany,$subject,$mailBody,$usehtmlformat);
  if ($CopyEmail!="")
    SecureLinkSendEmailOut($CopyEmail,$YourEmail,$YourCompany,$subject,$mailBody,$usehtmlformat);
  return($result);
}

function SecureLinkGetItemVars($buf,$n)
{
	$start = 0;
	$itemids="";
	do
	{
	  $pos = strpos($buf, "!!!".$n."(", $start);
	  $found = 0;
	  if (is_integer($pos))
	  {
	    $found = 1;
	    $pos2=strpos($buf, ")!!!", $pos);
	    if (is_integer($pos2))
	    {
	      if ($itemids!="")
	        $itemids.="|";
	      $itemids.=substr($buf,$pos+strlen($n)+4,$pos2-($pos+strlen($n)+4));
	    }
	    $start=$pos2;
	  }
	}
	while ($found==1);
	return($itemids);
}

function SecureLinkSendEmail($toemail,$clientemail, $expiry, $ipaddr, $iplevel, $fname,$floc)
{
  global $thisurl,$SecureLinkLocation,$SecureLinkLocations,$EmailHeaderNoSlashR,$SecureLinkKey,$HTMLEmail;
  global $YourCompany,$YourEmail,$DownloadBackground,$CopyEmail;
  if ($expiry != 0)
  {
    if (strlen($expiry) == 12)
      $expirytime = mktime(substr($expiry, 8, 2), substr($expiry, 10, 2), 0, substr($expiry, 4, 2), substr($expiry, 6, 2), substr($expiry, 0, 4), -1);
    else
      $expirytime = time() + ($expiry * 60);
  }
  else
    $expirytime = 0;
  $subject = "Download Links from ".$YourCompany;
  $mailBody="Please use the link(s) below to begin downloading.\n\n";
  for ($k=0;$k<count($fname);$k++)
  {
    $plink=GetSecureLink($fname[$k], $floc[$k], $expirytime, $clientemail, $ipaddr, $iplevel, $SecureLinkKey, "1", $thisurl);
    $fnameonly=SecureLinkFileName($fname[$k]);
    if ($floc[$k]=="")
      $reallocation=$SecureLinkLocation.$fname[$k];
    else
      $reallocation=$SecureLinkLocations[$floc[$k]].$fname[$k];
    // See if link is local path or URL
    if (substr(trim(strtolower($reallocation)),0,3)=="s3|")
    {
      $size = SecureLinkFileSizeS3($reallocation);
    }
    else
    {
      $pos=strpos(strtolower($reallocation),"http://");
      $pos2=strpos(strtolower($reallocation),"ftp://");
      if (!is_integer($pos))
        $fsize=@filesize($reallocation);
      else
      		$fsize=SecureLinkFileSizeRemote($reallocation);
    }
    if($HTMLEmail == "Y")
    {
      $mailBody.="To download ".$fnameonly." click the link below:\n";
      $mailBody.="<a href=\"".$plink."\">".$fnameonly."</a>";
      if ($fsize!=0)
        $mailBody.=" (".SecureLinkFriendlyFileSize($fsize).")";
      if ($expiry!="0")
        $mailBody.=" ~ Download link will expire in ".SecureLinkFriendlyExpiryTime($expiry).".\n";
      else
        $mailBody.=" \n";
      $mailBody.=" \n";
    }
    else
    {
      $mailBody.="To download ".$fnameonly." click the link below:\n".$plink."\n";
      $mailBody.=$fnameonly;
      if ($fsize!=0)
        $mailBody.=" (".SecureLinkFriendlyFileSize($fsize).")";
      if ($expiry!="0")
        $mailBody.=" ~ Download link will expire in ".SecureLinkFriendlyExpiryTime($expiry).".\n";
      else
        $mailBody.=" \n";
      $mailBody.=" \n";
    }
  }
  $mailBody.="\n";
  $mailBody.=$YourCompany."\n";
  $mailBody.=$YourEmail."\n";
  // If using a download background page replace auth with authe
  if ($DownloadBackground!="")
    $mailBody = str_replace("?securelinkauth=", "?securelinkauthe=", $mailBody);
  if($HTMLEmail == "Y")
  {
    $mailBody = eregi_replace("\n","<br>\n",$mailBody);
    $mailBody="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD W3 HTML//EN\">\n<HTML>\n<HEAD>\n<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-1\">\n<TITLE>Download Information</TITLE>\n</HEAD\n<BODY>\n".$mailBody;
    $mailBody.="</BODY>\n</HTML>\n";
  }
  $result=SecureLinkSendEmailOut($toemail,$YourEmail,$YourCompany,$subject,$mailBody,$HTMLEmail);
  if ($CopyEmail!="")
    SecureLinkSendEmailOut($CopyEmail,$YourEmail,$YourCompany,$subject,$mailBody,$HTMLEmail);
  return($result);
}

function SecureLinkGetMIMEType($fn)
{
	global $mt;
  $ext=SecureLinkFileExtension($fn);
  if (isset($mt[$ext]))
    $mimetype=$mt[$ext];
  else
    $mimetype="";
  return("$mimetype");
}

function SecureLinkFriendlyExpiryTime($exp)
{
  if ($exp==0)
    return("");
  if (($exp>=1) && ($exp<=59))
  {
    if ($exp==1)
      return("$exp minute");
    else
      return("$exp minutes");
  }
  if (($exp>=60) && ($exp<=1440))
  {
    $hours=intval($exp/60);
    $mins=$exp % 60;
    if ($hours==1)
      $ret=$hours." hour";
    else
      $ret=$hours." hours";
    if ($mins>0)
    {
      if ($mins==1)
        $ret.=" & ".$mins." minute";
      else
        $ret.=" & ".$mins." minutes";
    }
    return($ret);
  }
  if ($exp>=1441)
  {
    $days=intval($exp/1440);
    $exp=$exp-($days*1440);
    $hours=intval($exp/60);
    $mins=$exp % 60;
    if ($days==1)
      $ret=$days." day";
    else
      $ret=$days." days";
    if ($hours>0)
    {
      if ($mins==0)
        $ret.=" &";
      if ($hours==1)
        $ret.=" ".$hours." hour";
      else
        $ret.=" ".$hours." hours";
    }
    if ($mins>0)
    {
      if ($mins==1)
        $ret.=" & ".$mins." minute";
      else
        $ret.=" & ".$mins." minutes";
    }
    return($ret);
  }
}

function SecureLinkFriendlyFileSize($sz)
{
  if ($sz<=1023)
    return($sz." Bytes");
  if (($sz>=1024) && ($sz<=1048575))
  {
    $sz=intval($sz/1024);
    return($sz." KB");
  }
  if ($sz>=1048576)
  {
    $sz=$sz/1048576;
    $sz=intval($sz*100)/100;
    return($sz." MB");
  }
}

function SecureLinkFileExtension($fname)
{
  if ($fname == "")
    return("");
  $pos = strrpos($fname, ".");
  if (is_integer($pos))
    return(strtolower(substr($fname, $pos)));
  return("");
}

function SecureLinkFileName($fname)
{
  if ($fname == "")
    return("");
  // First see if link is for S3
  if (substr(trim(strtolower($fname)),0,3)=="s3|")
  {
    $pos=strrpos($fname, "|");
    if (is_integer($pos))
      $fname = substr($fname, $pos + 1);
  }
  $pos1 = strrpos($fname, "/");
  $pos2 = strrpos($fname, "\\");
  if ($pos1 === false)
    $pos1 = -1;
  if ($pos2 === false)
    $pos2 = -1;
  if ($pos1 > $pos2)
    $pos = $pos1;
  else
    $pos = $pos2;
  if ($pos > -1)
  {
    $name = substr($fname, $pos + 1);
    $fname=$name;
  }
  // See if actual filename part
  $pos=strpos($fname,"^");
  if (is_integer($pos))
    $fname=substr($fname,0,$pos);
  // See if query part
  $pos=strpos($fname,"?");
  if (is_integer($pos))
    $fname=substr($fname,0,$pos);
  return($fname);
}

function SecureLinkFileNamepath($fname)
{
  if ($fname == "")
    return("");
  $pos1 = strrpos($fname, "/");
  $pos2 = strrpos($fname, "\\");
  if ($pos1 === false)
    $pos1 = -1;
  if ($pos2 === false)
    $pos2 = -1;
  if ($pos1 > $pos2)
    $pos = $pos1;
  else
    $pos = $pos2;
  // See if actual filename part
  $pos=strpos($fname,"^");
  if (is_integer($pos))
    $fname=substr($fname,0,$pos);
  // See if query part
  $pos=strpos($fname,"?");
  if (is_integer($pos))
    $fname=substr($fname,0,$pos);
  return($fname);
}

function SecureLinkFileQuery($fname)
{
  $pos=strpos($fname,"?");
  if (is_integer($pos))
    $fname=substr($fname,$pos+1);
  else
    return("");
  // See if actual filename part
  $pos=strpos($fname,"^");
  if (is_integer($pos))
    $fname=substr($fname,0,$pos);
  // See if query part
  $pos=strpos($fname,"?");
  if (is_integer($pos))
    $fname=substr($fname,0,$pos);
  return($fname);
}

function SecureLinkAltFileName($fname)
{
  $actualfname=$fname;
  $pos=strpos($fname,"^");
  if (is_integer($pos))
    $actualfname=substr($fname,$pos+1);
  return($actualfname);
}

function SecureLinkCustomMessage($Template,$msg)
{
  if ($Template == "")
    return(0);
  $ext = SecureLinkFileExtension($Template);
  if ($ext == ".php")
  {
    /*ob_start();
    include $Template;
    $page = ob_get_contents();
    ob_end_clean();
		*/
  	$_SESSION['SecureLinkErrorMsg'] = $msg;
  	exit("<script>window.location.href='$Template';</script>");
  }
  else
  {
    if (!($fh = @fopen($Template, "r")))
      return(0);
    $page = fread ($fh, 200000);
    fclose($fh);
  }
  $page = eregi_replace("!!!message!!!", $msg, $page);
  print $page;
  return(1);
}

function SecureLinkShowMessage($Template,$msg)
{
  if (0==SecureLinkCustomMessage($Template,$msg))
  {
    print ("<HTML>\n");
    print ("<HEAD>\n");
    print ("<TITLE>Warning</TITLE>\n");
    print ("</HEAD>\n");
    print ("<BODY>\n");
    print("$msg<BR>");
    print ("</BODY>\n");
    print ("</HTML>\n");
  }
}

function SecureLinkValidEmail($email)
{
	// Create the syntactical validation regular expression
	$regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
	// Presume that the email is invalid
	$valid =false;
	// Validate the syntax
	if (eregi($regexp, $email))
	  $valid=true;
	return $valid;
}

function SecureLinkXGetAllHeaders()
{
 global $_SERVER;
 $headers = array();
  while (list($key, $value) = each ($_SERVER))
  {
    if (strncmp($key, "HTTP_", 5) == 0)
    {
      $key = strtr(ucwords(strtolower(strtr(substr($key, 5), "_", " "))), " ", "-");
      $headers[$key] = $value;
    }
  }
  return $headers;
}

function SecureLinkSendEmailOut($toemail, $fromemail, $fromname, $subject, $mailBody, $htmlformat)
{
  global $EmailHeaderNoSlashR, $ExtraMailParam, $ErrorTemplate, $ErrorEmail, $UsePHPmailer;
  // Remove any comma in from name
  $fromname = str_replace(",", " ", $fromname);
  // Handle multiple email addresses
  $sendtoemail=explode(",",$toemail);
  // If phpmailer setup then use it otherwise handle with PHP mail() function
  if ($UsePHPmailer == 1)
  {
    global $EmailUsername, $EmailPassword, $EmailServer, $EmailPort;
    if ($EmailPort=="")
      $EmailPort=25;
    require_once("class.phpmailer.php");
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Host = $EmailServer;
		$mail->Port = $EmailPort;
    $mail->SMTPAuth = true; // This line is important for smtp authentication
    $mail->Username = $EmailUsername;
    $mail->Password = $EmailPassword;
    $mail->From = $fromemail;
    $mail->FromName = $fromname;
    for ($k=0; $k<count($sendtoemail); $k++)
      $mail->AddAddress($sendtoemail[$k]);
    if ($htmlformat == "Y")
      $mail->IsHTML(true);
    else
      $mail->IsHTML(false);
    $mail->Subject = $subject;
    $mail->Body = $mailBody;
    $mail->Send();
    if ($mail->isError())
      return(false);
    return(true);
  }
  else
  {
	  $headers = "From: " . $fromname . " <" . $fromemail . ">\r\n";
	  $headers.= "Reply-To: " . $fromname . " <" . $fromemail . ">\r\n";
	  $headers.= "MIME-Version: 1.0\r\n";
	  if ($htmlformat=="Y")
	  {
	    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	    $headers .= "Content-Transfer-Encoding: base64\r\n";
	    $mailBody=chunk_split(base64_encode($mailBody));
	  }
	  else
	    $headers .= "Content-type: text/plain\r\n";
	  if ($EmailHeaderNoSlashR == 1)
	    $headers = eregi_replace("\r", "", $headers);
    if ($EmailHeaderNoSlashR == 1)
      $headers = eregi_replace("\r", "", $headers);
    for ($k=0; $k<count($sendtoemail); $k++)
    {
      if ($ExtraMailParam != "")
        $sent = mail($sendtoemail[$k], $subject, $mailBody, $headers, $ExtraMailParam);
      else
        $sent = mail($sendtoemail[$k], $subject, $mailBody, $headers);
      if ($sent==false)
        return(false);
    }
    return(true);
  }
}

function SecureLinkXFPassThru($file)
{
 global $downloadbuffer;
 if ($downloadbuffer>0)
 {
   @set_time_limit(86400);
   while(!feof($file))
   {
      print(fread($file, $downloadbuffer));
      ob_flush();
      flush();
      sleep(1);
   }
   fclose($file);
 }
 else
   @fpassthru($file);
}

function SecureLinkFileSizeRemote($url, $timeout=2)
{
	$url = parse_url($url);
	if ($fp = @fsockopen($url['host'], ($url['port'] ? $url['port'] : 80), $errno, $errstr, $timeout))
	{
	  fwrite($fp, 'HEAD '.$url['path'].$url['query']." HTTP/1.0\r\nHost: ".$url['host']."\r\n\r\n");
	  @stream_set_timeout($fp, $timeout);
	  while (!feof($fp))
	  {
	    $size = fgets($fp, 4096);
	    if (stristr($size, 'Content-Length') !== false)
	    {
	      $size = trim(substr($size, 16));
	      break;
	    }
	  }
	  fclose ($fp);
	}
	return is_numeric($size) ? intval($size) : false;
}

function SecureLinkLogDownload($logfile,$fname,$email)
{
  global $ipaddr;
  if (is_writeable($logfile))
  {
    $fh=@fopen($logfile,"a");
    if ($fh)
    {
      $logstr=date("d/m/y").",".date("H:i:s").",".$fname.",".$email.",".$ipaddr; // date,time,filename,email,IP
      $logstr.="\n";
      fputs($fh,$logstr);
      fclose($fh);
    }
  }
}
function SecureLinkGetS3URL($location,$expires,$operation="GET")
{
  // Split into access key id, secret access key, bucket , filename
  $parts=explode("|",$location);
  $accesskeyid=trim($parts[1]);
  $secretaccesskey=trim($parts[2]);
  $bucket=trim($parts[3]);
  $filename=trim($parts[4]);
  // Cleanup filename
  $filename = rawurlencode($filename);
  $filename = str_replace('%2F', '/', $filename);
  // Make path to use
  $path = $bucket.'/'.$filename;
  // Make signature
  $strtosign =$operation ."\n"."\n"."\n".$expires ."\n"."/$path";
  $hash=SecureLinkHMACSHA1($secretaccesskey,$strtosign);
  $signature=SecureLinkHEX2B64($hash);
  $signature = urlencode($signature);
  $url = sprintf('http://%s.s3.amazonaws.com/%s?AWSAccessKeyId=%s&Expires=%u&Signature=%s',$bucket, $filename,$accesskeyid, $expires, $signature);
  return($url);
}

function SecureLinkFileSizeS3($location)
{
  global $ServerTimeAdjust;
  $url=SecureLinkGetS3URL($location,time()+$ServerTimeAdjust,"GET");
  $header=get_headers($url,1);
  if (is_numeric($header['Content-Length']))
    return ($header['Content-Length']);
  return ("Unknown");
}

function SecureLinkHMACSHA1($key,$data)
{
  $blocksize=64;
  $hashfunc='sha1';
  if (strlen($key)>$blocksize)
      $key=pack('H*', $hashfunc($key));
  $key=str_pad($key,$blocksize,chr(0x00));
  $ipad=str_repeat(chr(0x36),$blocksize);
  $opad=str_repeat(chr(0x5c),$blocksize);
  $hmac = pack(
              'H*',$hashfunc(
                  ($key^$opad).pack(
                      'H*',$hashfunc(
                          ($key^$ipad).$data
                      )
                  )
              )
          );
  return bin2hex($hmac);
}

function SecureLinkHEX2B64($str)
{
  $raw = '';
  for ($i=0; $i < strlen($str); $i+=2) {
          $raw .= chr(hexdec(substr($str, $i, 2)));
  }
  return base64_encode($raw);
}
function SecureLinkEmailDownloadNotify($fname, $id, $ipaddr, $expiry)
{
  global $NotifyDownloadEmail, $YourCompany, $YourEmail;
  $subject = "FinFisher URL Download of $fname";
  $mailBody = "Download notification.\n\n";
  $mailBody .= "Filename : " . $fname . "\n";
  $mailBody .= "ID : " . $id . "\n";
  $mailBody .= "IP : " . $ipaddr . "\n";
  $mailBody .= "Download time : " . date("d M Y H:i:s") . "\n";
  $mailBody .= "Expiry time : " . date("d M Y H:i:s", $expiry) . "\n";
  $mailBody .= "User agent : " . $_SERVER['HTTP_USER_AGENT'] . "\n";
  $mailBody .= "\n";
  SecureLinkSendEmailOut($NotifyDownloadEmail, $YourEmail, $YourCompany, $subject, $mailBody, "N");
  return;
}

?>
