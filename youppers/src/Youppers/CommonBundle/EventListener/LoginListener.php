<?php
namespace Youppers\CommonBundle\EventListener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class LoginListener
{
	/**
	 * @var string
	 */
	protected $locale;

	/**
	 * Router
	 *
	 * @var RouterInterface
	 */
	protected $router;

	/**
	 * @var SecurityContextInterface
	 */
	protected $securityContext;

	/**
	 * @param SecurityContext $securityContext
	 * @param Router $router The router
	 */
	public function __construct(SecurityContextInterface $securityContext, RouterInterface $router)
	{
		$this->securityContext = $securityContext;
		$this->router = $router;
	}

	public function handle(AuthenticationEvent $event)
	{
		$user = $event->getAuthenticationToken()->getUser();
		if ($user instanceof UserInterface) {
			$this->locale = $user->getLocale();
		}
	}

	public function onKernelResponse(FilterResponseEvent $event)
	{
		if (null !== $this->locale) {
			$request = $event->getRequest();
			$request->getSession()->set('_locale', $this->locale);
		}
	}
}