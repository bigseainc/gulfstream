# Gulfstream #

This is a base framework for quick set up of an API and Admin panel functionality with Twig, Bootstrap, and Slim PHP.

We've kept a simple handler for Propel in here as well, since we've been using this for some projects.

## How do I get set up? ##

### For New Sites ###

* Run `composer install` in root
* Duplicate sample.env and add your credentials
* Set up MAMP or equivalent to point a domain to `/api/public`

### For Existing Configurations ###

* Run `composer install` in root
* Set up database and import one of two files:
    * `.scripts/clean-slate.sql` (bare-bones, mostly just table set up)
    * `.scripts/latest.sql` (most recent copy from live version)
* Duplicate sample.env and add your credentials
* Set up environment to point a domain to `/api/public`

# Admin

* confirm your .env has the proper `API_BASE` url set
* set up environment to point a domain to `/admin/public`

## Contribution guidelines ##

We are trying to keep this simple to configure and get API-based projects off the ground quickly. Currently, development is internal. We're interested in ideas for improvement, and will definitely consider adding suggestions.

## Who do I talk to? ##

Chris Lagasse <chris@bigseadesign.com>
Big Sea <bugs@bigseadesign.com>