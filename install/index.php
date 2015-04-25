<!--
@(#)File:           $RCSfile: index.php $
@(#)Last changed:   $Date: 2015/04/02 13:00:00 $
@(#)Purpose:        Setup Installation
@(#)Author:         Vincent Palcon
@(#)Copyright:      (C) Actino Inc. 2014-2015
@(#)Product:        Actino Software
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
-->
<?php
ini_set('max_execution_time', 300);
session_start();
function run_sql_file($location, $prefix, $data_language) {
    $commands = '';
    $handle = @fopen($location, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            $line = $buffer;
            if (strpos($line, '{PREFIX}') !== false)
                $line = str_replace('{PREFIX}', $prefix . '_', $buffer);
            $commands .= $line . "\n";
        }
        if (!feof($handle)) {
            echo "Error: Unexpected failure of fgets()\n";
        }
        fclose($handle);
    }
    //convert to array
    $commandsArray = explode(";", $commands);
    //run commands
    $item = $total = $success = 0;
    $handle_log = null;
    foreach ($commandsArray as $command) {
        if (trim($command)) {
            if(mysql_query($command)){
                $success++;
            }else{
              if(is_null($handle_log)){
                 $handle_log = fopen('log/failed.log', "w");
                 if (!$handle) {
                    throw new Exception("Could not open the file log!");
                 }else{
                    fwrite($handle_log, "--------------------------------------------------------------------------------------------------------------------------------------------\n");
                    fwrite($handle_log, "-- Failed.log - Date Time: ".date('Y-m-d h:m:s',time())." - Installation: ".$data_language[0]->name."\n");
                    fwrite($handle_log, "--------------------------------------------------------------------------------------------------------------------------------------------\n");
                    fwrite($handle_log, "\n");
                 }
              }  
              fwrite($handle_log, "------------------------------[BEGIN COMMAND]------------------------------------------\n");
              fwrite($handle_log, "-- Nº Command: ".$item." - Command SQL failed: ".$command.";\n");
              fwrite($handle_log, "-------------------------------[END COMMAND]-------------------------------------------\n\n\n");
            }
            $item++;
            //$success += (@mysql_query($command) == false ? 0 : 1);
            $total += 1;
        }
    }
    if(!is_null($handle_log))fclose($handle_log);
    $failed = $total - $success;
    ini_set('auto_detect_line_endings', FALSE);
    //return number of successful queries and total number of queries found
    return array(
        "success" => $success,
        "total" => $total,
        "failed" => $failed
    );
}

$languages = array();
$xml = simplexml_load_file("setting.xml");
 $_SESSION['language'] = (string)$xml->languages->default;
$n_languages = count($xml->languages->language);
for ($i = 0; $i <= $n_languages - 1; $i++) {
    $languages[] = (string)$xml->languages->language[$i]['id'];
}
if ((isset($_POST['input_language'])) && (in_array($_POST['input_language'], $languages))) {
    $_SESSION['language'] = $_POST['input_language'];
}
if(!isset($_SESSION['language']))$_SESSION['language'] = (string)$xml->languages->default;
$data_lang = include('languages'.DIRECTORY_SEPARATOR.$_SESSION['language'].'.php');
function translate($string){
    GLOBAL $data_lang;
    return $data_lang[$string];
}
$data_language = $xml->xpath('//language[@id="' . $_SESSION['language'] . '"]');

//check db connect
$error_connecting = true;
$run_sql = false;
$inputURL = '';
$inputHost = '';
$inputUsername = '';
$inputPassword = '';
$inputDatabase = '';
$inputPrefix = '';

if (isset($_POST['inputHost'])) {
    error_reporting(0);
    $cn = mysql_connect($_POST['inputHost'], $_POST['inputUsername'], $_POST['inputPassword']);
    if ($cn) {
        $dbcheck = mysql_select_db($_POST['inputDatabase']);
        if ($dbcheck) {
            $inputURL = $_POST['inputURL'];
            $inputHost = $_POST['inputHost'];
            $inputUsername = $_POST['inputUsername'];
            $inputPassword = $_POST['inputPassword'];
            $inputDatabase = $_POST['inputDatabase'];
            $inputPrefix = $_POST['inputPrefix'];
            $error_connecting = false;
        }
    }
} elseif (isset($_POST['InHost'])) {
    //error_reporting(0);
    $cn = mysql_connect($_POST['InHost'], $_POST['InUsername'], $_POST['InPassword']);
    if ($cn) {
        $dbcheck = mysql_select_db($_POST['InDatabase']);
        if ($dbcheck) {
            $result = run_sql_file('source/' . $xml->source, $_POST['InPrefix'], $data_language);
            $run_sql = true;
        }

        $f=fopen("../class/Constants.php","w");
   $database_inf="<?php
     define('BASE_DIR', '".$_POST['InURL']."');
     define('HOST', '".$_POST['InHost']."');
     define('DB_NAME', '".$_POST['InDatabase']."');
     define('DB_USER', '".$_POST['InUsername']."');
     define('DB_PASS', '".$_POST['InPassword']."');
     ";
  if (fwrite($f,$database_inf)>0){
   fclose($f);
  }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php echo $xml->copyright ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="TeaFramework Installation Tool">
        <meta name="author" content="Basilio Fajardo Gálvez">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <style type="text/css">
			.navbar-static-top {
			  margin-bottom: 19px;
              background: #78D4E2;
              border-bottom: 5px solid #3FB4C6;
			}
            .navbar > .container .navbar-brand {
              color: #fff;
              font-size: 26px;
              
            }
            .navbar-inverse .navbar-nav > .dropdown > a {
                color: #fff;
            }
            .navbar-inverse .navbar-nav > .dropdown > a .caret {
               border-top-color: #fff;
               border-bottom-color: #fff;
            }
            .img-circle {
    float: left;
    width: 24px;
    height: 24px;
    overflow: hidden;
    border-radius: 50%;
    cursor: pointer;
}
        </style>
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
    </head>

   <!-- Static navbar -->
    <div class="navbar navbar-inverse navbar-static-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href=""><?php echo $xml->title ?></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php if($_SESSION['language'] == 'fr' ) { ?><img class='img-circle' src='images/fr.png'> <?php } else if($_SESSION['language'] == 'en' ) { ?><img class='img-circle' src='images/en.png'><?php } ?> <b class="caret"></b></a>
              <ul class="dropdown-menu">
				<?php
				$n_choose = count($data_language[0]->choose->option);
				for ($z = 0; $z <= $n_choose - 1; $z++) {
					echo '<li><a href="#language" id="' . $data_language[0]->choose->option[$z]['value'] . '">' . $data_language[0]->choose->option[$z] . '</a></li>';
				}
				?>
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
	
    <div class="container">
        <form action="" method="post" id="ch_language" name="ch_language">
            <input type="hidden" name="input_language" id="input_language" value="<?php echo $_SESSION['language'] ?>">
        </form>
        <form action="" method="post" id="InstallTables" name="InstallTables">
            <input type="hidden" name="InURL" id="InURL" value="<?php echo $inputURL ?>">
            <input type="hidden" name="InHost" id="InHost" value="<?php echo $inputHost ?>">
            <input type="hidden" name="InDatabase" id="InDatabase" value="<?php echo $inputDatabase ?>">
            <input type="hidden" name="InUsername" id="InUsername" value="<?php echo $inputUsername ?>">
            <input type="hidden" name="InPassword" id="InPassword" value="<?php echo $inputPassword ?>">
            <input type="hidden" name="InPrefix" id="InPrefix" value="<?php echo $inputPrefix ?>">
        </form>
        <div id="begin_install">
            <div class="jumbotron">
                <h1><?php echo $xml->title ?></h1>
                <p class="lead"><?php echo translate('Para finalizar el proceso de instalación de la aplicación se debe completar y seguir todos los pasos, hasta conseguir la instalación completa') ?></p>
                <a class="btn btn-lg btn-success" href="#step1"><?php echo translate('Empezar Ahora') ?></a>
            </div>
        </div>
        <div class="row" id="step1" style="display:none">
         <div class="col-md-3">
                <div class="poll">
                    <div class="title"><?php echo translate('Progreso de la Instalación') ?></div>
                    <small class="pull-right">10%</small>&nbsp;
                    <div class="progress">
                      <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 10%;">
                        <span class="sr-only">10% <?php echo translate('Completado') ?></span>
                      </div>
                    </div>
                    <div class="total">
                        <?php echo translate('Paso') ?> <span class="step"></span>  </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class='page-header'>
                    <h3><?php echo translate('Paso') ?> 1 - <?php echo translate('terms') ?></h3>  
                    <?php
 $next = true;
                if(!isset($_POST['tos'])){
                    echo '<div class="alert alert-danger toserror" style="display:none;"><img src="images/disable.png" border="0" style="margin-right:5px" />'.translate('You must agree to the Terms and Conditions').'</div>';
                }
                ?>
                   
                    <p style='height: 300px; overflow: auto; background: #eee; padding: 10px; text-align: justify;'>
By installing this software, you are agreeing to be bound by these web site Terms and Conditions of Use, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local
laws. If you do not agree with any of these terms, you are prohibited from using or accessing this site. The materials contained in this web site are protected by applicable copyright and trade mark law. 
<br><br>
<b>1. Definitions</b><br>
<br>
Business Content has the meaning given in Clause 8(a). Generated Content has the meaning given in Clause 4(i). Management Services has the meaning given in Clause 4. Actino Incorporated Services means the services described in Clause 2 (Registration and Actino Incorporated), Clause 3 (Creation and/or management of Business social media), Clause 4 (Management Services) and Clause 5 (Third Party Sites). Service Content means Generated Content and Business Content. Third Party Sites means third party web sites and services, such as search sites, social networks, and messaging services including but not limited to Facebook, Google+ and Twitter. Web means the World Wide Web. All other terms not defined in these Actino Incorporated Terms shall have the meaning ascribed to such terms in the Advertising Terms and Conditions.
<br><br>
<b>2. Registration and Actino Incorporated</b><br>
<br>
Registration Information. Business must deliver to the Company true, accurate, current, and complete information about the Business required by the Company to enable Company to register the Business as a customer (“Registration Information”) and promptly deliver to the Business updated Registration Information to keep the Registration Information true, accurate, current, and complete at all times. If Business provides any Registration Information that is untrue, inaccurate, out dated, or incomplete, or if Company has reasonable grounds to suspect that such information is untrue, inaccurate, out dated, or incomplete, Company may suspend or terminate the Actino Incorporated Services. Business must not share its password with third parties or otherwise provide access to third parties. If the security of Business username or password is compromised in any way, or if Business or its agent suspects that it may be, Business must immediately notify the Company. Company is not responsible for any loss or damage suffered by the Business if any password is compromised. Business acknowledges and agrees that it does not have, nor will it claim any right, title or interest in the Actino Incorporated Strategy, data, applications, methods of doing business or any elements thereof, or any content provided by Actino Incorporated.
<br><br>
<b>3. Creation and/or Management of Business Social Media</b><br>
<br>
Company must, and Business authorises Company to, create a social media presence. The Page created by the Company must contain marketing / promotional content, general information and contact information for the Business’s business. Company reserves the right to remove, take down, or withdraw content from the Business social media and terminate the management of the Business social media for any reason in Company’s absolute discretion, including but not limited to; (i) if Company believes that Business or anyone using Business’s account has violated these Actino Incorporated Terms or otherwise engaged in any activity that could result in harm or legal liability to Company or to any third party; or (ii) if Business fails to pay Company all fees for Actino Incorporated Services. Any loss of data encountered during page conversions are not the responsibility of Actino Incorporated.
<br><br>
<b>4. Management Services</b><br>
<br>
The Management Services shall initially include the following: (i) Content Creation Services. Company must provide Content Creation Services. Company must create and disseminate marketing/promotional content for Business’s business (“Generated Content”) in accordance with the relevant social media content guidelines. Business must read the Content Guidelines and by signing the Order, Business acknowledges that it has read and understands the Content Guidelines. Facebook may from time to time, change the Content Guidelines. Company is not responsible or liable in any way for any loss or damage incurred by the Business in relation to Generated Content that has been posted by the Company. If Company creates content that requires approval, Company will provide Business with notification before it goes live and Business may accept, reject, or modify such content, in Business’s discretion. (ii) Reputation Management Services. Company must provide Reputation Management Services. Business authorises Company to monitor the Internet for mentions of Business. However, Business acknowledges and agrees that Company cannot and does not guarantee the accuracy or completeness of Company’s monitoring services. In addition, Business acknowledges and agrees that Company may, for technical reasons, decide to reduce the volume of information provided to Business through the Reputation Management Services.
<br><br>
<b>5. Third Party Sites</b><br>
<br>
Business authorises Company to enter into on its behalf relationships with Third Party Sites. By authorising Company to establish relationships on Business’s behalf with Third Party Sites, Business agrees to be bound by the terms and conditions applicable to such Third Party Sites. Any activity that Business engages in on such Third Party Sites or that Company engages in on Business’s behalf, together with any information that Business submits or that Company submits on Business’s behalf to such Third Party Sites will be subject to the terms and conditions, including the privacy policies, governing such Third Party Sites. It is Business’s responsibility to read and comply with the terms and conditions of Third Party Sites. Company assumes no responsibility or liability to Business if Business uses the Third Party Sites directly. Because of the interconnected nature of Third Party Sites with other Web sites and services, Service Content posted to Third Party Sites may be difficult to remove. For example, Service Content that is removed from a Third Party Site may remain on other Web sites (including end-user Web pages) or may be cached in search engine indexes. Accordingly, although Company will use commercially reasonable efforts to remove Service Content from Third Party sites when directed by Business to do so through the Actino Incorporated Services, Company cannot guarantee that Service Content will be completely removed from the Third Party Sites. The Third Party Sites that Company supports may change over time. Company may in its absolute discretion cease to provide the Actino Incorporated Services through a Third Party Site or add a Third Party Site.
<br><br>
<b>6. Business Authorisations</b><br>
<br>
Business expressly authorises Company to do all things deemed reasonably necessary by the Company to perform its obligations under these Actino Incorporated Terms including but not limited to; (i) establishing or assuming control of relationships on Business’s behalf with Third Party Sites, (ii) submitting and managing the Generated Content on or through the Business social media and Third Party Sites, (iii) registering Business with such Third Party Sites using information Business provided to Company (including the Registration Information), (iv) generating and storing passwords for such Third Party Sites so that Company may administer and update Business’s presence and post and manage the Service Content on such Third Party Sites, (v) using the functionality of such Third Party Sites on Business’s behalf, in any manner Company see fit, for the purposes of performing the Actino Incorporated Services (e.g. if the Business Facebook Page is linked to Twitter, Company is authorised to decide who can “follow” Business and who Business’s account will “follow”. Similarly, if Business has, for instance, a Google Places listing, Company is authorised to access and manage that listing for the benefit of Business or if Business does not have a Google Places listing, Company will be authorised create one for Business). Business must provide all such assistance and information necessary to enable Company to perform its obligations under these Actino Incorporated Terms and understands that Business’s failure to do so will adversely affect Company’s ability to provide the Actino Incorporated Services.
<br><br>
<b>7. Business Conduct</b><br>
<br>
Business must not use the Actino Incorporated Services or Third Party Sites to: (i) violate any law or regulation; (ii) violate or infringe other people’s intellectual property, privacy, publicity, or other legal rights; (iii) promote, post, or transmit anything that is illegal, defamatory, abusive, harassing, pornographic, indecent, profane, obscene, hateful, racist, or otherwise objectionable; (iv) as part of a trade-mark, design-mark, trade-name, business name, service mark, or logo; (v) send unsolicited or unauthorized advertising or commercial communications, such as spam; (vi) sell, license or otherwise disseminate consumer information, collected through use of the Actino Incorporated Services, to third parties for the purposes of Third Party marketing; (vii) transmit any malicious or unsolicited software; (viii) upload, post, or otherwise transmit any material that contains software viruses or any other computer code, files, or programs designed to interrupt, destroy, or limit the functionality of any computer software or hardware or telecommunications equipment; (ix) stalk, harass, or harm another individual or business; (x) impersonate any person or entity, or misrepresent Business’s affiliation with a person or entity; (xi) forge headers or otherwise manipulate identifiers in order to disguise the origin of any Content transmitted through the Actino Incorporated Services or develop restricted or password-only access pages, or hidden pages or images (those not linked to from another accessible page); (xii) use any means to “harvest,” “scrape,” “crawl,” “reverse engineer” or “spider” any Web pages or content contained in the Actino Incorporated Services. (xiii) provide any false or misleading information via the Actino Incorporated Services, or create an account for anyone other than Itself; (xiv) make any offers via Actino Incorporated Services which Business cannot or does not intent to honour per the terms of such offer; or (xv) interfere with or disrupt the Actino Incorporated Services. In addition, Business agrees not to use the Actino Incorporated Services in connection with any business that: (xvi) is primarily in the business of collecting, and then selling, licensing or otherwise disseminating consumer information for the purposes of Third Party marketing; or (xvii) promotes or involves pornography or explicit sexual images or merchandise.
<br><br>
<b>8. Business Content</b><br>
<br>
(a) Intellectual Property. Business Content means all content, including any text, images, logos, trademarks, service marks, promotional materials, product or service information, comments, reviews, photos, audio and video clips and other information, that Business posts or shares on the Business social media or Third Party Sites, including any content from Business’s existing web site (“Business Content”). Business represents and warrants that: (i) Business owns all rights to Business’s Content or, alternatively, that Business has the unrestricted right to give Company the rights described above, including the right to distribute Business’s Content on or through Third Party Sites (as described herein); (ii) Business has paid and will pay in full any fees or other payments that may be related to the use of Business’s Content; and (iii) Business Content does not infringe the intellectual property rights, privacy rights, publicity rights, moral rights or other legal rights of any third party. (b) Business Content Licence Business grants to Company and its affiliates a nonexclusive, royalty free, fully-paid, perpetual, worldwide irrevocable, license to use, reproduce, display, perform, adapt, modify, distribute, make derivative works of and otherwise exploit the Business Content in connection with the Actino Incorporated Services including, but not limited to advertising and promoting Actino Incorporated, the Actino Incorporated Services and may refuse to accept or transmit Business Content. Company may, but is not obligated to, remove Business Content from Third Party Sites or the social media for any reason. Business acknowledges and agrees that Business is solely responsible for inserting any content on the Business social media that Business may be legally required to include. For example, if Business is a law firm and applicable laws require Business to include certain disclaimers on Web sites, it is Business’s responsibility to insert such disclaimers.
<br><br>
<b>9. Copyright and Intellectual Property</b><br>
<br>
Company respects the intellectual property rights of others. Company may remove Service Content or other applicable content available on the Business social media for any reason, including, without limitation, on receiving notice from a third party that the service content violates copyright or other intellectual property rights of a third party.
<br><br>
<b>10. Business Interactions with Others</b><br>
<br>
Company is not responsible or liable for any damage or loss related to Business’s interactions with end-users, end-user’s interactions with Business or interactions between end-users that may occur on Business social media or in connection with Business presence on any Third Party Site. Company is not a party to any transaction between Business and any end user and Company has no obligation to monitor or enforce any such transactions. Business is responsible for complying with all applicable laws, rules and regulations. For example, Business is responsible for complying with all applicable laws, rules, and regulations that may apply to Business’s communications with end-users and other people including but not limited to the Privacy Act 1988 (Cth). Business is responsible for complying with all applicable privacy laws, rules, and regulations with respect to any information Business obtains from others whether directly in the operation of Business, the Business social media, or through the Actino Incorporated Services. Business is also responsible for complying with all applicable laws, rules, and regulations in relation to any sweepstakes, contests, or promotions that Business makes available or publicises through the Business social media or the Actino Incorporated Services.
<br><br>
<b>11. Privacy</b><br>
<br>
Company respects Business privacy and the Company’s Privacy Policy. Business understands and agrees that Business privacy practices with respect to information provided by visitors to the Business social media must comply with the terms of such Privacy Policy.
<br><br>
<b>12. Links</b><br>
<br>
The Actino Incorporated Services may contain links to other Web sites, or allow others to send Business such links. Company does not represent or warrant the accuracy or lawfulness of the content and information contained on any such Web sites. A link to a third party’s Web site does not mean that Company endorses it or that Company is affiliated with it. Company is not responsible or liable for any damage or loss related to the use of any third party Web site. Business should always read the terms and conditions and privacy policy of a third party Web site before using it.
<br><br>
<b>13. Changes to the Actino Incorporated Services</b><br>
<br>
Company often enhances and updates the Actino Incorporated Services. Company may change or discontinue the Actino Incorporated Services at any time, for any reason, with or without notice to Business. Business understands and acknowledges that Company may change the Actino Incorporated Services, at any time in its absolute discretion.
<br><br>
<b>14. Disclaimers and Exclusion of Warranties</b><br>
<br>
Business understands and agrees THAT the Actino Incorporated Services are provided to it on an “AS IS” and “AS AVAILABLE” basis. IN ADDITION TO THE DISCLAIMERS PROVIDED IN THE ADVERTISING TERMS and TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, COMPANY AND ITS SUBSIDIARIES, AFFILIATES, OFFICERS, EMPLOYEES, AGENTS, PARTNERS AND LICENSORS: (I) make no REPRESENTATIONS OR WARRANTIES about the completeness, accuracy, availability, timeliness, security or reliability of the BUSINESS social media OR THE OTHER Actino Incorporated SERVICES; (II) make no REPRESENTATIONS OR WARRANTIES about THE ACCURACY OR COMPLETENESS OF ANY INFORMATION PROVIDED TO BUSINESS BY OR ON BEHALF OF THE COMPANY INCLUDING THE GENERATED CONTENT AND ANY REPORTING, ANALYTICS AND SIMILAR INFORMATION, ANY CONTENT AVAILABLE ON OR THROUGH THE Actino Incorporated SERVICES (INCLUDING ANY CONTENT SUBMITTED BY AN END-USER), OR THE CONTENT OF ANY WEB SITES OR RESOURCES LINKED TO THE BUSINESS social media; (III) make no REPRESENTATIONS OR WARRANTIES that the Actino Incorporated Services will meet Business requirements or be available on an uninterrupted, secure, or error-free basis. (iv) will not be responsible or liable for any harm to your computer system , loss of data , or other harm that results from your access to or use of the Actino Incorporated Services, liability for any loss, damage, cost or expense (whether direct or indirect, including consequential loss ), however incurred, including in tort. COMPANY AND ITS SUBSIDIARIES, AFFILIATES, OFFICERS, EMPLOYEES, AGENTS, PARTNERS AND LICENSORS total liability to you for a breach of any Non-excludable Condition, is limited, at our option, to any one of supplying or replacing, or paying the cost of supplying or replacing any sERVICES supplied to you or supplying again, or paying the cost (subject to a maximum of THE GREATER OF $100.00 OR THE AMOUNT BUSINESS HAS PAID COMPANY IN THE TWELVE (12) MONTH PERIOD PRIOR TO THE RELEVANT EVENT) of supplying again, the services supplied to you in respect of which the breach occurred. “Non-excludable Condition” means an implied condition or warranty the exclusion of which from a contract (including a contract with a consumer as defined in the COMPETITION AND CONSUMER ACT 2010 (Cth)) would contravene any statute or cause part OR ALL OF THIS clause to be void. Notwithstanding any other provision of these Actino Incorporated terms, nothing in these Actino Incorporated terms is intended to exclude any Non-excludable Condition.
<br><br>
<b>15. Changes to Actino Incorporated Terms</b><br>
<br>
Company reserves the right, at its absolute discretion, to amend these Actino Incorporated Terms at any time by giving at least 7 days notice and Company must use its reasonable efforts to notify Business of those changes. Business’ continued use of the Actino Incorporated Services constitutes acceptance of the amended Actino Incorporated Terms.
<br><br><br>

</p> 
                        <div class="checkbox">
  <label><input type="checkbox" class="tos" name="tos" value="agree">I agree to the terms and conditions</label>
</div>
                    <ul class="pager">
                    <li class="disabled"><a href="#">&larr; <?php echo translate('Anterior') ?></a></li>
                    <li<?php if (!$next) echo ' class="disabled"'; ?>><a <?php if (!$next) echo 'href="#"'; else echo 'href="#next"'; ?>><?php echo translate('Siguiente') ?> &rarr;</a></li>
                </ul>                 
                </div>
           </div>
        </div>
        <div class="row" id="step2" style="display:none">
            <div class="col-md-3">
                <div class="poll">
                    <div class="title"><?php echo translate('Progreso de la Instalación') ?></div>
                    <small class="pull-right">25%</small>&nbsp;
					<div class="progress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 25%;">
						<span class="sr-only">25% <?php echo translate('Completado') ?></span>
					  </div>
					</div>
                    <div class="total">
                        <?php echo translate('Paso') ?> <span class="step"></span>  </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class='page-header'>
                    <h3><?php echo translate('Paso') ?> 2 - <?php echo translate('Requerimientos del sistema') ?></h3>                    
                </div>
                <?php
                $version = explode('.', PHP_VERSION);
                $ext = get_loaded_extensions();
                $extensions = array();
                foreach ($ext as $value) {
                    $extensions[] = strtolower($value);
                }
                $PHP_VERSION_ID = ($version[0] * 10000 + $version[1] * 100 + $version[2]);
                $version_setting = explode('.', $xml->requires->version);
                $n_point = count($version_setting);
                switch ($n_point) {
                    case 1: $required_version = ($version_setting[0] * 10000);
                        break;
                    case 2: $required_version = ($version_setting[0] * 10000 + $version_setting[1] * 100);
                        break;
                    case 3: $required_version = ($version_setting[0] * 10000 + $version_setting[1] * 100 + $version_setting[2]);
                        break;
                }
                define('INSTALL', '<img src="images/check.png" border="0" style="margin-right:5px" />');
                define('DISABLE', '<img src="images/disable.png" border="0" style="margin-right:5px" />');
                if ($PHP_VERSION_ID < $required_version)
                    echo '<div class="alert alert-danger"><img src="images/disable.png" border="0" style="margin-right:5px" />'.translate('Versión').' PHP: ' . PHP_VERSION . ' '.translate('La versión no es compatible').'</div>'; else
                    echo '<div class="alert alert-success"><img src="images/check.png" border="0" style="margin-right:5px" />'.translate('Versión').' PHP: ' . PHP_VERSION . '</div>';
                ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:80%;text-align:left"><?php echo translate('Extensión') ?></th>
                            <th style="width:20%;text-align:left"><?php echo translate('Estado') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $next = true;
                        foreach ($xml->requires->extension as $value) {
                            echo '<tr><td width="250">' . ucfirst($value['name']) . '</td><td width="150">';
                            if (in_array(strtolower($value['name']), $extensions))
                                echo INSTALL; else {
                                echo DISABLE;
                                $next = false;
                            }
                            echo '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table> 
                <ul class="pager">
                    <li><a href="#back">&larr; <?php echo translate('Anterior') ?></a></li>
                    <li<?php if (!$next) echo ' class="disabled"'; ?>><a <?php if (!$next) echo 'href="#"'; else echo 'href="#next"'; ?>><?php echo translate('Siguiente') ?> &rarr;</a></li>
                </ul>
            </div>
        </div>
        <div class="row" id="step3" style="display:none">
            <div class="col-md-3">
                <div class="poll">
                    <div class="title"><?php echo translate('Progreso de la Instalación') ?></div>
                    <small class="pull-right">50%</small>&nbsp;
					<div class="progress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 50%;">
						<span class="sr-only">50% <?php echo translate('Completado') ?></span>
					  </div>
					</div>
                    <div class="total">
                        <?php echo translate('Paso') ?> <span class="step"></span>  </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class='page-header'>
                    <h3><?php echo translate('Paso') ?> 3 - <?php echo translate('Conexión con la Base de Datos') ?></h3>                    
                </div>
<?php 
if ((isset($_POST['inputHost'])) && ($error_connecting)) echo'<div class="alert alert-danger error_connecting"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Error!</strong> '.translate('No se ha podido establecer conexión con la base de datos').'.</div><script>$(function({$(\'.load_connect\').hide();})</script>'; elseif ((isset($_POST['inputHost'])) && (!$error_connecting)) echo'<div class="alert alert-success success_connecting"><button type="button" class="close" data-dismiss="alert">&times;</button>'.translate('La conexión con la base de datos se ha realizado con éxito').'.</div><script>$(function({$(\'.load\').hide();})</script>'; ?>
                <?php function url(){
                    
                      return sprintf(
                        "%s://%s/",
                        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                        $_SERVER['SERVER_NAME']
                      );
                    }
                    ?>
                <form action="" role="form" id="form_connect" name="form_connect" style="width:50%" method="post">
                    <div class="control-group">
                        <label for="inputURL"><?php echo translate('Website URL') ?></label>
                        <input type="text" class="form-control" id="inputURL" name="inputURL" value="<?php echo url(); ?>" required="required" />
                   <span class="help-block"><?php echo translate('Note:') ?></span>
                    </div>
                     <div style='border-bottom: 3px solid #ccc; padding-bottom: 10px; margin-bottom: 10px; clear: both;'></div>
                    <div class="control-group">
                        <label for="inputHost"><?php echo translate('Nombre del Host') ?></label>
						<input type="text" class="form-control" id="inputHost" name="inputHost" value="<?php echo $xml->values->host ?>" required="required" />
						<span class="help-block"><?php echo translate('Nombre del Host ó dirección IP del servidor de la Base de Datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputDatabase"><?php echo translate('Base de Datos') ?></label>
						<input type="text" class="form-control" id="inputDatabase" name="inputDatabase" value="<?php echo $xml->values->database ?>" required="required" />
						<span class="help-block"><?php echo translate('Nombre de la Base de datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputUsername"><?php echo translate('Nombre de Usuario') ?></label>
						<input type="text" class="form-control" id="inputUsername" name="inputUsername" value="<?php echo $xml->values->username ?>" required="required" />
						<span class="help-block"><?php echo translate('Nombre de usuario para la conexión con la Base de Datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputPassword"><?php echo translate('Contraseña') ?></label>
						<input type="password" class="form-control" id="inputPassword" name="inputPassword" value="" required="required" />
						<span class="help-block"><?php echo translate('Contraseña de la Base de Datos') ?></span>
                    </div>
                    <div class="control-group">
                        <label for="inputPrefix"><?php echo translate('Prefijo') ?></label>
						<input type="text" class="form-control" id="inputPrefix" name="inputPrefix" value="<?php echo $xml->values->prefix ?>" required="required" />
						<span class="help-block"><?php echo translate('Prefijo de las tablas') ?></span>
                    </div>
                </form>                
            </div>    
            <ul class="pager">
                <li><a href="#back">&larr; <?php echo translate('Anterior') ?></a></li>
                <li><span class="load" style="display:none"><img src="images/load.gif" style="margin-right:5px;vertical-align:middle" alt="Installing..." /><?php echo translate('Espere, creando estructura de tablas') ?>...</span><span class="load_connect" style="display:none"><img src="images/load.gif" style="margin-right:5px;vertical-align:middle" alt="Connecting..." /><?php echo translate('Espere, conectando con la Base de Datos') ?>...</span> <a <?php if ($error_connecting) echo 'href="#connect_db"'; else echo 'href="#datatables"'; ?>><?php if ($error_connecting) echo translate('Check Connection'); else echo translate('Install Database'); ?> &rarr;</a></li>
            </ul>
        </div>
        <div class="row" id="step4" style="display:none">
            <div class="col-md-3">
                <div class="poll">
                    <div class="title"><?php echo translate('Progreso de la Instalación') ?></div>
                    <small class="pull-right">100%</small>&nbsp;
					<div class="progress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
						<span class="sr-only">100% <?php echo translate('Completado') ?></span>
					  </div>
					</div>
                    <div class="total">
                        <?php echo translate('Paso') ?> <span class="step"></span>  </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class='page-header'>
                    <h3><?php echo translate('Paso') ?> 4 - <?php echo translate('Instalación Completada') ?></h3>                    
                </div>
                <div class="alert alert-warning" style="font-size:16px;margin-bottom:45px">
                    <span class="glyphicon glyphicon-warning-sign"></span> <?php echo translate('La instalación ha sido completada. Para mayor seguridad borre la carpeta') ?> <b>/install</b>.
                </div>
                <div class="well" style="padding-bottom:45px;margin-bottom:50px">
                    <h3 style="margin-left:25px"><?php echo translate('Resumen de la Instalación') ?></h3>
                    <div>
                    <table border="0" cellspacing="10" style="margin-left:25px">
                        <tr>
                            <td style="width:300px" class="text-success"><?php echo translate('Sentencias SQL Ejecutadas con éxito') ?>:</td><td class="text-success"><b><?php if(isset($result['success'])) echo $result['success']; ?></b></td>
                        </tr>
						<?php echo($result['failed'] >= 1)?'<tr><td><code>'.translate('Las sentencias fallidas están localizadas en el archivo').': log/failed.log</code></td></tr>':''; ?>
                        <tr>
                            <td class="text-error"><?php echo translate('Sentencias SQL Fallidas') ?>:</td><td class="text-error"><b><?php if(isset($result['failed'])) echo $result['failed']; ?></b></td>
                        </tr>
                        <tr>
                            <td class="text-info"><?php echo translate('Total Sentencias SQL Ejecutadas') ?>:</td><td class="text-info"><b><?php if(isset($result['total'])) echo $result['total']; ?></b></td>
                        </tr>
                    </table>
                    </div>
                </div>                
            </div>    
        </div>
        <hr/>
        <div class="footer">
            <p>&copy; <?php echo $xml->copyright ?></p>
        </div>
    </div> <!-- /container -->

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/install.js"></script>
    <script>
        $(document).ready(function(){
<?php

if (!$run_sql) {

    if (isset($_POST['inputHost']))
        echo '$("#begin_install").hide(); $("#step3").show(); $(".step").html("3/4"); step=3; $("#inputURL").val("' . $_POST['inputURL'] . '"); $("#inputHost").val("' . $_POST['inputHost'] . '"); $("#inputDatabase").val("' . $_POST['inputDatabase'] . '"); $("#inputUsername").val("' . $_POST['inputUsername'] . '"); $("#inputPassword").val("' . $_POST['inputPassword'] . '"); $("#inputPrefix").val("' . $_POST['inputPrefix'] . '");';
    if ((isset($_POST['inputHost'])) && (!$error_connecting))
        echo '$(".form-control").attr("disabled","disabled")';
}else {
    echo'$("#begin_install").hide(); $("#step4").show(); $(".step").html("4/4");';

}


?>
    });
    </script>
</body>
</html>
