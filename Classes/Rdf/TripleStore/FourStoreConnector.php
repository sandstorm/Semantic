<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Rdf\TripleStore;

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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class FourStoreConnector extends AbstractConnector {

	/**
	 * @var \SandstormMedia\Semantic\Rdf\TripleStore\HttpRequestService
	 * @FLOW3\Inject
	 */
	protected $httpRequestService;

	/**
	 * base URI, with NO trailing slash
	 *
	 * @var string
	 */
	protected $baseUri;


	public function injectSettings($settings) {
		if (!isset($settings['4Store']['baseUri'])) {
			throw new \Exception('TODO: 4store Base Uri not set.');
		}
		$this->baseUri = rtrim($settings['4Store']['baseUri'], '/');
	}

	public function storeObject($object) {

	}

	public function addOrUpdateGraph($graphUri, $dataAsTurtle) {
		$uri = $this->baseUri . '/data/' . $graphUri;
		$this->httpRequestService->putStringToUri($dataAsTurtle, $uri, 'Content-Type: application/x-turtle', 201);
	}

	public function removeGraph($graphUri) {
		$uri = $this->baseUri . '/data/' . $graphUri;
		$this->httpRequestService->delete($uri);
	}
}
?>