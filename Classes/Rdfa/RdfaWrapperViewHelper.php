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

namespace SandstormMedia\Semantic\Rdfa;




use TYPO3\FLOW3\Annotations as FLOW3;

use \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RdfaWrapperViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'span'; // TODO: use a different one later?

	/**
	 * @var \SandstormMedia\Semantic\Core\RdfGenerator
	 * @FLOW3\Inject
	 */
	protected $rdfGenerator;

	/**
	 * @var SandstormMedia\Semantic\Core\Rdf\Environment\ProfileInterface
	 * @FLOW3\Inject
	 */
	protected $profile;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\ExternalReferenceRepository
	 * @FLOW3\Inject
	 */
	protected $metadataRepository;

	/**
	 * @var \SandstormMedia\Semantic\Core\Schema\ClassSchemaResolver
	 * @FLOW3\Inject
	 */
	protected $classSchemaResolver;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\TextAnnotationsRepository
	 * @FLOW3\Inject
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

		$rdfSubject = $this->rdfGenerator->getResourceUriForObject($object);

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