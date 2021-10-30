# laravel-csv

This package provides a Laravel Facade for writing/Reading Csv files.

## Installation

```bash
$ composer require jdefez/laravel-csv
```

## Reading a Csv file

### Basic usage

You can both work with `SplFileObject` or `SplTempFileObject`.

```php
$reader = Csv::reader(new SplFileObject('path-to-my-file', 'r'));

foreach ($reader->read() as $row) {

  // returns an array with the row's values

}
```

### Reading the first row

By default the first row is skipped. If you need to read the first row use
`$reader->withHeadings()` method.

```php
$reader = Csv::reader(new SplFileObject('path-to-my-file', 'r'))
  ->withHeadings();
```

### Reader::keyByColumnName()

The rows will be returned under the form of associative arrays with the columns
names as keys. The columns names will be kamel cased.

```php
// Given a file
//
// lastname;firstname;date of birth
// Jacky;Freek;1875-02-12

$reader = Csv::reader(new SplFileObject('path-to-my-file', 'r'))
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

$reader = Csv::reader(new SplFileObject('path-to-my-file', 'r'))
  ->keyByColumnName()

foreach ($reader->read() as $row) {
  //object(stdClass)#277 (2) {
  //    ["firstname"]=> string(4) "Jack"
  //    ["lastname"]=> string(5) "Freek"
  //    ["date_of_birth"]=> string(13) "1875-02-12"
  //}
}
```

### Fixing enconding

```php

// Fixing encoding from ISO to UTF-8

$reader = $reader->setToEncoding('UTF-8')
    // You need to provide a list of encoding that will be used to detect the
    //   current encoding. By default the Reader uses the list bellow
    ->setSearchEncodings(['ISO-8859-15', 'ISO-8859-1'])
    ->toObject();

```

## Writing a Csv file

```php
// todo
```

## Testing

## Todo:

**Reader:**

 - Adding the ability to setup headings names. It could be a convinient way to
   map data. Especialy if we want to key by columns names or cast rows to
   stdClass when there are no columns names at all.
