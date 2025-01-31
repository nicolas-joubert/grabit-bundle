# Documentation

## Installation

Open a command console, enter your project directory and install it using composer:

```bash
composer require nicolas-joubert/grabit-bundle
```

Remember to add the following line to `config/bundles.php` (not required if Symfony Flex is used)

```php
// config/bundles.php

return [
    // ...
    NicolasJoubert\GrabitBundle\GrabitBundle::class => ['all' => true],
];
```

## Configuration

### Doctrine ORM Configuration

Add these in the config mapping definition (or enable [auto_mapping](https://symfony.com/doc/current/reference/configuration/doctrine.html#mapping-configuration)):

```php
# config/packages/doctrine.yaml

doctrine:
    orm:
        mappings:
            GrabitBundle: ~
```

And then create the corresponding entities:

```php
// src/Entity/ExtractedData.php

use Doctrine\ORM\Mapping as ORM;
use NicolasJoubert\GrabitBundle\Entity\ExtractedData as BaseExtractedData;

#[ORM\Entity]
#[ORM\Table(name: 'grabit_extracted_data')]
class ExtractedData extends BaseExtractedData {}
```

```php
// src/Entity/Source.php

use Doctrine\ORM\Mapping as ORM;
use NicolasJoubert\GrabitBundle\Entity\Source as BaseSource;

#[ORM\Entity]
#[ORM\Table(name: 'grabit_source')]
class Source extends BaseSource {}
```

```php
// src/Entity/Template.php

use Doctrine\ORM\Mapping as ORM;
use NicolasJoubert\GrabitBundle\Entity\Template as BaseTemplate;

#[ORM\Entity]
#[ORM\Table(name: 'grabit_template')]
class Template extends BaseTemplate {}
```

The only thing left is to update your schema:

```bash
bin/console doctrine:schema:update --force
```

## Full configuration

```yaml
# config/packages/grabit.yaml

grabit:
  class:
    # Models
    extracted_data: App\Entity\ExtractedData
    source: App\Entity\Source
    template: App\Entity\Template
    # Dtos
    grabbed: NicolasJoubert\GrabitBundle\Dto\Grabbed
  proxy_urls:
    flaresolverr: ''
    squid: ''
  templates:
    # @see config/default_templates.yaml
```

## Basic Usage

First, create a Source on a existing Template:

```sql
INSERT INTO app.grabit_source (label, urls, template)
VALUES ('my_source_for_symfony_blog', 'https://feeds.feedburner.com/symfony/blog', 'symfony_blog');
```

Then, run the command to grab the source:

```bash
bin/console grabit:grab
```

So, data will be stored in grabit_extracted_data table using Grabbed Dto.

## Advanced Usage

- [Headers](advanced/headers.md)
- [Templates](advanced/templates.md)
- [Proxies](advanced/proxies.md)
- [Errors](advanced/errors.md)

## Additional bridges

- [Front](https://github.com/nicolas-joubert/grabit-front-bundle)
- [SonataAdmin](https://github.com/nicolas-joubert/grabit-sonata-admin-bundle)

