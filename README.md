# Vox Data manipulation library

With this library you can:
* Serialize php classes into json, xml and create custom formatters
* Unserialize data into php objects (json, xml and custom formats)
* Transfer data between different data structures (from a class instance to a different class instance)
* Normalize php object into array
* Use a property accessor to access private class members
* You can do all that with extension and annotate discriminators
* It's also compatible with ocramius proxies

## Usage

since this library depends on [Vox\Metadata](https://github.com/jhonatanTeixeira/metadata) package you must obtain a 
MetadataFactory instance, with it you can then create a ObjectExtractor and a ObjectHydrator instance.

Given the class:
```php
class DataClass {
    private string $name;
    
    public int $age;
    
    public function __construct($name, $age) {
        $this->name = $name;
        $this->age = $age;
    }
    
    public function getName() {
        return $this->name;
    }
}
```

You may create your data extractor and hydrator:

```php
$mf = (new MetadataFactoryFactory())->createAnnotationMetadataFactory();
$oe = new ObjectExtractor($mf);
$oh = new ObjectHydrator($mf);
```

To extract the data, you may use the extract method and you can provide a context to it, there's a special built in 
context value called extractType you can use, that will provide a property on the normalized array called __type__ with 
the origin class FQCN (fully qualified class name), that may be useful on the denormalization (hydration) process.

```php
$context = ['extractType' => true];
$data = $extractor->extract(new DataClass('John Doe', 18), $context);
```

You can hydrate the data back into a new class or a class instance:

```php
$object = $oh->hydrate(DataClass::class, $data);
$oh->hydrate($object, $data);
```

### Data bindings

You can customize the data input and output by using the @Binding and @Exclude annotations:

```php
class DataClass {
    #[Bindings(source: 'first_name', target: 'username')]
    private string $name;
    
    #[Exclude(input: false, output: true)]
    public int $age;
    
    public function __construct($name, $age) {
        $this->name = $name;
        $this->age = $age;
    }
    
    public function getName() {
        return $this->name;
    }
}
```

In this example you can have an input array containing the "first_name" field and it will be mapped to the name property, 
and when you extract it, the resulting array will contain this data on the "username" field. These are completely optional
in case you want the same field for input and output you can pass only the source, note that 'source' is the first 
parameter so you can omit the param name like this ```#[Bindings('first_name')]```.

The exclusion can also be controlled by input and output, in this case will be used on hydration and be left out on 
extraction.