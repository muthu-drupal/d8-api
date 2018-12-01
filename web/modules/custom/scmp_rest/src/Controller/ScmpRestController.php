<?php

namespace Drupal\scmp_rest\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Controller routines for REST resources.
 */
class ScmpRestController extends ControllerBase {

  /**
   * Entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Constructs a new CustomRestController object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query factory.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * Return articles for given topic in a formatted JSON response.
   *
   * @param string $topic
   *   Term name.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The formatted JSON response.
   */
  public function getArticlesByTopic($topic) {
    // Initialize the response array.
    $response_array = [];
    $vocabulary = 'topic';
    $topic_term = taxonomy_term_load_multiple_by_name($topic, $vocabulary);
    $term_id = array_keys($topic_term);
    $node_query = $this->entityQuery->get('node')
      ->condition('type', 'article')
      ->condition('field_topic', $term_id[0])
      ->condition('status', 1)
      ->sort('changed', 'DESC')
      ->range(0, 10)
      ->execute();
    if ($node_query) {
      $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_query);
      foreach ($nodes as $node) {
        $response_array[] = $this->getNodeData($node);
      }
    }
    else {
      // Set the default response to be returned if no results can be found.
      $response_array = ['message' => 'Articles not found.'];
    }
    $response = $this->setCache($response_array, 'article_by_topic');
    return $response;
  }

  /**
   * Return article details for given node id.
   *
   * @param int $nid
   *   Node id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The formatted JSON response.
   */
  public function getArticle($nid) {
    // Initialize the response array.
    $response_array = [];
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($nid);
    if ($node) {
      $response_array = $this->getNodeData($node);
    }
    else {
      // Set the default response to be returned if no results can be found.
      $response_array = ['message' => 'Article not found.'];
    }
    $response = $this->setCache($response_array, 'get_article');
    return $response;
  }

  /**
   * Return articles based on published date.
   *
   * @param string $published_date
   *   Publication date of the article.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The formatted JSON response.
   */
  public function getArticlesByDate($published_date) {
    // Initialize the response array.
    $response_array = [];
    $date = new DrupalDateTime($published_date);
    if ($date instanceof DrupalDateTime && !$date->hasErrors()) {
      $timestamp = $date->getTimestamp();
      $end_timestamp = strtotime('+1 day', $timestamp);
      $node_query = $this->entityQuery->get('node')
        ->condition('field_publication_date', $timestamp, '>=')
        ->condition('field_publication_date', $end_timestamp, '<=')
        ->condition('type', 'article')
        ->condition('status', 1)
        ->sort('changed', 'DESC')
        ->execute();
      if ($node_query) {
        $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_query);
        foreach ($nodes as $node) {
          $response_array[] = $this->getNodeData($node);
        }
      }
      else {
        // Set the default response to be returned if no results can be found.
        $response_array = ['message' => 'Articles not found.'];
      }
    }
    else {
      $response_array = ['message' => 'Invalid date.'];
    }
    $response = $this->setCache($response_array, 'get_articles_by_date');
    return $response;
  }

  /**
   * Return articles based on published date range.
   *
   * @param string $start_date
   *   Publication date of the article.
   * @param string $end_date
   *   Publication date of the article.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The formatted JSON response.
   */
  public function getArticlesByDateRange($start_date, $end_date) {
    // Initialize the response array.
    $response_array = [];
    $start_date_obj = new DrupalDateTime($start_date);
    $end_date_obj = new DrupalDateTime($end_date);
    if (($start_date_obj instanceof DrupalDateTime && !$start_date_obj->hasErrors()) && ($end_date_obj instanceof DrupalDateTime && !$end_date_obj->hasErrors()) {
      $start_timestamp = $start_date_obj->getTimestamp();
      $end_timestamp = $end_date_obj->getTimestamp();
      $end_final_timestamp = strtotime('+1 day', $end_timestamp);
      $node_query = $this->entityQuery->get('node')
        ->condition('field_publication_date', $start_timestamp, '>=')
        ->condition('field_publication_date', $end_final_timestamp, '<=')
        ->condition('type', 'article')
        ->condition('status', 1)
        ->sort('changed', 'DESC')
        ->execute();
      if ($node_query) {
        $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_query);
        foreach ($nodes as $node) {
          $response_array[] = $this->getNodeData($node);
        }
      }
      else {
        // Set the default response to be returned if no results can be found.
        $response_array = ['message' => 'Articles not found.'];
      }
    }
    else {
      $response_array = ['message' => 'Invalid date.'];
    }
    $response = $this->setCache($response_array, 'get_articles_by_date');
    return $response;
  }

  /**
   * Helper function to fetch node data.
   *
   * @param object $node
   *   Node id.
   *
   * @return array
   *   Node information.
   */
  private function getNodeData($node) {
    $image_path = '';
    $related_node_ids = [];
    $topic_node_ids = [];
    if (!empty($node->field_image->entity)) {
      $file = $node->field_image->entity;
      $image_path = file_create_url($file->getFileUri());
    }
    foreach ($node->field_related_article as $related_article) {
      $related_node_ids[] = $related_article->target_id;
    }
    foreach ($node->field_topic as $topic) {
      $topic_node_ids[] = $topic->target_id;
    }
    $publication_date = !empty($node->field_publication_date->value) ? date('d-m-Y H:i:s', $node->field_publication_date->value) : '';
    return [
      'nid' => (int) $node->nid->value,
      'title' => $node->title->value,
      'body' => $node->body->value,
      'image_path' => $image_path,
      'publication_date' => $publication_date,
      'related_article_nid' => implode(",", $related_node_ids),
      'topic_tid' => implode(",", $topic_node_ids),
    ];
  }

  /**
   * Helper function to set cache and support JSON data.
   *
   * @param array $response_array
   *   Response data.
   * @param string $key
   *   Unique key to set cache.
   *
   * @return object
   *   Node information.
   */
  private function setCache(array $response_array, $key) {
    // Add the cache tag so the endpoint results will update when nodes are
    // updated.
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->setCacheTags([$key]);
    // Create the JSON response object and add the cache metadata.
    $response = new CacheableJsonResponse($response_array);
    return $response->addCacheableDependency($cache_metadata);
  }

}
