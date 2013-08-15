<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2012 by Georg Großberger <georg@grossberger.at>                 *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the BSD 3-Clause License.                              *
 *                                                                     */

namespace RSA;

/**
 * A public / private key pair
 *
 * @author Georg Großberger <georg@grossberger.at>
 * @copyright 2012 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class KeyPair {

	/**
	 * The Public Key
	 *
	 * @var string
	 */
	private $publicKey;

	/**
	 * The Keys exponent
	 *
	 * @var integer
	 */
	private $exponent;

	/**
	 * The private key
	 *
	 * @var string
	 */
	private $privateKey;

	/**
	 * A backend instance
	 *
	 * @var BackendInterface
	 */
	private static $backend;

	/**
	 * Constructor
	 * Sets the keys properties
	 *
	 * @param string $publicKey
	 * @param string $exponent
	 * @param string $privateKey
	 */
	private final function __construct($publicKey, $exponent, $privateKey) {
		$this->publicKey = $publicKey;
		$this->exponent = $exponent;
		$this->privateKey = $privateKey;
	}

	public function __sleep() {
		return array('publicKey', 'exponent', 'privateKey');
	}

	/**
	 * Encrypt using private key
	 *
	 * @param string $plainText
	 * @return string
	 */
	public function encrypt($plainText) {
		return self::getBackend()->encrypt($this, $plainText);
	}

	/**
	 * Decrypt using private key
	 *
	 * @param string $encrypted
	 * @return string
	 */
	public function decrypt($encrypted) {
		return self::getBackend()->decrypt($this, $encrypted);
	}

	/**
	 * Create a javascript string that makes a new Encrypter instance in the variable given
	 *
	 * @param string $objectName
	 * @return string
	 */
	public function toJavascript($objectName = 'rsaEncrypter') {
		return
			'var ' . $objectName . '=new Encrypter("' .
				$this->getPublicKey() .
				'","' .
				sprintf('%x', $this->getExponent()) .
			'");';
	}

	/**
	 * Create a new KeyPair
	 * @static
	 * @return KeyPair
	 */
	public static function createNew() {
		list($publicKey, $exponent, $privateKey) = self::getBackend()->createKeys();
		return new KeyPair($publicKey, $exponent, $privateKey);
	}

	/**
	 * Get the backend, creates it if it does not exist
	 *
	 * @static
	 * @return BackendInterface
	 * @throws NoBackendException
	 */
	private static function getBackend() {
		if (!self::$backend) {
			$backend = new ModuleBackend();
			if ($backend->isAvailable()) {
				self::$backend = $backend;
			} else {
				$backend = new CommandLineBackend();
				if ($backend->isAvailable()) {
					self::$backend = $backend;
				} else {
					$backend = new SeclibBackend();
					if ($backend->isAvailable()) {
						self::$backend = $backend;
					}
				}
			}

			if (!self::$backend) {
				throw new NoBackendException('Unable to optain a backend, please check your PHP configuration');
			}
		}
		return self::$backend;
	}

	/**
	 * Returns Exponent
	 *
	 * @return integer
	 */
	public function getExponent() {
		return $this->exponent;
	}

	/**
	 * Returns Private Key
	 *
	 * @return string
	 */
	public function getPrivateKey() {
		return $this->privateKey;
	}

	/**
	 * Returns Public Key
	 *
	 * @return string
	 */
	public function getPublicKey() {
		return $this->publicKey;
	}
}
