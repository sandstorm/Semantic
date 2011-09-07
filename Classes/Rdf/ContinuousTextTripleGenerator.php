<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Rdf;

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

use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\RdfNode;
use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\Graph;
use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\Triple;
use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode;
use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\Literal;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class ContinuousTextTripleGenerator extends BasicTripleGenerator {

	/**
	 * @var SandstormMedia\Semantic\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var SandstormMedia\Semantic\Domain\Repository\TextAnnotationsRepository
	 * @inject
	 */
	protected $textAnnotationsRepository;

	public function generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, array $propertySchema, RdfNode $rdfSubject, RdfNode $rdfPredicate, Graph $graph) {
		parent::generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, $propertySchema, $rdfSubject, $rdfPredicate, $graph);

		$possibleTextAnnotations = $this->textAnnotationsRepository->findOneByUuidAndPropertyName($subjectDomainModelIdentifier, $propertyName);
		if ($possibleTextAnnotations) {
			$annotationType = new NamedNode('annot:Annotation');

			foreach ($possibleTextAnnotations->getAnnotations() as $annotation) {
				$annotationInstance = new \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\BlankNode();
				$siocAbout = new NamedNode('sioc:about');
				$rdfObject = new NamedNode($annotation['uri']);
				$graph->add(new Triple($rdfSubject, $siocAbout, $rdfObject));

				$graph->add(new Triple($rdfSubject, new NamedNode('annot:annotatedBy'), $annotationInstance));
				$graph->add(new Triple($annotationInstance, new NamedNode('rdf:type'), $annotationType));
				$graph->add(new Triple($annotationInstance, new NamedNode('annot:predicate'), $rdfPredicate));
				//$graph->add(new Triple($annotationInstance, new NamedNode('annot:annotatedText'), $rdfPredicate)); // TODO
				$graph->add(new Triple($annotationInstance, new NamedNode('annot:offset'), new Literal($annotation['offset'])));
				$graph->add(new Triple($annotationInstance, new NamedNode('annot:length'), new Literal($annotation['length'])));
				$graph->add(new Triple($annotationInstance, new NamedNode('annot:about'), $rdfObject));
			}
		}
	}
}
?>