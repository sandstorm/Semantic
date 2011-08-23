<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\TripleStore;

/*                                                                        *
 * This script belongs to the FLOW3 package "Semantic".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
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
			throw new \Exception('TODO: Error in sending HTTP request. Response:' . $response);
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