<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Controller;

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

use Guzzle\Http\Message\RequestFactory;
use SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class ContinuousTextEnrichmentController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @return string
	 * @skipCsrfProtection
	 */
	public function indexAction() {
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function enrichAction($value) {
		$client = new \Guzzle\Service\Client('http://api.opencalais.com/tag/rs/enrich');
		$request = $client->post('');
		$request->setHeader('x-calais-licenseID', 'qcruddcdafqvc7fdqggpexgh');
		$request->setHeader('content-type', 'text/raw');
		$request->setHeader('accept', 'application/json');
		$request->setBody($value);

		$response = $request->send();
		$response = json_decode($response->getBody(TRUE));

		$annotatedString = new \SandstormMedia\Semantic\Domain\Model\AnnotatedString($value);
		foreach ($response as $val) {
			if (!isset($val->_type) || $val->_typeGroup !== 'entities') {
				continue;
			}

			if (!$val->_typeReference) continue;

			$type = new NamedNode($val->_typeReference);
			$subject = $this->findIdentifier($val);
			if (!$subject) continue; // TODO: Implement some clever searching here at dbpedia, geonames, etc, if identifier is not found (based on the Type reference)

			foreach ($val->instances as $instance) {
				$annotatedString->add($subject, $type, $instance->offset, $instance->length);
			}

		}
		return $annotatedString->getStringWithAnnotations();
	}

	protected function findIdentifier($val) {
		if (isset($val->resolutions)) {
			foreach ($val->resolutions as $resolution) {
				if ($resolution->id) return new NamedNode($resolution->id);
			}
		}
		return NULL;
	}
}
?>