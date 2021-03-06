<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for for the session based security context holder
 *
 * @version $Id: ContextHolderSessionTest.php 3643 2010-01-15 14:38:07Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ContextHolderSessionTest extends \F3\Testing\BaseTestCase {

	/**
	 * Set up.
	 *
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setUp() {
		$mockObjectConfiguration = $this->getMock('F3\FLOW3\Object\Configuration\Configuration', array(), array(), '', FALSE);
		$mockObjectBuilder = $this->getMock('F3\FLOW3\Object\ObjectBuilder', array(), array(), '', FALSE);

		$this->mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManager', array('getObject', 'getObjectConfiguration', 'reinjectDependencies'), array(), '', FALSE);
		$this->mockObjectManager->expects($this->any())->method('getObjectConfiguration')->will($this->returnValue($mockObjectConfiguration));
		$this->mockObjectManager->expects($this->any())->method('getObject')->will($this->returnValue($mockObjectBuilder));
	}

	/**
	 * @test
	 * @category unit
	 * @expectedException \F3\FLOW3\Security\Exception\NoContextAvailableException
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getContextThrowsAnExceptionIfThereIsNoContextFound() {
		$securityContextHolder = new \F3\FLOW3\Security\ContextHolderSession();
		$securityContextHolder->getContext();
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function currentRequestIsSetInTheSecurityContext() {
		$mockContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockAuthenticationManager = $this->getMock('F3\FLOW3\Security\Authentication\AuthenticationManagerInterface');

		$mockContext->expects($this->once())->method('setRequest')->with($mockRequest);

		$mockAuthenticationManager->expects($this->any())->method('getTokens')->will($this->returnValue(array()));

		$securityContextHolder = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\ContextHolderSession'), array('dummy'));
		$securityContextHolder->injectObjectManager($this->mockObjectManager);
		$securityContextHolder->injectObjectFactory($this->objectFactory);
		$securityContextHolder->injectAuthenticationManager($mockAuthenticationManager);
		$securityContextHolder->_set('context', $mockContext);

		$securityContextHolder->initializeContext($mockRequest);
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function securityContextCallsTheAuthenticationManagerToSetItsTokens() {
		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockAuthenticationManager = $this->getMock('F3\FLOW3\Security\Authentication\AuthenticationManagerInterface');

		$mockAuthenticationManager->expects($this->once())->method('getTokens')->will($this->returnValue(array()));

		$securityContextHolder = new \F3\FLOW3\Security\ContextHolderSession();
		$securityContextHolder->injectObjectManager($this->mockObjectManager);
		$securityContextHolder->injectObjectFactory($this->objectFactory);
		$securityContextHolder->injectAuthenticationManager($mockAuthenticationManager);

		$securityContextHolder->initializeContext($mockRequest);
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function tokenFromAnAuthenticationManagerIsReplacedIfThereIsOneOfTheSameTypeInTheSession() {
		$token1ClassName = uniqid('token1');
		$token2ClassName = uniqid('token2');
		$token3ClassName = uniqid('token3');

		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockAuthenticationManager = $this->getMock('F3\FLOW3\Security\Authentication\AuthenticationManagerInterface');

		$token1 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $token1ClassName);
		$token1Clone = new $token1ClassName();
		$token2 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $token2ClassName);
		$token2Clone = new $token2ClassName();
		$token3 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $token3ClassName);

		$tokensFromTheManager = array($token1, $token2, $token3);
		$tokensFromTheSession = array($token1Clone, $token2Clone);
		$mergedTokens = array($token1Clone, $token2Clone, $token3);

		$mockAuthenticationManager->expects($this->once())->method('getTokens')->will($this->returnValue($tokensFromTheManager));
		$mockContext->expects($this->once())->method('getAuthenticationTokens')->will($this->returnValue($tokensFromTheSession));
		$mockContext->expects($this->once())->method('setAuthenticationTokens')->with($this->identicalTo($mergedTokens));

		$securityContextHolder = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\ContextHolderSession'), array('dummy'));
		$securityContextHolder->injectObjectManager($this->mockObjectManager);
		$securityContextHolder->injectObjectFactory($this->objectFactory);
		$securityContextHolder->injectAuthenticationManager($mockAuthenticationManager);
		$securityContextHolder->_set('context', $mockContext);

		$securityContextHolder->initializeContext($mockRequest);
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function initializeContextCallsUpdateCredentialsOnAllTokens() {
		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockAuthenticationManager = $this->getMock('F3\FLOW3\Security\Authentication\AuthenticationManagerInterface');

		$mockToken1 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface');
		$mockToken2 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface');
		$mockToken3 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface');

		$mockToken1->expects($this->once())->method('updateCredentials');
		$mockToken2->expects($this->once())->method('updateCredentials');
		$mockToken3->expects($this->once())->method('updateCredentials');
		$mockContext->expects($this->once())->method('getAuthenticationTokens')->will($this->returnValue(array()));
		$mockAuthenticationManager->expects($this->once())->method('getTokens')->will($this->returnValue(array($mockToken1, $mockToken2, $mockToken3)));

		$securityContextHolder = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\ContextHolderSession'), array('dummy'));
		$securityContextHolder->injectObjectManager($this->mockObjectManager);
		$securityContextHolder->injectObjectFactory($this->objectFactory);
		$securityContextHolder->injectAuthenticationManager($mockAuthenticationManager);
		$securityContextHolder->_set('context', $mockContext);

		$securityContextHolder->initializeContext($mockRequest);
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function theSecurityContextHolderSetsAReferenceToTheSecurityContextInTheAuthenticationManager() {
		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockAuthenticationManager = $this->getMock('F3\FLOW3\Security\Authentication\AuthenticationManagerInterface');

		$mockContext->expects($this->once())->method('getAuthenticationTokens')->will($this->returnValue(array()));
		$mockAuthenticationManager->expects($this->once())->method('getTokens')->will($this->returnValue(array()));
		$mockAuthenticationManager->expects($this->once())->method('setSecurityContext')->with($mockContext);

		$securityContextHolder = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\ContextHolderSession'), array('dummy'));
		$securityContextHolder->injectObjectManager($this->mockObjectManager);
		$securityContextHolder->injectObjectFactory($this->objectFactory);
		$securityContextHolder->injectAuthenticationManager($mockAuthenticationManager);
		$securityContextHolder->_set('context', $mockContext);

		$securityContextHolder->initializeContext($mockRequest);
	}

	/**
	 * @test
	 * @category unit
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function filterInactiveTokensWorks() {
		$matchingRequestPatternClassName = uniqid('matchingRequestPattern');
		$notMatchingRequestPatternClassName = uniqid('notMatchingRequestPattern');
		$abstainingRequestPatternClassName = uniqid('abstainingRequestPattern');
		$authenticationToken1ClassName = uniqid('authenticationToken1');
		$authenticationToken2ClassName = uniqid('authenticationToken2');
		$authenticationToken3ClassName = uniqid('authenticationToken3');
		$authenticationToken4ClassName = uniqid('authenticationToken4');
		$authenticationToken5ClassName = uniqid('authenticationToken5');
		$authenticationToken6ClassName = uniqid('authenticationToken6');

		$request = $this->getMock('F3\FLOW3\MVC\RequestInterface');

		$matchingRequestPattern = $this->getMock('F3\FLOW3\Security\RequestPatternInterface', array(), array(), $matchingRequestPatternClassName);
		$matchingRequestPattern->expects($this->any())->method('canMatch')->will($this->returnValue(TRUE));
		$matchingRequestPattern->expects($this->any())->method('matchRequest')->will($this->returnValue(TRUE));

		$notMatchingRequestPattern = $this->getMock('F3\FLOW3\Security\RequestPatternInterface', array(), array(), $notMatchingRequestPatternClassName);
		$notMatchingRequestPattern->expects($this->any())->method('canMatch')->will($this->returnValue(TRUE));
		$notMatchingRequestPattern->expects($this->any())->method('matchRequest')->will($this->returnValue(FALSE));

		$abstainingRequestPattern = $this->getMock('F3\FLOW3\Security\RequestPatternInterface', array(), array(), $abstainingRequestPatternClassName);
		$abstainingRequestPattern->expects($this->any())->method('canMatch')->will($this->returnValue(FALSE));
		$abstainingRequestPattern->expects($this->never())->method('matchRequest');

		$token1 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $authenticationToken1ClassName);
		$token1->expects($this->once())->method('hasRequestPatterns')->will($this->returnValue(TRUE));
		$token1->expects($this->once())->method('getRequestPatterns')->will($this->returnValue(array($matchingRequestPattern)));

		$token2 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $authenticationToken2ClassName);
		$token2->expects($this->once())->method('hasRequestPatterns')->will($this->returnValue(FALSE));
		$token2->expects($this->never())->method('getRequestPatterns');

		$token3 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $authenticationToken3ClassName);
		$token3->expects($this->once())->method('hasRequestPatterns')->will($this->returnValue(TRUE));
		$token3->expects($this->once())->method('getRequestPatterns')->will($this->returnValue(array($notMatchingRequestPattern)));

		$token4 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $authenticationToken4ClassName);
		$token4->expects($this->once())->method('hasRequestPatterns')->will($this->returnValue(TRUE));
		$token4->expects($this->once())->method('getRequestPatterns')->will($this->returnValue(array($abstainingRequestPattern)));

		$token5 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $authenticationToken5ClassName);
		$token5->expects($this->once())->method('hasRequestPatterns')->will($this->returnValue(TRUE));
		$token5->expects($this->once())->method('getRequestPatterns')->will($this->returnValue(array($abstainingRequestPattern, $notMatchingRequestPattern, $matchingRequestPattern)));

		$token6 = $this->getMock('F3\FLOW3\Security\Authentication\TokenInterface', array(), array(), $authenticationToken6ClassName);
		$token6->expects($this->once())->method('hasRequestPatterns')->will($this->returnValue(TRUE));
		$token6->expects($this->once())->method('getRequestPatterns')->will($this->returnValue(array($abstainingRequestPattern, $matchingRequestPattern, $matchingRequestPattern)));

		$securityContextHolder = $this->getMock($this->buildAccessibleProxy('F3\FLOW3\Security\ContextHolderSession'), array('dummy'), array(), '', FALSE);
		$resultTokens = $securityContextHolder->_call('filterInactiveTokens', array($token1, $token2, $token3, $token4, $token5, $token6), $request);

		$this->assertEquals(array($token1, $token2, $token4, $token6), $resultTokens);
	}
}
?>