[![Build status](https://circleci.com/gh/j-d/csv-objects.svg?style=shield&circle-token=:circle-token)](https://circleci.com/gh/j-d/csv-objects)

# CSV to Objects

Library to import CSV or XLSX files into an array of objects according to an specified definition

## Instructions

To convert a file into an an array of objects, you must provide an `ImportDefinition`. This object is created by 
passing an array in an specific format. We recommend using YAML for defining this file as it is human-friendly. This 
format can be quite sophisticated, so two examples are provided: 
  
### Basic import definition
 
```yaml
name: Fruits definition                              # (Optional) Name of the import definition
columns:                                             # (Required) An associative array with the headings of the columns in the file that will be imported
    Name:   { fruit: '#Name#' }                      # 'fruit' is a shortname for the object being created for the list (defined below) and #Name# will be the argument passed to the constructor. The hashes indicate that it should replace it with the value on that column                                         
    Weight: ~                                        # Null indicates that it can be ignored
returns: CSVObjects\ImportBundle\Tests\Objects\Fruit # The class of the elements that will be returned 
```

### Full import definition

```yaml
name: Fruits definition                             
columns:
    File Version:  { expect: 38 }                                                    # (Optional) Expects indicates that the row value must always be as stated
    Name:          { fruit: ['#Name#', '#Colour#', '#Origin#', '#Origin - City#'] }  # The constructor now takes three arguments
    Weight:        ~
    Colour:        { validate: ['red', 'yellow'] }                                   # (Optional) Valid values that this column could have
    Origin:        { map: { 'Granada': 'Spain', 'Malaga': 'Spain', 'Dover': 'UK' } } # (Optional) It will apply this alias to the data on this column. If no validate is provided, non-matching values will convert into null
    Origin - City: ~
copy:                                                                               # (Optional) If specified, if will add more columns to the row, copying from the referenced columns before being processed
    Origin - City: Origin
returns: CSVObjects\ImportBundle\Tests\Objects\Fruit
```
