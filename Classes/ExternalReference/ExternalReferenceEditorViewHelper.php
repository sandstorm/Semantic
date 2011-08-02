<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\ExternalReference;

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

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ExternalReferenceEditorViewHelper extends \TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper {

	protected $settings;

	/**
	 * @var SandstormMedia\Semantic\ExternalReference\Controller\ExternalReferenceEditorController
	 * @inject
	 */
	protected $controller;

	/**
	 * @var SandstormMedia\Semantic\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $externalReferenceRepository;

	protected $enabled = FALSE;

	protected $resolverConfiguration = array();
	/**
	 * @param array $settings
	 */
	public function injectSettings($settings) {
		$this->settings = $settings;
	}

	public function initialize() {
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
			$formObjectName = get_class($formObject);
			if (isset($this->settings['PropertyMapping'][$formObjectName]['properties'][$this->arguments['property']]['externalResolver'])) {
				$externalResolverConfiguration = $this->settings['PropertyMapping'][$formObjectName]['properties'][$this->arguments['property']]['externalResolver'];
				$this->ajaxWidget = TRUE;
				$this->enabled = TRUE;
				$this->resolverConfiguration = $externalResolverConfiguration;
			}
		}
	}

	public function getWidgetConfiguration() {
		$metadata = NULL;
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
			$metadata = $this->externalReferenceRepository->findOneByObjectAndPropertyName($formObject, $this->arguments['property']);
		}
		return array('resolver' => $this->resolverConfiguration, 'metadata' => $metadata);
	}

	/**
	 * @param string $property
	 * @return string
	 */
	public function render($property) {
		if (!$this->enabled) return '';

		$response = $this->initiateSubRequest();
		return $response->getContent();
	}
}
?>