# Showcase

This module for Drupal 10.
Provides functions for fast create pages and block
from `*.html.twig` files.

For use this module your need create or update
TWIG file.

## For create block

1. Your need add on twig file, next lines:
```
{#---
  {
    "label": "Example showcase block",
  }
---#}
```
2. Cache rebuild
3. Add block on "Block layout" page (`/admin/structure/block`)

## For create page

1. Your need add on twig file, next lines:
```twig
{#---
  {
    "path": "/front_page",
  }
---#}
```
2. Cache rebuild
3. Goto the path URL (`/front_page`)


## Provides data supported on TWIG file.
You can use `data` key in FrontMatter.
```
{#---
  {
    "path": "/front_page",
    "data": {
      "text": "showcase_example path"
    }
  }
---#}

<div class="text">{{ data.text }}</div>
```

Key `data` support callable strings

```
{#---
  {
    "path": "/front_page",
    "data": "\\Drupal\\showcase_example\\Showcase\\ShowcaseExample"
  }
---#}

<div>
  {{ data.text }}
</div>
```

For more information, see `showcase_example` module.

## All settings

| Key            | Description                                                                                                                         |
|----------------|-------------------------------------------------------------------------------------------------------------------------------------|
| `label`        | Optional plugin title.                                                                                                              |
| `path`         | The route path. Creating new route if path not registered or override already exist. <br/>If path NULL to will create block plugin. |
| `requirements` | The route requirements.                                                                                                             |
| `access`       | The access to plugin. Override access to route.                                                                                     |
| `data`         | The list variables available in template or callable string.                                                                        |

