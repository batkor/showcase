<?php

declare(strict_types=1);

namespace Drupal\showcase\Helper;

/**
 * The helper object for generate pagination.
 */
final class PagerGenerator {

  private int $first;

  private int $last;

  private array $pages;

  private int $begin = 0;

  public function __construct(
    private readonly int $total,
    private readonly int $itemsPerPage,
    private readonly int $current,
  ) {}

  public function setBegin(int $begin): void {
    $this->begin = $begin;
  }

  public function next(): ?int {
    $page = $this->current + 1;

    return $page <= $this->total ? $page : NULL;
  }

  public function prev(): ?int {
    $page = $this->current - 1;

    return $page >= $this->begin ? $page : NULL;
  }

  public function first(): int {
    if (empty($this->first)) {
      $this->pages();
    }

    return $this->first;
  }

  public function last(): int {
    if (empty($this->last)) {
      $this->pages();
    }

    return $this->last;
  }

  public function pages(): array {
    if (!empty($this->pages)) {
      return $this->pages;
    }

    $middle = (int) \ceil($this->itemsPerPage / 2);
    $this->first = \max($this->begin, $this->current - $middle + 1);
    $this->last = $this->itemsPerPage + $this->first - 1;

    if ($this->last >= $this->total) {
      $this->first = \max($this->begin, $this->total - $this->itemsPerPage + 1);
      $this->last = \min($this->total, $this->itemsPerPage + $this->first - 1);
    }

    $this->pages = \range($this->first, $this->last);

    return $this->pages;
  }

}
