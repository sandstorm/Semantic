<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Rdfa;

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

use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RdfaWrapperViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'span'; // TODO: use a different one later?

	/**
	 * @var \SandstormMedia\Semantic\Domain\Service\ResourceUriService
	 * @inject
	 */
	protected $resourceUriService;

	/**
	 * @var SandstormMedia\Semantic\Domain\Model\Rdf\Environment\ProfileInterface
	 * @inject
	 */
	protected $profile;

	/**
	 * @var SandstormMedia\Semantic\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $metadataRepository;

	/**
	 * @var \SandstormMedia\Semantic\Schema\ClassSchemaResolver
	 * @inject
	 */
	protected $classSchemaResolver;

	/**
	 * @var SandstormMedia\Semantic\Domain\Repository\TextAnnotationsRepository
	 * @inject
	 */
	protected $textAnnotationsRepository;

	/**
	 * @param string $propertyPath
	 * @return string
	 */
	public function render($propertyPath) {
		$propertyPathParts = explode('.', $propertyPath);

		$propertyName = array_pop($propertyPathParts);
		$objectPath = implode('.', $propertyPathParts);
		$innerContent = $this->renderChildren();

		if (strlen($objectPath) == 0) return $innerContent;

		$object = \TYPO3\FLOW3\Reflection\ObjectAccess::getPropertyPath($this->templateVariableContainer, $objectPath);
		if (!is_object($object)) {
			// Could be that this is a simple value, and no object.
			return $innerContent;
		}

		$rdfSubject = $this->resourceUriService->buildResourceUri($object);

		$rdfPredicate = NULL;
		$propertySchema = $this->classSchemaResolver->getPropertySchema(get_class($object), $propertyName);

		$annotatedText = $this->textAnnotationsRepository->findOneByObjectAndPropertyName($object, $propertyName);
		if ($annotatedText !== NULL) {
			$innerContent = $annotatedText->getStringWithAnnotations($innerContent);
		}

		if (isset($propertySchema['rdfType'])) {
			$rdfPredicate = new NamedNode($propertySchema['rdfType']);
		}

		$possibleRdfExternalReference = $this->metadataRepository->findOneByObjectAndPropertyName($object, $propertyName);
		if ($possibleRdfExternalReference) {
			$value = $possibleRdfExternalReference->getValue();
			$this->tag->addAttribute('content', $value);
		}

		if ($rdfPredicate !== NULL && !is_object($innerContent)) { // TODO: hack to prevent conversion of f.e. DateTime
			$this->tag->setContent($innerContent);
			$this->tag->addAttribute('about', $rdfSubject);

			$curie = $this->profile->getPrefixes()->shrink((string)$rdfPredicate);
			list($prefix, ) = explode(':', $curie, 2);

			$this->tag->addAttribute('xmlns:' . $prefix, $this->profile->getPrefixes()->get($prefix));
			$this->tag->addAttribute('property', $curie);
			// TODO: handle the case that f.e. DateTime is shown, with "content"

			return $this->tag->render();
		} else {
			return $innerContent;
		}
	}
}
?>