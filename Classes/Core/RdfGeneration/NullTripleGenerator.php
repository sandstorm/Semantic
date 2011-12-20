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

namespace SandstormMedia\Semantic\Core\RdfGeneration;



use TYPO3\FLOW3\Annotations as FLOW3;

use \SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Graph;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Triple;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
use \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class NullTripleGenerator implements TripleGeneratorInterface {
	public function generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, array $propertySchema, RdfNode $rdfSubject, RdfNode $rdfPredicate, Graph $graph) {
	}
}
?>