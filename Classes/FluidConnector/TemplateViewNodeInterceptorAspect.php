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

namespace SandstormMedia\Semantic\FluidConnector;




use TYPO3\FLOW3\Annotations as FLOW3;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Aspect
 */
class TemplateViewNodeInterceptorAspect {

	/**
	 * @var \SandstormMedia\Semantic\Rdfa\FluidSyntaxTreeInterceptor
	 * @FLOW3\Inject
	 */
	protected $rdfaInterceptor;

	/**
	 * @var \SandstormMedia\Semantic\Linkification\FluidSyntaxTreeInterceptor
	 * @FLOW3\Inject
	 */
	protected $linkificationInterceptor;


	/**
	 * @FLOW3\AfterReturning("method(TYPO3\Fluid\View\TemplateView->buildParserConfiguration()) && setting(SandstormMedia.Semantic.rdfa.enable)")
	 * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint The current join point
	 * @return void
	 */
	public function addTemplateViewInterceptor(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$parserConfiguration = $joinPoint->getResult();
		$parserConfiguration->addInterceptor($this->rdfaInterceptor);
		$parserConfiguration->addInterceptor($this->linkificationInterceptor);
	}
}
?>