<?php

namespace Drupal\showcase\Discovery;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\Json;

/**
 * The parser front matter.
 */
class ShowcaseFrontMatter {

  const REGEXP = '/\A({#---(.*?)?\R---#})/s';

  /**
   * String contains source data.
   */
  protected string $source;

  /**
   * The parse result.
   */
  protected array $result;

  /**
   * Constructors
   *
   * @param string $source
   *   String contains source data.
   */
  public function __construct(string $source) {
    $this->source = $source;
  }

  /**
   * Returns new instance.
   *
   * @param string $source
   *   String contains source data.
   */
  public static function create(string $source): static {
    return new static($source);
  }

  /**
   * Returns parse result.
   *
   * Array empty if front matter not found. Else return array contains keys:
   * - raw: The raw data include front matter selectors.
   * - front_matter_data: The front matter data for decode.
   * - data: The data after decode.
   *
   * @throws \Exception
   */
  public function parse(): array {
    if (!empty($this->result)) {
      return $this->result;
    }

    $this->result = [];

    if (preg_match(static::REGEXP, $this->source, $matches)) {
      $raw = !empty($matches[1]) ? trim($matches[1]) : '';
      $frontMatterData = !empty($matches[2]) ? trim($matches[2]) : '';
      $data = Json::decode($frontMatterData);

      if (is_null($data)) {
        throw new \Exception(sprintf('Failed parse front matter from %s', $this->source));
      }

      $this->result = [
        'raw' => $raw,
        'front_matter_data' => $frontMatterData,
        'data' => $data,
      ];
    }

    return $this->result;
  }

}
