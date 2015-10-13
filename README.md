# jadeWP
Object-oriented array data gathering framework for WordPress

Version: 0.0.1

 + first version
 + menu module
 
Loading example:

Clone jadeWP into WP root directory
Add to yor theme functions php:

include_once ABSPATH.'/jadewp/init_autoloader.php';
use jadeWP\className\className as className;
$className = new className();

That's it.