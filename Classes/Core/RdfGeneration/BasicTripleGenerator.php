<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Core\RdfGeneration;

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

use \SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Graph;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Triple;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class BasicTripleGenerator implements TripleGeneratorInterface {
	public function generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, array $propertySchema, RdfNode $rdfSubject, RdfNode $rdfPredicate, Graph $graph) {
			$rdfObject = new Literal($propertyValue);
			$graph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
	}
}
?>