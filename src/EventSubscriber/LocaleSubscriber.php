<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter or is present as a cookie
        if ($locale = $request->attributes->get('_locale')) {
            $request->setLocale($locale);
            $event->getRequest()->attributes->set('set_locale_cookie', $locale);

        } elseif ($locale = $request->query->get('_locale')) {
            $request->setLocale($locale);
            $request->attributes->set('set_locale_cookie', $locale);

        } elseif ($locale = $request->cookies->get('_locale')) {
            $request->setLocale($locale);
            $request->attributes->set('set_locale_cookie', $locale);

        } else {
            // if no explicit locale has been set on this request, use one from the cookie
            $request->setLocale($request->cookies->get('_locale', $this->defaultLocale));
            $event->getRequest()->attributes->set('set_locale_cookie', $this->defaultLocale);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($locale = $request->attributes->get('set_locale_cookie')) {
            $response->headers->setCookie(new Cookie('_locale', $locale, (new \DateTime())->modify('+1 year')));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
            KernelEvents::RESPONSE => [['onKernelResponse', 0]],
        ];
    }
}
