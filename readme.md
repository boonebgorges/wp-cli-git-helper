# wp-cli-git-helper

Adds the `wp gh` command, which is a wrapper for `wp [plugin|theme] [update|install]`.

## Huh?

One common workflow for managing a WordPress site through version control is to keep the entire codebase - WP, themes, and plugins - under version control. On this setup, updating third-party plugins and themes can be tedious, since updates may be frequent, requiring lots of commits and commit messages. `wp gh` does this work for you, by composing a commit message and performing the commit.

## Examples

Installing a plugin:

    $ wp gh plugin install --version=3.0 jetpack
    Installing Jetpack by WordPress.com (3.0)
    Downloading install package from https://downloads.wordpress.org/plugin/jetpack.3.0.zip...
    Unpacking the package...
    Installing the plugin...
    Plugin installed successfully.

    # `wp gh` has written a commit message and created a changeset
    $ git log -n 1
    commit 40588985cc6ef1904350932106737be933b141ce
    Author: Boone B Gorges <boonebgorges@gmail.com>
    Date:   Tue Dec 16 21:03:46 2014 -0500

        Install plugin: jetpack.

        Name: Jetpack by WordPress.com
        Version: 3.0

Updating a theme:

    $ wp gh theme update make
    Downloading update from https://downloads.wordpress.org/theme/make.1.4.6.zip...
    Unpacking the update...
    Installing the latest version...
    Removing the old version of the theme...
    Theme updated successfully.
    Success: Updated 1/1 themes.

    # `wp gh` has written a commit message and created a changeset
    $ git log -n 1
    commit e0327c9bc26fa0e35e1a06d128f998d86c3b81db
    Author: Boone B Gorges <boonebgorges@gmail.com>
    Date:   Tue Dec 16 21:09:11 2014 -0500

        Update theme: make.

        Name: Make
        New version: 1.4.6
        Previous version: 1.0.0

## Totally Sweet and Customizable Features

None to speak of. This is a tool I've built for my own workflow. It assumes that you keep your entire WP installation in the same repo. It assumes that you like the commit message format I've chosen. It does not support core updates (yet). Some things I will eventually add:

* Ability to override the commit message, either by passing it directly as a `--commit-message` param, or perhaps by drafting a commit message and then sending you to `$EDITOR`.
* Support for `wp core update`.
* Proper `delete` support.
* Support for the `--all` flag.
* SVN support (jk!)

## Changelog

### 0.1.0

* Initial release
