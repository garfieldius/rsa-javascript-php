# RSA Framework for Browser-Server

A simple framework for RSA encryption of form field values in browser - server communication using Javascript and PHP.

## Useage

### Requirements

You will need a server with PHP 5.3 installed and a web browser that does not choke on more complicated javascript code.

### Installation

Create an autoloader for the PHP code. eg.:

```php

// Define the folder in which the php files of this lib live in
define('RSA_CLASS_PATH', '/path/to/the/php/folder/in/this/library/');

// Add a simple auto loader
spl_autoload_register(function($class) {
	$class = ltrim($class, '\\');
	if (substr($class, 0, 3) === 'RSA') {
		require_once RSA_CLASS_PATH . substr($class, 4) . '.php';
	}
});

```

Now you can create a key pair like this:

```php

$key = \RSA\KeyPair::createNew();

```

On the client side, include the rsa.js file (or the rsa.min.js file if you want to keep it small). 

```html

<script type="text/javascript" src="javascript/rsa.js"></script>

```

After the script tag, that loads the rsa.js file you can use <code>$key->toJavascript();</code> function to create a code snippet that takes care of setting the values and creating an instance.
eg:

```html

<script type="text/javascript">
	<?php echo $key->toJavascript(); ?>
	
	// Now we can use it
	// by default the RSAKey object lives in the "rsaEncrypter" variable
	var cipherText = rsaEncrypter.encrypt("Something private");
</script>

```	
	
For a full example see the <code>demo/index.php</code> file	

## License

This one is provided under the terms of the GPL v3. See the [FSF GPL website](http://www.gnu.org/licenses/gpl) for details.

## Credits

This library includes the following tools:

* [RSA and ECC in JavaScript](http://www-cs-students.stanford.edu/~tjw/jsbn/) toolset, (c) 2005 by Tom Wu, released under a [BSD license](http://www-cs-students.stanford.edu/~tjw/jsbn/LICENSE)
* [phpseclib](http://phpseclib.sourceforge.net/) released under a [MIT License](http://www.opensource.org/licenses/mit-license.html)

