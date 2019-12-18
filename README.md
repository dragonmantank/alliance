# Alliance

Alliance is a PHPUnit extension that adds in functionality for using a database
in integration and functional testing. This will clean and run database
migrations automatically between test runs, and provides additional assertions
for checking data inside of the database.

This extension is _not_ meant to be a replacement for unit tests, but as a
compliment for when actual database testing needs to be done. Tests that use
Alliance will be slower than traditional unit tests.

## Installation

Installation is done via [Composer](https://getcomposer.org) and should be
added as a dev requirement:

```bash
composer require --dev dragonmantank/alliance
```

Alliance requires PHPUnit 8.0 and PHP 7.2 or higher.

## Configuration

Alliance is configured through a combination of the `phpunit.xml` file for your
project as well as the configuration settings your migration library already
uses. This allows you to maintain your DB settings in an expected location for
your application, and Alliance will ingest that to do it's job.

For the most part, you will need to add a new `<extensions>` block to your
`phpunit.xml` file, and modify the configuration options. Each migration
library plugin will have its own set of configuration.

More information can be found in the ["Configuring Extensions"](https://phpunit.readthedocs.io/en/8.5/extending-phpunit.html#configuring-extensions)
portion of the PHPUnit Manual.

### Configuring Phinx

```xml
<extensions>
    <extension class="Alliance\DbTester">
        <arguments>
            <string>phinx</string>
            <array>
                <!-- Config file path is relative to the phpunit.xml file -->
                <element key="config_file"><string>phinx.yml</string></element>
                <!-- Environment block to read from your config file -->
                <element key="environment"><string>testing</string></element>
            </array>
        </arguments>
    </extension>
</extensions>
```

## Usage

All you need to do is have your extend `Alliance\DbTestCase` instead of 
`PHPUnit\Framework\TestCase`. This will allow the extension to detect that
it needs to run the database mechanisms.

```php
namespace MyAppTest;

use Alliance\DbTestCase;

class FooTest extends DbTestCase
{

}
```

`Alliance\DbTestCase` will expose some database-specific assertions that you
can then use in your tests.

### New Assertions

#### `assertNotInTable(string $table, array $query)`

Make sure that a specified row does not exist in the specified table. `$query`
is an array of columns and values that will be used to build an AND query to
check for the row.

```php
$this->assertNotInTable('posts', ['title' => 'My Post title']);
```

#### `assertSingleRowInTable(string $table, array $query)`

Make sure that a specified row does exist as a single row in the specified 
table. `$query` is an array of columns and values that will be used to build 
an AND query to check for the row.

Multiple rows that match the query will cause this assertion to fail, so make
sure your query is as specific as possible.

```php
$this->assertSingleRowInTable(
    'posts', 
    [
        'id' => $post->id,
        'title' => $post->title,
        'slug' => $post->slug
    ]
);
```

## Supported Migration Libraries

- [phinx](https://github.com/cakephp/phinx)

## License

Licensed under the [Apache License 2.0](LICENSE.md)