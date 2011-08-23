<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\FluidConnector;

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
 * @aspect
 */
class TemplateViewNodeInterceptorAspect {

	/**
	 * @var \SandstormMedia\Semantic\Rdfa\FluidSyntaxTreeInterceptor
	 * @inject
	 */
	protected $rdfaInterceptor;

	/**
	 * @var \SandstormMedia\Semantic\Linkification\FluidSyntaxTreeInterceptor
	 * @inject
	 */
	protected $linkificationInterceptor;


	/**
	 * @afterreturning method(TYPO3\Fluid\View\TemplateView->buildParserConfiguration()) && setting(SandstormMedia.Semantic.rdfa.enable)
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