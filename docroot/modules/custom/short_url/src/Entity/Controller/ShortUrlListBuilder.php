<?php

namespace Drupal\short_url\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\GeneratedLink;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for short_url entity.
 *
 * @ingroup short_url
 */
class ShortUrlListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new ShortUrlListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the short_url list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {

    $header['short_url'] = $this->t('Shortened URL');
    $header['original_url'] = $this->t('Original URL');
    $header['hit_count'] = $this->t('Hit Count');

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    $options = [
      'absolute' => TRUE,
    ];

    $site_url = Url::fromRoute('<front>', [], $options)->toString();

    /** @var \Drupal\short_url\Entity\ShortUrl $entity */
    $short_url = $site_url . $entity->short_url->value;
    $url_link = "<a href='$short_url'>$short_url</a>";

    $url = new GeneratedLink();
    $url->setGeneratedLink($url_link);

    /* @var $entity \Drupal\short_url\Entity\ShortUrl */
    $row['short_url'] = $url;
    $row['original_url'] = $entity->get('original_url')->value;
    $row['hit_count'] = $entity->get('hit_count')->value;

    return $row;
  }

}
