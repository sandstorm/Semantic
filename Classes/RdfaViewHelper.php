<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic;

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

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 2 or later
 */
class RdfaViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'span'; // TODO: use a different one later?

	protected $settings;

	/**
	 * @var \F3\Semantic\Domain\Service\ResourceUriService
	 * @inject
	 */
	protected $resourceUriService;

	/**
	 * @var F3\Semantic\Domain\Model\Rdf\Environment\ProfileInterface
	 * @inject
	 */
	protected $profile;

	/**
	 * @var F3\Semantic\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $metadataRepository;

	/**
	 * @param array $settings
	 */
	public function injectSettings($settings) {
		$this->settings = $settings;
	}

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

		$object = \F3\FLOW3\Reflection\ObjectAccess::getPropertyPath($this->templateVariableContainer, $objectPath);
		if (!is_object($object)) {
			// Could be that this is a simple value, and no object.
			return $innerContent;
		}

		$rdfSubject = $this->resourceUriService->buildResourceUri($object, $this->controllerContext->getUriBuilder());

		$rdfPredicate = NULL;
		$rdfSchema = isset($this->settings['PropertyMapping'][get_class($object)]) ? $this->settings['PropertyMapping'][get_class($object)] : array();
		if (isset($rdfSchema['properties'][$propertyName]['type'])) {  // TODO handle external references
			$rdfPredicate = new Domain\Model\Rdf\Concept\NamedNode($rdfSchema['properties'][$propertyName]['type']);
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