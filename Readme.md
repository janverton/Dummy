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

> Hello {name}!

You will be able to use it like this

> <?php   
>
> // Create a Dummy instance   
> include 'Dummy.php';   
> $dummy = new Dummy;   
>
> // Get the template and replace the name variable with 'World'   
> $dummy->parseTemplate('demo.tpl');   
> $this->dummy->replace('name', 'World');   
> 
> // Display the contents   
> echo $dummy->getTemplate('demo.tpl');   

Some more advanced usage
------------------------
Looping data to a fancy table you say? No probs! Again, a sweet template 
named demo2.tpl:

> <table>   
>   {loop artists}   
>     <tr><td>Name: {:name}</td><td>({:dob})</td></tr>   
>   {/loop}   
> </table>   

The next piece of code will print 2 profound queen members, How cool is that!

> <?php   
>
> // Create a Dummy instance   
> include 'Dummy.php';   
> $dummy = new Dummy;   
>
> // Get the template and replace the name variable with 'World'   
> $dummy->parseTemplate('demo2.tpl');   
> // Create an array with stdClass objects with a name and age property   
> // assigned   
> $vocals = new stdClass;   
> $vocals->name = 'Freddie';   
> $vocals->dob = '1946-09-05';   
>   
> $guitars = new stdClass;   
> $guitars->name = 'Brian';   
> $guitars->dob = '1947-07-19';   
>   
> // Add the objects to an Array Iterator   
> $iterator = new ArrayIterator(array($vocals, $guitars));   
>   
> // Now assign data to the loop   
> $this->dummy->assignLoop('artists', $iterator);   
>   
> // Display the contents   
> echo $dummy->getTemplate('demo2.tpl');   
