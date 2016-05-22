# KayakDocs Generator

Generates JSON documentation from composer package sources.


## Installation

    $ git clone https://github.com/kayak-docs/generator.git
    $ cd generator
    $ composer install  
    $ php generate.php

Base package and paths are currently hardcoded in generate.php. Modify the file if you want to change them.


## Roadmap

- Generate documentation for multiple packages during a single run.
- Resolve and include dependencies.
- Configuration via file.
- Generate JSON files.
- Console output during generation.
