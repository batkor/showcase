# Showcase

This module for Drupal 10.
Provides functions for fast create pages and block
from `*.html.twig` files.

For use this module your need create or update
TWIG file.

## Create block

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

## Create page

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
| `html`         | Use this options if your template contains html tag.                                                                                |
| `requirements` | The route requirements.                                                                                                             |
| `access`       | The access to plugin. Override access to route.                                                                                     |
| `data`         | The list variables available in template or callable string.                                                                        |
| `css`          | The paths list to .css file for attach into a plugin. Not working if enabled html key.                                              |
| `jss`          | The paths list to .js file for attach into a plugin. Not working if enabled html key.                                               |


## Extra settings

### Management environment.

Your can set next options on `settings.php` file
`$settings['showcase']['env']` Variable use for
control `access` to plugin. Default support `all`
value. You can set `dev` and in plugin set `access`
key to `dev` and plugin rendered only project DEVELOPMENT environment.

### Arbitrary template directory.

After add next code on settings.php:
```php
$settings['showcase']['directories'][] = '../templates';
```
Your can create templates in as directory.

```
.
├── ...
├── templates                 # Directory inputted in settings.php
│   ├── front-page.html.twig  # Your template
│   └── ...
├── web                       # Drupal root directory
│   ├── core
│   ├── modules
│   └── ...
```

### Attach *.css and *.js files.

```
{#---
  {
    ...
    "css": {
      "/assets/showcase_example/path.css": {},
      "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css": {
        "crossorigin": "anonymous",
        "integrity": "sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
      }
    },
    "js": {
      "/assets/showcase_example/path.js": {},
      "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js": {
        "crossorigin": "anonymous",
        "integrity": "sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
      }
    }
    ...
  }
---#}
```
