# laravel-csv

This package provides a Laravel Facade for writing/reading Csv files.

## Installation

```bash
$ composer require jdefez/laravel-csv
```

## Reading a Csv file

This utility class does not take hold of any data. It simply provides an
iterator that you can use to read your csv files.

### Basic usage

You can both work with `SplFileObject` or `SplTempFileObject`.

```php

use Jdefez\LaravelCsv\Facades\Csv;

$reader = Csv::reader(new SplFileObject('path-to-my-file.csv', 'r'));

foreach ($reader->read() as $row) {

  // returns an array with the row's values

}
```

### Reading the first row

By default the first row is skipped. If you need to read the first row use
`$reader->withHeadings()` method.

```php
$reader = Csv::reader(new SplFileObject('path-to-my-file.csv', 'r'))
  ->withHeadings();
```

### Reader::keyByColumnName()

The rows will be returned under the form of an associative arrays with the
columns names as keys. The columns names will be kamel cased.

```php
// Given a file
//
// lastname;firstname;date of birth
// Jacky;Freek;1875-02-12

$reader = Csv::reader(new SplFileObject('path-to-my-file.csv', 'r'))
  ->keyByColumnName()

foreach ($reader->read() as $row) {

  //array(
  //    'firstname' => 'Jacky',
  //    'lastname' => 'Freek',
  //    'date_of_birth' => '1875-02-12'
  //)

}
```

### Reader::toObject()

The rows will be casted to object using the column names as properties.
The columns names will be kamel cased.

```php
// Given a file
//
// lastname;firstname;birthdate
// Jacky;Freek;1875-02-12

$reader = Csv::reader(new SplFileObject('path-to-my-file.csv', 'r'))
  ->toObject()

foreach ($reader->read() as $row) {

  //object(stdClass)#277 (2) {
  //    ["firstname"]=> string(4) "Jack"
  //    ["lastname"]=> string(5) "Freek"
  //    ["date_of_birth"]=> string(13) "1875-02-12"
  //}

}
```

### Fixing enconding

For this feature to work, you need to provide a list of encodings that will be
used to detect the current encoding. By default the Reader uses:
`['ISO-8859-15', 'ISO-8859-1']`

```php

// Fixing encoding from ISO to UTF-8

$reader = $reader->setToEncoding('UTF-8')
    ->setSearchEncodings(['ISO-8859-15', 'ISO-8859-1'])
    ->toObject();

```

## Writing a Csv file

### Writing a entire collection of data

```php
$collection = collect([
  ['Jack', 'Freek', '1875-02-12'],
  ['Julian', 'Nightmare', '1815-11-11'],
  // ...
]);

Csv::writer()
  ->setFile(new SplFileObject('path-to-my-file.csv', 'w'))
  ->setColumns(['firstname', 'lastname', 'date of birth'])
  ->setData($collection)
  ->write();
```

### Writing line by line.

```php
$collection = collect([
  ['firstname', 'lastname', 'date_of_birth'],
  ['Jack', 'Freek', '1875-02-12'],
  ['Julian', 'Nightmare', '1815-11-11'],
  // ...
]);

$writer = Csv::writer()->setFile(new SplFileObject('path-to-my-file.csv', 'w'));

$collection->each(fn ($line) => $writer->put($line));

```

### Mapping data

You can also map data when writing to the file with `Writer::write(callable $callback)`
of `Writer::put(array|callable $row)`.

```php

$models = Users::all();

$writer = Csv::writer(new SplFileObject('path-to-my-file.csv', 'w'));

$writer->setData($models)
  ->write(fn ($item) => [
    $item->firstname,
    $item->lastname,
    $item->birthday->format('Y-m-d')
  ]);

// Or iterate over the collection and append each line to the file.

$models->each(fn ($model) => $writer->put(fn () => [
    $model->firstname,
    $model->lastname,
    $model->birthday->format('Y-m-d')
]);

```

## Todo:

**Reader:**

 - Adding the ability to setup headings names. It could be a convinient way to
   map data. Especialy if we want to key by columns names or cast rows to
   stdClass when there are no columns names at all.

**Writer:**

 - Writing to a given encoding
 - Writer::put should also take an array or a callable as parameter

