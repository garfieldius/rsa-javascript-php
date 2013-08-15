<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2012 by Georg Großberger <georg@grossberger.at>                 *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

namespace RSA;

/**
 * Interface definition of a OpenSSL Backend
 *
 * @author Georg Großberger <georg@grossberger.at>
 * @copyright 2012 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
interface BackendInterface {

	/**
	 * Test if this backend is available
	 *
	 * @abstract
	 * @return boolean
	 */
	public function isAvailable();

	/**
	 * Generates a new key pair and returns it as an array, which has
	 * 0 => Public Key
	 * 1 => Exponent
	 * 3 => Private Key
	 *
	 * @abstract
	 * @return array
	 */
	public function createKeys();

	/**
	 * Encrypt the given text with the given key pair
	 *
	 * @abstract
	 * @param KeyPair $key
	 * @param string $plainText
	 * @return string
	 */
	public function encrypt(KeyPair $key, $plainText);

	/**
	 * Decrypt the given message using the given key pair
	 *
	 * @abstract
	 * @param KeyPair $key
	 * @param string $encryptedText
	 * @return string
	 */
	public function decrypt(KeyPair $key, $encryptedText);
}
