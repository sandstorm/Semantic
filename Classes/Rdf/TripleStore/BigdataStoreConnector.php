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
class BigdataStoreConnector extends AbstractConnector {

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
		if (!isset($settings['bigdata']['baseUri'])) {
			throw new \Exception('TODO: 4store Base Uri not set.');
		}
		$this->baseUri = rtrim($settings['bigdata']['baseUri'], '/');
	}

	public function addOrUpdateGraph($graphUri, $dataAsTurtle) {
		$rdfXml = $this->convertToRdfXml($dataAsTurtle);
		//$rdfXmlWithProvenance = $this->addProvenanceToRdfXml($rdfXml, $graphUri);


		$uri = $this->baseUri . '?query=' . urlencode(sprintf('CONSTRUCT { <%1$s> ?p ?o } WHERE { <%1$s> ?p ?o. }', $graphUri));

		$tmpPathAndFilename = tempnam(sys_get_temp_dir(), 'tmp.xml');
		file_put_contents($tmpPathAndFilename, $rdfXml);
		// TODO: check that CURL is available
		var_dump($rdfXml);
		exec(sprintf('curl -X PUT --data-binary @%s -H \'Content-Type: application/rdf+xml\' "%s"', $tmpPathAndFilename, $uri));
		unlink($tmpPathAndFilename);
	}

	public function removeGraph($graphUri) {
		throw new \Exception("Not supported yet. TODO: IMplement");
	}

	protected function convertToRdfXml($dataAsTurtle) {
		$inputPathAndFilename = tempnam(sys_get_temp_dir(), 'in');

		file_put_contents($inputPathAndFilename, $dataAsTurtle);
		$output = array();
		$returnValue = 0;
		exec('rapper -o rdfxml -i ntriples ' . $inputPathAndFilename, $output, $returnValue);
		if ($returnValue !== 0) {
			throw new \SandstormMedia\Semantic\Exception('Rapper not installed, use "port install raptor2" to install.', 1324378599);
		}

		unlink($inputPathAndFilename);
		return implode("\n", $output);
	}

	protected function addProvenanceToRdfXml($rdfXmlString, $graphUri) {

		$RDF_NS = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
		$BIGDATA_NS = 'http://www.bigdata.com/rdf#';

		$triples = new \SimpleXMLElement($rdfXmlString);

		$counter = 0;
		foreach ($triples->xpath('rdf:Description/*') as $node) {
			$provenanceNode = 'S' . $counter;
			$counter++;

			$node->addAttribute('bigdata:sid', $provenanceNode, $BIGDATA_NS);

			$provenanceDescription = $triples->addChild('rdf:Description', NULL, $RDF_NS);
			$provenanceDescription->addAttribute('rdf:nodeID', $provenanceNode, $RDF_NS);
			$provenanceDescription->addChild('sm:source', NULL, 'http://sandstorm-media.de/ns/2011/general#')
				->addAttribute('rdf:resource', $graphUri, $RDF_NS);
		}

		return $triples->asXML();
	}
}
?>