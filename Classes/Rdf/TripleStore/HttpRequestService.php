<?php
/*                                                                        *
 * This script belongs to the "SandstormMedia.Semantic" package.          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3          *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * © 2011 Sandstorm Media UG (haftungsbeschränkt)                         *
 *        http://sandstorm-media.de                                       */

namespace SandstormMedia\Semantic\Rdf\TripleStore;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class HttpRequestService {

	public function putStringToUri($stringToPut, $uri, $header = '', $expectedHttpResponseCode = NULL) {
		$fh = fopen('php://memory', 'rw');
		fwrite($fh, $stringToPut);
		rewind($fh);

		$ch = $this->initCurl();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_PUT, TRUE);
		curl_setopt($ch, CURLOPT_INFILE, $fh);
		curl_setopt($ch, CURLOPT_INFILESIZE, strlen($stringToPut));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
		$response = curl_exec($ch);
		fclose($fh);

		$info = curl_getinfo($ch);

		if ($expectedHttpResponseCode !== NULL && $expectedHttpResponseCode != $info['http_code']) {
			throw new \Exception('TODO: Error in sending HTTP request. Response:' . curl_error($ch) . $response);
		}
	}

	public function delete($uri) {
		$ch = $this->initCurl();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		$response = curl_exec($ch);
	}

	protected function initCurl() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		return $ch;
	}
}
?>