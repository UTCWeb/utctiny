YOURLS-AuthMgrPlus
=====================

This plugin manages essential YOURLS functions and separates user data with role-based access controls (RBAC). With access controls enabled, you can safely delegate access to the admin pages and API while keeping link data private. You share an installation, log on, add a link, and nobody else sees it.

Features
--------
-  Easily assign users to roles
-  Easily fine tune role permissions
-  IPv4 based Authentication (optional)
-  All plugin pages, including main management page, hidden to non-admins by default. Easy to unblock pages.
-  Plenty of hooks to filter Roles, Role Capabilities, and _any_ of the default data environemnt (such as plugin page visibility)
-  Fine(r) tuned API access
-  PHP 8 compatible
-  No tracking of admins or editors by default

Requirements
------------
- YOURLS 1.7.3 +
- Incompatible Plugins:
    - nicwaller's [`authmgr`](https://github.com/nicwaller/yourls-authmgr-plugin)
    - Ian Barber's [`Seperate Users`](https://github.com/ianbarber/Yourls-Separate-Users)

Installation
------------
1. Download the [latest release](https://github.com/joshp23/YOURLS-AuthMgrPlus) of this plugin.
1. Copy the `authMgrPlus` folder into your `user/plugins` folder for YOURLS.
1. Set up some parameters for authMgrPlus (details below)
1. Activate the plugin with the plugin manager in the YOURLS admin interface.
1. If you have pre-existing links in your database, you will have to manually asign them a user via an sql querry.

Default Roles
-------------
The default roles are set up as follows:

Role          | Capabilities
--------------|---------------------------------------------------------------------------------------------------
Administrator | Can manage plugins, no limits, not tracked on any
Editor        | Can add (+API), access own and all others', edit & delete own & anon URL's, not tracked on any
Contributor   | Can add (+API), access own and anon's, and edit & delete own URLs, not tracked on own
Anonymous     | Can add and access (see stats, etc) anon links (If public)

Configuration
-------------
Add role assignments to your `user/config.php` file.

```
$amp_role_assignment = array(
  'administrator' => array(
    'your_username',
  ),
  'editor' => array(
    'your_close_friend',
  ),
  'contributor' => array(
    'your_other_friend',
  ),
);
```

You can also designate a range of IP addresses that will automatically be granted all capabilities. By default, all accesses from IPv4 localhost (127.0.0.0/8) are granted full access.

```
$amp_admin_ipranges = array(
    '127.0.0.0/8',
);
```
Plugin management and plugin pages are available to admins only by default. Individual pages can be exposed to non-admin roles like so:
```
$amp_allowed_plugin_pages = array(
	'sample_page',
	'another_plugin_slug'
);
```
Explore the code to see how to set `$amp_role_capabilities` and `$amp_anon_capabilities`. These are set to defaults in the `amp_env_check()` function.

You can also assign a default role to all logged-in users that have no explicit role (note, case sensative):
```
$amp_default_role = "Editor";
```


#### NOTE:
This is a fork of nicwaller's [Authmgr](https://github.com/nicwaller/yourls-authmgr-plugin) merged with Ian barber's [Separate Users](https://github.com/joshp23/Yourls-Separate-Users) plugin. Both code bases underwent heavy rewrites, and have been extensively updated and tightly integrated here, resulting in a lean and highly functional user authorization management environment.

### Tips
Dogecoin: DARhgg9q3HAWYZuN95DKnFonADrSWUimy3

License
-------
Copyright 2018 Joshua Panter  
Copyright 2013 Nicholas Waller (code@nicwaller.com)  
Copyright 2011 Ian Barber  
