# RSA Framework for Browser-Server

A simple framework for RSA encryption of form field values in browser - server communication using Javascript and PHP.

## Useage

### Requirements

You will need a server with PHP 5.3 installed and a web browser that does not choke on more complicated javascript code.

### Installation

Install via composer

    composer install trenker/simple-rsa

Now you can create a key pair like this:

```php

require 'vendor/autoload.php';

$key = \RSA\KeyPair::createNew();

```

On the client side, include the rsa.js file (or the rsa.min.js file if you want to keep it small).

```html

<script type="text/javascript" src="javascript/rsa.js"></script>

```

You can *autoload* the rsa scripts using the `JavascriptHelper::getFrontendUrl` function

```html

<script type="text/javascript" src="<?= \RSA\JavascriptHelper::getFrontendUrl(); ?>"></script>

```

This will publish the the minified script to an accessible location. By default it is *DOCUMENT_ROOT/scripts/rsa.min.MTIME_TIMESTAMP.js*. This can easily be adjusted using the arguments or the function. Please take a look into the [source file](lib/RSA/JavascriptHelper.php) for details

After the script tag, that loads the rsa.js file you can use `$key->toJavascript();` function to create a code snippet that takes care of setting the values and creating an instance.
eg:

```html

<script type="text/javascript">
	<?php echo $key->toJavascript(); ?>

	// Now we can use it
	// by default the RSAKey object lives in the "rsaEncrypter" variable

	var cipherText = rsaEncrypter.encrypt("Something private");
</script>

```

For a full example see the [demo/index.php](demo/index.php) file

## License

This one is provided under the terms of the GPL v3. See the [FSF GPL website](http://www.gnu.org/licenses/gpl) for details.

## Credits

This library includes the following tools:

* [RSA and ECC in JavaScript](http://www-cs-students.stanford.edu/~tjw/jsbn/) toolset, (c) 2005 by Tom Wu, released under a [BSD license](http://www-cs-students.stanford.edu/~tjw/jsbn/LICENSE)
* [phpseclib](http://phpseclib.sourceforge.net/) released under a [MIT License](http://www.opensource.org/licenses/mit-license.html)

