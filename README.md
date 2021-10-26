
#laravel-csv

This package provides a Laravel Facade for writing/Reading Csv files.

##Installation

```bash
$ composer require jdefez/laravel-csv
```

##Reading a Csv file

Basic usage

```php

// You can both work with SplFileObject or SplTempFileObject

$reader = Csv::reader(new SplFileObject('path-to-my-file', 'r'));

foreach ($reader->read as $row) {

  // returns an array with the row's values

}
```

```php
// By default the first row is skipped. If you need those headings use withHeadings()

$reader = Csv::reader(new SplFileObject('path-to-my-file', 'r'))
  ->withHeadings();
```

##Writing in a Csv file

```php
// todo
```

##Testing

##Todo:

 - Check the package auto discovery works well.

**Reader:**

 - Adding the ability to setup headings names. It could be a convinient way to
   map data. Especialy if we want to key by columns names or cast rows to
   stdClass when there are no columns names at all.
