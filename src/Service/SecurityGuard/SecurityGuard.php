<?php
namespace PHPCrystal\PHPCrystal\Service\SecurityGuard;

use PHPCrystal\PHPCrystal\Component\Service\AbstractContractor;
use PHPCrystal\PHPCrystal\Service\Event as Event;
use PHPCrystal\PHPCrystal\Contract as Contract;
use PHPCrystal\PHPCrystal\Component\Exception\Security\AuthRequired;
use PHPCrystal\PHPCrystal\Component\Exception\Security\CsrfTokenValidation;

class SecurityGuard extends AbstractContractor
{
	private $app;
	private $httpRequest;
	private $httpResponse;
	private $config;
	private $csrfTokenRequired;

	private $sessService;
	private $authService;

	/**
	 * @return void
	 */
	private function addEventListeners()
	{
		// set CSRF token once user is loged in
		$this->app->addEventListener(Event\Type\User\Login, function($event) {
			if ( ! ($event->isSuccess() && $this->csrfTokenRequired)) {
				return;
			}

			$this->httpResponse->setCookie(
				$this->config->get('csrf-token-cookie-name'),
				$this->generateCsrfToken(),
				$this->sessService->getCookieLifetime()
			);
		});
	}

	/**
	 * @return void
	 */
	private function checkCsrfToken()
	{
		$cookieName = $this->config->get('csrf-token-cookie-name');
		$headerFieldName = $this->config->get('csrf-token-header-field-name');

		$cookieValue = $this->httpRequest->getCookie($cookieName);
		if (empty($cookieValue)) {
			return;
		}

		$headerValue = $this->httpRequest->getHeader($headerFieldName);
		if ($cookieValue != $headerValue) {
			CsrfTokenValidation::create("CSRF token validation has been failed")
				->_throw();
		}
	}

	/**
	 * SafeGuard service constructor
	 * 
	 * @api
	 */
	public function __construct(Contract\Session $session, Contract\Auth $auth)
	{
		parent::__construct();
		$this->sessService = $session;
		$this->authService = $auth;
	}

	/**
	 * @return void
	 */
	public function init()
	{
		if ($this->isInitialized) {
			return;
		}
		
		$this->app = $this->getApplication();
		$this->request = $this->app->getRequest();
		$this->config = $this->app->getContext()->pluck('phpcrystal.security_guard');
		$this->addEventListeners();

		$this->isInitialized = true;
	}

	/**
	 * @return void
	 */
	public function process(Event\Type\System\SecurityPolicyApplication $secAppEvent)
	{
		// check whether user is authenticated
		if ($secAppEvent->isAuthRequired() && ! $this->authService->isAuthenticated()) {
			AuthRequired::create('Authentication required')
				->_throw();
		}

		// check X-Csrf-Token header field if necessary
		if ($secAppEvent->isCsrfTokenRequired()) {
			$this->csrfTokenRequired = true;
			$this->checkCsrfToken();
		}
	}

	/**
	 * @return
	 */
	public function generateCsrfToken()
	{
		return md5($this->sessService->getId() . $this->config('csrf-token-secret-key'));
	}
}
