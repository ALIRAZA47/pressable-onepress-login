# Pressable Plugin
Pressable OnePress Log In Plugin

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

* Git Clone the WordPress Coding Standards
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

## Customizing Redirects in Pressable OnePress Log In Plugin
To tailor the redirect behavior of the plugin, you can utilize the filters. These filters allow you to specify allowed hosts and set custom redirect URLs in case of plugin errors. 
Below is a guide on how to implement these filters. Remember, this code should be added to an MU (Must-Use) plugin.

#### Step 1: Create Your Custom Function
You will write a custom function to modify the redirect behavior. 
This function can have any name that makes sense for your context. 
For our example, we'll name it `customize_onepress_redirects`.

#### Step 2: Add the Code to an MU-Plugin
Add the following code to your MU-plugin file. If you don't have 
an MU-plugin, you can create one in the wp-content/mu-plugins directory 
of your WordPress installation.

```php
<?php

add_action('muplugins_loaded', 'customize_onepress_redirects');

function customize_onepress_redirects() {
    add_filter('onepress_login_custom_redirect_url', 'custom_redirect_url_function', 10, 3);
    add_filter('onepress_login_additional_hosts', 'custom_allowed_hosts_function');

    function custom_allowed_hosts_function($default_hosts) {
        // Add your custom hosts here
        $default_hosts[] = "yourcustomdomain.com";
        return $default_hosts;
    }

    function custom_redirect_url_function($default_redirect_url, $site_id, $user) {
        // Implement your custom logic to determine the redirect URL
        return "https://yourcustomdomain.com";
    }
}
```
