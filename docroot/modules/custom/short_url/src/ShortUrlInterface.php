<?php

namespace Drupal\short_url;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a ShortUrl entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup short_url
 */
interface ShortUrlInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
