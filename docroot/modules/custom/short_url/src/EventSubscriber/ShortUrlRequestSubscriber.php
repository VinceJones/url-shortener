<?php

namespace Drupal\short_url\EventSubscriber;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\short_url\Entity\ShortUrl;
use Drupal\short_url\Service\ShortUrlValidationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * ShortUrl subscriber for controller requests.
 */
class ShortUrlRequestSubscriber implements EventSubscriberInterface {

  /**
   * ShortUrlValidationService.
   *
   * @var \Drupal\short_url\Service\ShortUrlValidationService
   */
  public $validation_service;

  /**
   * ShortUrlRequestSubscriber constructor.
   *
   * @param \Drupal\short_url\Service\ShortUrlValidationService $short_url_validation_service
   */
  public function __construct(ShortUrlValidationService $short_url_validation_service) {
    $this->validation_service = $short_url_validation_service;
  }

  /**
   * Handles the redirect if any found.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onKernelRequestCheckShortUrl(GetResponseEvent $event) {

    /** @var \Symfony\Component\HttpFoundation\Request $request */
    $request = clone $event->getRequest();

    if (empty($uri = $request->getRequestUri())) {
      return;
    }

    $path = ltrim($uri, '/');

    if (empty($path)) {
      return;
    }

    if (empty($results = $this->validation_service->getShortUrlEntityByPath($path))) {
      return;
    }

    $short_url_entity = ShortUrl::load(current($results));

    $hit_count = $short_url_entity->get('hit_count')->value;
    $hit_count = (int) $hit_count + 1;
    $short_url_entity->set('hit_count', $hit_count);
    $short_url_entity->save();

    if (empty($redirect_path = $short_url_entity->get('original_url')->value)) {
      return;
    }

    $redirect = new TrustedRedirectResponse($redirect_path);
    $event->setResponse($redirect);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    // This needs to run before RouterListener::onKernelRequest(), which has
    // a priority of 32. Otherwise, that aborts the request if no matching
    // route is found.
    $events[KernelEvents::REQUEST][] = array('onKernelRequestCheckShortUrl', 33);
    return $events;
  }

}
