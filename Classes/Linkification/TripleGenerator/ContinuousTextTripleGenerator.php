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

namespace SandstormMedia\Semantic\Linkification\TripleGenerator;




use TYPO3\FLOW3\Annotations as FLOW3;

use \SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Graph;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Triple;
use \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class ContinuousTextTripleGenerator extends \SandstormMedia\Semantic\Core\RdfGeneration\BasicTripleGenerator {

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\ExternalReferenceRepository
	 * @FLOW3\Inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\TextAnnotationsRepository
	 * @FLOW3\Inject
	 */
	protected $textAnnotationsRepository;

	public function generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, array $propertySchema, RdfNode $rdfSubject, RdfNode $rdfPredicate, Graph $graph) {
		parent::generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, $propertySchema, $rdfSubject, $rdfPredicate, $graph);

		$possibleTextAnnotations = $this->textAnnotationsRepository->findOneByUuidAndPropertyName($subjectDomainModelIdentifier, $propertyName);
		if ($possibleTextAnnotations) {
			$annotationType = new NamedNode('annot:Annotation');

			if ($possibleTextAnnotations->getAnnotations()) {
				foreach ($possibleTextAnnotations->getAnnotations() as $annotation) {
					$annotationInstance = new \SandstormMedia\Semantic\Core\Rdf\Concept\BlankNode();
					$siocAbout = new NamedNode('sioc:about');
					$rdfObject = new NamedNode($annotation['uri']);
					$graph->add(new Triple($rdfSubject, $siocAbout, $rdfObject));

					$graph->add(new Triple($rdfSubject, new NamedNode('annot:annotatedBy'), $annotationInstance));
					$graph->add(new Triple($annotationInstance, new NamedNode('rdf:type'), $annotationType));
					$graph->add(new Triple($annotationInstance, new NamedNode('annot:predicate'), $rdfPredicate));
					//$graph->add(new Triple($annotationInstance, new NamedNode('annot:annotatedText'), $rdfPredicate)); // TODO

					$graph->add(new Triple($annotationInstance, new NamedNode('annot:offset'), new Literal($annotation['offset'], NULL, new NamedNode('xsd:integer'))));
					$graph->add(new Triple($annotationInstance, new NamedNode('annot:length'), new Literal($annotation['length'], NULL, new NamedNode('xsd:integer'))));
					$graph->add(new Triple($annotationInstance, new NamedNode('annot:about'), $rdfObject));
				}
			}
		}
	}
}
?>