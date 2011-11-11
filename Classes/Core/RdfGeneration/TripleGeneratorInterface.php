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



use \SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Graph;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Triple;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface TripleGeneratorInterface {
	public function generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, array $propertySchema, RdfNode $rdfSubject, RdfNode $rdfPredicate, Graph $graph);
}
?>