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
 * Backend using the OpenSSL PHP module
 *
 * @package Uglifier
 * @author Georg Großberger <georg@grossberger.at>
 * @copyright 2012 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleBackend implements BackendInterface {

	/**
	 * Test if this backend is available
	 *
	 * @return boolean
	 */
	public function isAvailable() {
		if (extension_loaded('openssl') && function_exists('openssl_pkey_new')) {
			$testKey = openssl_pkey_new();
			if (is_resource($testKey)) {
				openssl_free_key($testKey);
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Generates a new key pair and returns it as an array, which has
	 * 0 => Public Key
	 * 1 => Exponent
	 * 3 => Private Key
	 *
	 * @return array
	 */
	public function createKeys() {
		// Initialize
		$keyResource = openssl_pkey_new();
		$csr  = openssl_csr_new(array(), $keyResource);

		// Export the private key
		openssl_pkey_export($keyResource, $privateKey);

        // Export the public key
        $pubkey = openssl_pkey_get_details($keyResource);
        $pPubliKey = $pubkey["key"];

		// Export the public key
		openssl_csr_export($csr, $data, FALSE);
		preg_match('/Modulus:\n?(?P<publicKey>[a-f0-9:\s]+)\s*Exponent:\s*(?P<exponent>[0-9]+)/', $data, $matches);

		$publicKey = trim(strtoupper(substr(preg_replace('/[\s\n\r:]+/', '', $matches['publicKey']), 2)));
		$exponent  = (int) $matches['exponent'];

		openssl_free_key($keyResource);
		return array($publicKey, $exponent, $privateKey, $pPubliKey);
	}

	/**
	 * Encrypt the given text with the private key
	 *
	 * @param KeyPair $key
	 * @param string $plainText
	 * @param bool $public encryp with public key
	 * @throws EncryptionException
	 * @return string
	 */
	public function encrypt(KeyPair $key, $plainText, $public=false) {
        if ($public) {
    		$success = openssl_public_encrypt($plainText, $result, $key->getPublicKey(true));
        } else {
    		$success = openssl_private_encrypt($plainText, $result, $key->getPrivateKey());
        }
		if ($success !== TRUE) {
			throw new EncryptionException('Encryption failed');
		}
		return $result;
	}

	/**
	 * Decrypt the given message using the given private key
	 *
	 * @param KeyPair $key
	 * @param string $encryptedText
	 * @param bool $public decryp with public key
	 * @throws DecryptionException
	 * @return string
	 */
	public function decrypt(KeyPair $key, $encryptedText, $public=false) {
		$encryptedText = base64_decode($encryptedText);
        if ($public) {
    		$success = openssl_public_decrypt($encryptedText, $result, $key->getPublicKey(true));
        } else {
    		$success = openssl_private_decrypt($encryptedText, $result, $key->getPrivateKey());
        }
		if ($success !== TRUE) {
			throw new DecryptionException('Decryption failed');
		}
		return $result;
	}
}
