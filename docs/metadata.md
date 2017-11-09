# Metadata Generation

Since CleaRest is all based in annotations, it's important to retrieve these metadata quickly and it's not
wise to parse doc comments in run time, every request.

Therefore all metadata is extracted once and stored in files to be retrieved upon request.
The metadata generation is done basically in two steps: metadata extraction and consistency check, to avoid
runtime errors.

Both functionalities are encapsulated in the `CleaRest\Metadata\ConsoleTool`, which is used in the console script
*vendor/junior-ferraz/clearest/scripts/genmetadata.php*.

## What does it do?

The metadata generation process consists of those tasks:
 * Extract framework classes' metadata
 * Extract user namespace classes' metadata
 * Generate services implementation index
 * Generate capabilities index
 * Check type consistency (in annotations)
 * Check annotation arguments consistency
 * Check PlainObjects consistency
 * Saves all metadata into files
 
## How to use the tool?

You can run the *vendor/junior-ferraz/clearest/scripts/genmetadata.php* 
or call the `CleaRest\Metadata\ConsoleTool::run()` directly passing the arguments,
in case you want to use it in a deployment script or so.

The console tool has these parameters:
```
php genmetadata.php <source-folder> <namespace> [--everything] [--include=<file>] [out-folder=<folder>]
```
Parameters:
 * **source-folder**: where the source files are (classes, interfaces etc).
 The tool will include the files under this folder and generate metadata to the included classes 
 in the <namespace> given namespace.
 * **namespace**: only classes under this namespace are considered
 
Flags:
 * **--everything**: if this flag is not present, only services and plain objects will have their metadata generated.
 Other classes and interfaces will be ignored.
 * **--include=\<file>**: includes a <file> before generation. This is particularly useful when you need to register
 annotations or validations rules before generating metadata.
 * **--out-folder=\<path>**: defines where the metadata files will be saved.
 By default it's a *metadata* folder in the top level of the project (same level as *src*)

## Loading metadata

If you want to use a class metadata to know its methods, parameters, types, annotations etc, 
call the `CleaRest\Metadata\MetadataStorage` with the class or interface name.
```php
<?php
$metadata = \CleaRest\Metadata\MetadataStorage::getClassMetadata(MyClass::class);
```