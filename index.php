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
ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>Installation Script</title>
</head>
<body>
<?php require 'class/Constants.php';
if (!defined('DB_NAME')) {
   header('Location: install');
   exit;
 } else {
$link = mysqli_connect(HOST,DB_USER,DB_PASS, DB_NAME);
if (!$link) {
     header('Location: install');
   	exit;
}
}
?>
  <p>This is our site.</p>
</body>
</html>
<?php
ob_end_flush();
?>