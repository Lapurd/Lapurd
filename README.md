Lapurd
======

[![Build Status](https://api.travis-ci.org/Lapurd/Lapurd.png)](https://travis-ci.org/Lapurd/Lapurd)
[![Coverage Status](https://coveralls.io/repos/Lapurd/Lapurd/badge.png)](https://coveralls.io/r/Lapurd/Lapurd)

Lapurd is a reverse spelling of 'Drupal'!

It is a Drupal-like modular PHP framework written from scratch, which borrows a
lot goodies from Drupal, but aims to be more programmer friendly and built on
modern PHP technologies.

The design goals are:

* Follow PSR standards

* Drupal-like modular system

* Use composer manage dependence

Compared with Drupal
--------------------

###Differences

* Lapurd uses namespaces to distinguish different providers.

* Lapurd introduces an 'application' layer on top of the themes and modules with the final programmable configuration ability. It is much like the 'Profile' in Drupal.

* Lapurd makes a view the basic themable element. Any component that adds a view must have a template named the same with suffix '.tpl.php' provided in its 'views' directory.

###Similarities

* Lapurd has similar module locating mechanism. It will try to find modules inside 'APPROOT/modules' first, then 'SYSROOT/modules'.

* Lapurd has similar template locating mechanism. Similar to Drupal, a template could be overwritten by another template either with a more specific name or being placed in a location with higher priority. It will try the 'APPROOT/views' directory first, if none is found, the component which the template's corresponded view belongs to will be searched.

* Lapurd has similar hook mechanism for organizing code and extending functionality. A hook can be implemented by either an application or a module. In Lapurd, they are called 'implementation providers'. The hook implementation provided by an application is always invoked at the end so as to give it the final chance to do modifications.

* Lapurd maps the url path query to router callback arguments in the same way that Drupal does.
