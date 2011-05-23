<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
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

use F3\Semantic\Domain\Model\Rdf\Concept\NamedNode;
use F3\Semantic\Domain\Model\Rdf\Concept\Literal;
use F3\Semantic\Domain\Model\Rdf\Concept\Triple;
use F3\Semantic\Domain\Model\Rdf\Concept\Graph;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 2 or later
 * @scope singleton
 */
class VoidController extends \F3\FLOW3\MVC\Controller\ActionController {

	protected $defaultViewObjectName = 'F3\Semantic\View\ShowNtView';

	/**
	 * Default action of the backend controller.
	 *
	 * @param string $dataType
	 * @param string $identifier
	 * @return string
	 * @skipCsrfProtection
	 */
	public function showAction() {
		$graph = $this->buildGraph();

		$this->view->assign('graph', $graph);

	}

	protected function buildGraph() {
		$rdfGraph = new Graph();

		$settings = $this->settings['datasetDescription'];
		$subject = new NamedNode($this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE)->uriFor('show'));

		$predicate = new NamedNode('rdf:type');
		$object = new NamedNode('void:DatasetDescription');
		$rdfGraph->add(new Triple($subject, $predicate, $object));

		$predicate = new NamedNode('dcterms:title');
		$object = new Literal($settings['title']);
		$rdfGraph->add(new Triple($subject, $predicate, $object));


		$mapping = array(
			'license' => 'dcterms:license',
			'norms' => 'http://vocab.org/waiver/terms/norms',
			// licenses
			'pddl' => 'http://www.opendatacommons.org/licenses/pddl/',
			'odc-by' => 'http://www.opendatacommons.org/licenses/by/',
			'odc-odbl' => 'http://www.opendatacommons.org/licenses/odbl/',
			'cc0' => 'http://creativecommons.org/publicdomain/zero/1.0/',

			// community norms
			'odc-by-sa' => 'http://www.opendatacommons.org/norms/odc-by-sa/',
		);

		foreach ($settings['datasets'] as $identifier => $datasetConfiguration) {
			$datasetSubject = new NamedNode($subject . '#' . $identifier);

			$predicate = new NamedNode('foaf:topic');
			$rdfGraph->add(new Triple($subject, $predicate, $datasetSubject));

			$predicate = new NamedNode('rdf:type');
			$object = new NamedNode('void:Dataset');
			$rdfGraph->add(new Triple($datasetSubject, $predicate, $object));

			foreach ($datasetConfiguration as $key => $value) {
				if (!isset($mapping[$key])) {
					throw new \Exception('TODO: Data set configuration ' . $key . ' not found!');
				}
				$predicate = new NamedNode($mapping[$key]);

				$object = new NamedNode(isset($mapping[$value])?$mapping[$value]:$value);
				$rdfGraph->add(new Triple($datasetSubject, $predicate, $object));
			}
			// TODO: add the other properties here
		}

		return $rdfGraph;
	}
	public function wellKnownAction() {
		$this->redirect('show', NULL, NULL, NULL, 0, 302, 'nt');
	}
}
?>