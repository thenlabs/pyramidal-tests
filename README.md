
# PyramidalTests

Write PHPUnit tests at BDD style.

```php
<?php

setTestCaseNamespace('ThenLabs\PyramidalTests\Demo\Tests');
setTestCaseClass('PHPUnit\Framework\TestCase');

use ThenLabs\PyramidalTests\Demo\Product;
use ThenLabs\PyramidalTests\Demo\Category;

testCase('create a product', function () {
    setUp(function () {
        $this->product = new Product;
    });

    test('by default the product has a code', function () {
        $this->assertNotEmpty($this->product->getCode());
    });

    test('by default the product not contains categories', function () {
        $this->assertCount(0, $this->product->getCategories());
    });

    testCase('the product adds a category', function () {
        setUp(function () {
            $this->category = new Category;
            $this->product->addCategory($this->category);
        });

        test('the product contains the category', function () {
            $this->assertContains($this->category, $this->product->getCategories());
        });
    });
});
```

## Documentation.

1. [English](https://thenlabs.org/en/doc/pyramidal-tests/master/index.html)
2. [Español](https://thenlabs.org/es/doc/pyramidal-tests/master/index.html)
