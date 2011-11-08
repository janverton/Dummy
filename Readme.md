Dummy
=====

A simple PHP templating engine, somewhat like the SMARTY framework but
significantly dumber

Installation
------------

None realy, just create an instance of the Dummy class and you're good to go!

Usage
-----
Let's say you've created a cutting edge template named demo.tpl which looks
like this:

> <h1>Hello {name}!</h1>

You will be able to use it like this

> <?php
>
> // Create a Dummy instance
> include 'Dummy.php';
> $dummy = new Dummy;
>
> // Get the template and replace the name variable with 'World'
> $dummy->getTemplate('demo.tpl');
> $this->dummy->replace('name', 'World');
> 
> // Display the contents
> echo $dummy->getTemplate('demo.tpl');