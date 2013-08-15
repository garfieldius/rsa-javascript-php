<?php
namespace RSA;
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the BSD 3-Clause License.                              *
 *                                                                     */

/**
 * Publish javascripts and get the URL
 *
 * @package RSA
 * @author Georg Großberger <contact@grossberger-ge.org>
 * @copyright 2013 by Georg Großberger
 * @license GPL v3 http://www.gnu.org/licenses/gpl-3.0.txt
 */
class JavascriptHelper {

	/**
	 * @param string $baseDirectory
	 * @param string $targetDirectory
	 * @param boolean $useMinified
	 * @return string
	 * @throws HelperException
	 */
	public static function getFrontendUrl($baseDirectory = NULL, $targetDirectory = 'scripts', $useMinified = TRUE) {

		$ds = DIRECTORY_SEPARATOR;
		$source = realpath(__DIR__ . $ds . '..' . $ds . '..') .  $ds . 'javascript' . $ds . 'rsa' . ($useMinified ? '.min' : '') . '.js';
		$file = $targetDirectory . '/rsa' . ($useMinified ? '.min' : '') . '.' . filemtime($source) . '.js';

		if (!$baseDirectory) {
			$baseDirectory = $_SERVER['DOCUMENT_ROOT'];
		}

		$target = $baseDirectory . $ds . $file;

		if (!is_file($target)) {

			$dir = dirname($target);

			if (!is_dir($dir)) {
				mkdir($dir, 0777, TRUE);
				if (!is_dir($dir)) {
					throw new HelperException("Cannot auto-publish '$file', please do so manually");
				}
			}

			if (!symlink($source, $target)) {
				if (!copy($source, $target)) {
					throw new HelperException("Cannot auto-publish '$file', please do so manually");
				}
			}
		}

		return $file;
	}
}
