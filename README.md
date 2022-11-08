# plugin-test
Pressable One Click Log In Plugin

# Setup for development
* Install Visual Studio Code
* Install Composer
```
brew install composer
```
* Open Visual Studio Code Terminal
Install code sniffer
```
composer require "squizlabs/php_codesniffer=*"
```

* Git Clone the WordPress COding Standards
Cloned not into project but a directory higher so it can be used by multiple projects
```
git clone git@github.com:WordPress/WordPress-Coding-Standards.git wordpress_coding_standards
```

* Set WordPress Coding Standards in installed paths for PHPCS
Be sure to use full path to cloned project location.
```
./vendor/bin/phpcs --config-set installed_paths /Users/<username>/Development/Pressable/wordpress_coding_standards
```
```
Using config file: /Users/paultrott/Development/Pressable/plugin-test/vendor/squizlabs/php_codesniffer/CodeSniffer.conf

Config value "installed_paths" updated successfully"
```

* Add WordPress as the standard
Update the Visual Studio Code settings file
```
// Mac Path
~/Library/Application\ Support/Code/User/settings.json
```

Add setting to file for global setting of standard
```
"phpcs.standard": "WordPress"
```

* Verify WP coding standards are configured correctly
```
./vendor/bin/phpcs -i

The installed coding standards are MySource, PEAR, PSR1, PSR2, PSR12, Squiz, Zend, WordPress, WordPress-Core, WordPress-Docs and WordPress-Extra
```

* Install additional composer packages
This will resolve some missing package errors.
```
composer require phpcsstandards/phpcsutils:@alpha
composer require phpcsstandards/phpcsextra:@alpha
```

* Test the code sniffer
```
phpcs --standard=WordPress <php-file>.php`
```

* Sample test localhost URL
```
http://localhost:8888/wordpress/wp-login.php?mpcp_token=MS0wZWQ1MzZlYjNlY2ZhMWJiNmQ0MmQwY2MxYTdkYTFkNmRlNjNiOTkyNTU5NzYzZDRhYjM4NWI5ZTE2ZmU5MWUw
```
