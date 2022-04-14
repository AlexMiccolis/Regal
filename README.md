<h1 align="center">Regal</h1>
<p align="center">A static site generator written in PHP, optimized for minimum bandwidth usage.</p>
<p align="center">Regal provides a simple HTML and CSS templating system with a syntax reminiscent of Vue.</p>

## Usage
A Regal site is built with a single command:
```sh
php vendor/bin/regal.php --templates=[template directory] [names of templates to render]
```
The compiled site will be written to `./dist` by default, this can be changed by providing a directory via the `--output` command line argument.

## Example
#### templates/index.html
```html
<!-- The template block contains the HTML structure  -->
<template>
    <!--
        The <instance> block inserts an instance of the specified template 
    into the current document. The regal:path attribute specifies the path
    to the template relative to the template directory passed via the
    command line.
        You can supply properties to the instance by adding attributes
    whose names start with ':' to the instance element.
    -->
    <instance regal:path="header" :title="Index" :author="Alex Miccolis"/>
    <body>
        <h1>Hello from Regal</h1>
    </body>
</template>

<!-- Styles are global by default -->
<style>
    h1 {
        color: red;
    }
</style>
```
#### templates/header.html
```html
<template>
    <head>
        <meta charset="utf-8">

        <!--
            This is a property substitution. During compilation, the double curly-braces
        will be replaced with the value of the "author" property.
        -->
        <meta name="author" content="{{ author }}">
        
        <!-- Can you guess what the title of index.html will be? -->
        <title>{{ title }}</title>
    </head>
</template>
```
####  Run
```sh
php vendor/bin/regal.php --templates=templates index
```
####  dist/index.html
```html
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Alex Miccolis">
        <title>Index</title>
        
        <!-- Styles are minified and appended to the head -->
        <style>h1 {color: red;}</style>
    </head>
    <body>
        <h1>Hello from Regal</h1>
    </body>
</html>
```
