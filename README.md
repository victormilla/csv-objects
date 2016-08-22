[![Build status](https://circleci.com/gh/j-d/csv-objects.svg?style=shield&circle-token=:circle-token)](https://circleci.com/gh/j-d/csv-objects)

# CSV to Objects

Library to import CSV or XLSX files into an array of objects according to an specified definition

## Instructions

To convert a file into an an array of objects, you must provide an `ImportDefinition`. This object is created by 
passing an array in an specific format. We recommend using YAML for defining this file as it is human-friendly. This 
format can be quite sophisticated, so two examples are provided: 
  
### Basic import definition
 
```yaml
name: Fruits definition                                  # (Optional) Name of the import definition
columns:                                                 # (Required) An associative array with the headings of the columns in the file that will be imported
    Name:   { fruit: '#Name#' }                          # 'fruit' is a shortname for the object being created for the list (defined below) and #Name# will be the argument passed to the constructor. The hashes indicate that it should replace it with the value on that column                                         
    Weight: ~                                            # Null indicates that it can be ignored
classes:                                                 # (Required) An associative array with the mapped classes 
    fruit: 'CSVObjects\ImportBundle\Tests\Objects\Fruit' # The first one will be the class that will be returned 
```

### Full import definition

All the extra properties are optional.

```yaml
name: Fruits definition
columns:
    File Version:  { expect: 38 }                                                                                             # Expects indicates that the row value must always be as stated
    Name:          { fruit: ['#Name#', '#Colour#', '#Origin#', '#Origin - City#', '#Class#', '#Expiry date#', '#Contract#'] } # The constructor now takes an array of arguments
    Weight:        ~
    Colour:        { validate: ['red', 'yellow'] }                                                                            # Valid values that this column could have
    Origin:        { map: { 'Granada': 'Spain', 'Malaga': 'Spain', 'Dover': 'UK' } }                                          # It will apply this alias to the data on this column. If no validate is provided, non-matching values will convert into null
    Origin - City: ~
    Class:         { extract: 'Class (.*)', validate: ['A+', 'A', 'B', 'C'] }                                                 # It is like mapping but using the expecified regular expression to do the conversion. The desired part must be in brackets. Functions can optionally be combined when required
    Expiry date:   { validate: 'date', sourceFormat: 'd/m/Y', format: 'Y-m-d' }                                               # If validate is not an array, it can be an special value. 'date' will check that it is a date. Optionally, you can specify the source format using 'sourceFormat' or reformat it by specifying 'format'
    Contract:      { contract: ['#Contract#'] }
copy:                                                                                                                         # If specified, if will add more columns to the row, copying from the referenced columns before being processed
    Origin - City: Origin
classes:
    fruit:    ['CSVObjects\ImportBundle\Tests\Objects\Fruit', 'getFruitFromFullInfo']                                         # If the mapped class is an array, it will make a static call to the specified method to create the class
    contract: ['CSVObjects\ImportBundle\Tests\Objects\Contract', 'getContractFromId' ]
```

For the object mappings, you can also use a service in the `['@service_name', 'method']` notation.

For a more complex example see `school-results.yml` and `school-results.csv`.
