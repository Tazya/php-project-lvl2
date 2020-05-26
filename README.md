![PHP CI](https://github.com/Tazya/php-project-lvl2/workflows/PHP%20CI/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/2def5b0fdbc268810a5f/maintainability)](https://codeclimate.com/github/Tazya/php-project-lvl2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/2def5b0fdbc268810a5f/test_coverage)](https://codeclimate.com/github/Tazya/php-project-lvl2/test_coverage)

# PHP-Project-lvl2
Gendiff - Cli application for search differences in configuration files  

## Install
Composer requires to install
Command for terminal:  
```
composer global require tazya/gendiff:dev-master
```  
PHP version > 7.4.0 is requred   

## Basic JSON and YAML config files compare
Use ```gendiff <file1> <file2>``` command to compare configs
[![asciicast](https://asciinema.org/a/325467.svg)](https://asciinema.org/a/325467)

## Recursive config files compare
Use ```gendiff <file1> <file2>``` command to compare configs
[![asciicast](https://asciinema.org/a/325468.svg)](https://asciinema.org/a/325468)

## Format options usage
Use ```gendiff --format <format> <file1> <file2>``` command to compare configs. Allow 'pretty', 'plain' and 'json' formats  
[![asciicast](https://asciinema.org/a/325822.svg)](https://asciinema.org/a/325822)

## Get results in JSON 
Use ```gendiff --format json <file1> <file2>``` command to show results in JSON. It can be useful as library in your project  
[![asciicast](https://asciinema.org/a/326352.svg)](https://asciinema.org/a/326352)