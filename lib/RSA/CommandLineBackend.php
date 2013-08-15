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
 * Backend that uses the CLI for openssl access
 *
 * @author Georg Großberger <georg@grossberger.at>
 * @copyright 2012 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class CommandLineBackend implements BackendInterface {

	protected $program;

	protected $tempDirectory;

	public function __construct() {

		$this->tempDirectory = $_ENV['TMP'] . DIRECTORY_SEPARATOR . 'php-rsa-' . DIRECTORY_SEPARATOR . uniqid() . DIRECTORY_SEPARATOR;
		if (!is_dir($this->tempDirectory)) {
			mkdir($this->tempDirectory, 0700, TRUE);
		}

		if (defined('OPENSSL_EXECUTABLE')) {
			$this->program = escapeshellcmd(OPENSSL_EXECUTABLE);
		} else {
			if (DIRECTORY_SEPARATOR === '/') {
				$result = shell_exec('which openssl');
				if (preg_match('/^\/([a-z0-9\-\.\/]+)\/openssl$/', $result) && is_executable($result)) {
					$this->program = escapeshellcmd($result);
					return;
				}
			}

			$paths = explode(PATH_SEPARATOR, $_ENV['PATH']);

			foreach ($paths as $path) {
				if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
					$path .= DIRECTORY_SEPARATOR;
				}
				$program = $path . 'openssl';
				if (DIRECTORY_SEPARATOR === '\\') {
					$program .= '.exe';
				}
				if (is_executable($program)) {
					$this->program = escapeshellcmd($program);
					return;
				}
			}
			$this->program = escapeshellcmd('openssl');
		}
	}

	/**
	 * Test if this backend is available
	 *
	 * @return boolean
	 */
	public function isAvailable() {
		$output = shell_exec($this->program . ' version');
		return strpos($output, 'OpenSSL') !== FALSE;
	}

	/**
	 * Generates a new key pair and returns it as an array, which has
	 * 0 => Public Key
	 * 1 => Exponent
	 * 3 => Private Key
	 *
	 * @throws KeyGenerationException
	 * @return array
	 */
	public function createKeys() {
		$exponent = 0x10001;

		$command = $this->program . ' genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:1024';
		$privateKey = shell_exec($command);

		if (strpos($privateKey, 'BEGIN RSA PRIVATE KEY') === FALSE || strpos($privateKey, 'END RSA PRIVATE KEY') === FALSE) {
			throw new KeyGenerationException('Unable to generate a private key');
		}

		$tmpFile = tempnam($this->tempDirectory, uniqid());
		file_put_contents($tmpFile, $privateKey);

		$command = $this->program . ' rsa -noout -modulus -in ' .escapeshellarg($tmpFile);
		$output = shell_exec($command);

		unlink($tmpFile);

		if (substr($output, 0, 8) !== 'Modulus=') {
			throw new KeyGenerationException('Unable to generate a public key modulus');
		}

		$publicKey = substr($output, 8);

		return array($publicKey, $exponent, $privateKey);
	}

	/**
	 * Encrypt the given text with the given key pair
	 *
	 * @param KeyPair $key
	 * @param string $plainText
	 * @return string
	 */
	public function encrypt(KeyPair $key, $plainText) {
		return $this->rsaUtil($key->getPrivateKey(), $plainText, 'decrypt');
	}

	protected function rsaUtil($key, $text, $action) {

		if ($action !== 'encrypt' && $action !== 'decrypt') {
			throw new DecryptionException('Action not valid');
		}

		$tmpKeyFile  = tempnam($this->tempDirectory, uniqid());
		$tmpDataFile = tempnam($this->tempDirectory, uniqid());

		file_put_contents($tmpKeyFile, $key);
		file_put_contents($tmpDataFile, $text);

		$command = $this->program .
			' rsautl -inkey ' . escapeshellarg($tmpKeyFile) .
			' -in ' . escapeshellarg($tmpDataFile) .
			' -' . $action;
		$output = shell_exec($command);

		unlink($tmpDataFile);
		unlink($tmpKeyFile);

		return $output;
	}

	/**
	 * Decrypt the given message using the given key pair
	 *
	 * @abstract
	 * @param KeyPair $key
	 * @param string $encryptedText
	 * @return string
	 */
	public function decrypt(KeyPair $key, $encryptedText) {
		return $this->rsaUtil($key->getPrivateKey(), $encryptedText, 'decrypt');
	}
}
