PHP to Typescript Converter Bundle
==================================

This bundle provides functionalty to genearte Typescript Interfaces from PHP-Class files (e.g. from an API View-Layer).

Installation
------------

It is installed into a Symfony-Project via composer:
```bash
$ composer require --dev braune-digital/php-to-typescript-converter
```


Usage
-------

Once required, the Generator-Command can be called via the symfony console:

```bash
$ bin/console bd:php-to-typescript-models-converter:convert [SOURCE_PATH] [TARGET_PATH]
```

* **SOURCE_PATH** is the path to the Directory with the View-Files
* **TARGET_PATH** is the path to the Directory where the Interfacs should be generated into

Only PHP-Classes in the **SOURCE_DIR**-Directory will be extracted, if they are marked with the [ConvertToTypescriptModel](./src/Annotation/ConvertToTypescriptModel.php)-Annotation.

Example:
```php
<?php

namespace My\Model\Ns;

use BrauneDigital\PhpToTypescriptConverterBundle\Annotation\ConvertToTypescriptModel;

/**
 * @ConvertToTypescriptModel()
 */ 
class ModelClass {
    public string $prop1;
    public int $prop2;
    public ?ClassName $prop3;

    /** @var SpecialClass[]  */
    public array $prop4;
}
```

Will Generate:
```typescript
import ClassName from './RelativePath/class.name.ts';
import SpecialClass from './RelativePath/special.class.ts';

interface ModelClass {
    prop1: string;
    prop2: number;
    prop3?: ClassName;
    prop4: SpecialClass[];
}
```

### Requirements
* Only public properies will be extracted.
* The usage of other classes is only possible, if they are within the Namespace of **SOURCE_DIR**.