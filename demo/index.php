<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2012 by Georg Großberger <georg@grossberger.at>                 *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */


require_once '../vendor/autoload.php';

set_exception_handler(function(Exception $e) {
	echo '<pre>';
	echo (string) $e;
	echo '</pre>';
	exit;
});

session_start();
$decrypted=NULL;


if (empty($_SESSION['key']) || !empty($_GET['createNew'])) {

	// If no key pair is found, or the generation of a new one is request
	// create one and store it in the session -> this is unsecure and only meant for demonstration purposes !!!
	$key = \RSA\KeyPair::createNew();
	$_SESSION['key'] = serialize($key);

	// redirect to the page if a createNew request was issued
	if (!empty($_GET['createNew'])) {
		header('Location: index.php');
		exit;
	}

} else {

	// If we have a key pair, load it
	$key = unserialize($_SESSION['key']);


	// If an encrypted text was sent, use the key pair to decrypt it
	if (!empty($_POST['encrypted'])) {
		$decrypted = $key->decrypt($_POST['encrypted']);
		$decrypted = '<h3>Server Side Decryption</h3>' .
					 '<p>Decrypted by PHP to:</p>' .
					 '<p><em>' . $decrypted . '</em></p>' .
					 '<p>This one simply runs<br><code>$key->decrypt($_POST[\'encrypted\']);</code></p>';

		// Another security warning: Sending back the decrypted data makes the whole thing useless !!
		// Here it is done to demonstrate the behaviour, on a real live app you will not want this!

		// In case of an ajax request, output the data and exit
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			echo $decrypted;
			exit;
		}
	}
}


?><!DOCTYPE html>
<html>
<head>
    <title>RSA Demo</title>
	<link rel="stylesheet" type="text/css" href="http://getbootstrap.com/2.3.2/assets/css/bootstrap.css">
</head>
<body>
<div class="container">
	<div class="row">
		<div class="span12">
			<h1 class="page-header">RSA Demo</h1>
		</div>
	</div>
	<div class="row">

		<section class="span4">
			<h3>Text to encrypt</h3>
			<p>
				<textarea cols="70" rows="10" id="plainText">Some sensitive data</textarea><br>
				<button class="btn btn-primary" id="toggle" onclick="EncryptMessage();">Encrypt</button>
				<button class="btn" onclick="location.href='index.php?createNew=1';return false;">Create New Key Pair</button>
			</p>
			<p>This one simply runs <code>rsaEncrypter.encrypt("theText");</code></p>
			<p>The public key modulus used is:<br>
				<code><?php echo $key->getPublicKey(); ?></code>
			</p>

		</section>

		<section class="span4">
			<h3>Encryption result <small>base64 encoded</small></h3>

			<textarea cols="80" rows="10" id="result"></textarea>

			<form action="index.php" method="post" id="decryptForm" style="display: none;">

				<input type="hidden" name="encrypted" id="dataToDecrypt">
				<button id="decrypter" class="btn btn-info">Decrypt on the server</button>
			</form>
		</section>

		<section class="span4" id="decryptionResult">


			<?php

			// Output the decryption result
			if (!empty($decrypted)) {
				echo $decrypted;
			}

			?>
		</section>
	</div>
</div>


<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="<?= \RSA\JavascriptHelper::getFrontendUrl(__DIR__); ?>"></script>
<script type="text/javascript">
	<?php

		// We have loaded the rsa javascript before
		// so we can use this helper to output a
		// javascript snippet that creates a new RSAKey instance
		echo $key->toJavascript();

	?>

	$(function() {
		$("#decrypter").click(function(e) {
			e.preventDefault();
			$.post("index.php", {"encrypted": $("#dataToDecrypt").val()}, function(responseText) {
				$("#decryptionResult").html(responseText);
			})
		});
	});
	function EncryptMessage() {

		// The javascript function created previously creates the instance in the variable
		// "rsaEncrypter" by default, so we can access it directly
		var result = rsaEncrypter.encrypt( document.getElementById("plainText").value );


		$("#result, #dataToDecrypt").val(result);
		$("#decryptForm").show();
	}
</script>

</body>
</html>
