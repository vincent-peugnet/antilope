# Antilope

[![build][build-badge]][build-url] ![style][codestyle] ![phpstan][phpstan]

__Antilope__ is the name of this __Sharable Network Tracker__.

Alternatives names could be *Argali* or *Urial* as it refers to *Gazelle*, the main inspiration dor this project.

This isn't an app, but a tool allowing people to create their own networks and setting them up for each use case. Checkout the [requirements](#requirements) to set up your own instance on a server.

> It's built using __PHP 7__ with __Symfony 5__ framework and __Bootstrap 4__ toolkit.

## Concept

The whole idea behind Antilope, is to transpose the *private tracker* system to anything else but torrents. __Sharables__ are the key concept of Antilope : they are a the unit of what you can share.

The classic question is : What can be shared through this system ?

And the classic answer is : what would you share ?

In fact, there are no categories defining what should or should not be a sharable. The only limitation of what can be shared or not is your imagination! Sharables are only defined by a set of parameters (place, date, consumability...) that are all optionals. Sharables are "managed" by at least one user. Those managers can precisely define how they relate to the sharable and how they want to manage it.

Antilope is designed to encourage the creation of small scale networks instead of massive ones. The main goals are :

- to increase trust between users instead of violents rating system.
- have the possibility to gamify life interactions.
- have an half self-managed, half moderated user management.

### References

- [Case study of private trackers as a solution of freeriding in peer-top-peer networks](https://www.cambridge.org/core/journals/journal-of-institutional-economics/article/institutional-solutions-to-freeriding-in-peertopeer-networks-a-case-study-of-online-pirate-communities/2F379FE0CB50DF502F0075119FD3E060#)
- [Description of the private tracker word in a Linux Wiki](https://wiki.installgentoo.com/index.php/Private_trackers)

## How it works

### Create a sharable

When you create a sharable you can setup a lot of meta infos :

- name
- description
- details (markdown)
- begin date (optional)
- end date (optional)
- place* (optional)
- responsibility (if managers are responsible for what is shared)
- consumability* (if the sharable is something that can only be shared a certain amount of times)
- visibility (optional : above which user class does users can access the sharable)
- contact method (see [Managing and contact method](#managing-and-contact-method))

*: todo

### Managing and contact method

There can be any number of users managing the same sharable, that way they will all receive the same amount of [share points](#share-score) during [validation process](#validation).

A contact method must be chosen for every sharable between this four strategies :

1. __no contact :__  no contact infos are given with the sharable.
2. __automatic :__ when an user is interested by a sharable, contact infos are exchanged.
3. __manual :__ when an user is interested by a sharable, contact infos are exchanged after interested users have been manually reviewed by a manager.
4. __never :__ Only interested user contact infos are send to managers.

The methods go from less protection to maximum managers protection. As you are sharing, you are always able to be more protected.

### Search and Discover

Depending on how [user classes](#user-class-system) have been set up, users can search through the database of sharable using differents views :

- list search
- map*
- calendar*

*: todo

### Interest

First step to benefit from a sharable is to indicate that you're interested. After that depending on the sharable [contact method](#managing-and-contact-method), contact infos are exchanged.

### Validation

The validation system is there to avoid violent rating system. As you can only validate a sharable and add a message like in a guestbook, you can only express criticism using words. You can't just put a 1/5 stars without explaining why.

Validation can only occur after the being interested in a sharable and that contact infos have been exchanged.

But if there is a real problem with a sharable, user could *report* it. Depending on the report subject, this will be handled by the managers or moderators.*

*: todo

### User Class system

One of the most efficient way to increase gamification in a network is to implement an user class system. In Antilope admins can set up any user classes they want from rank 0 to 100. They allow or disallow users to share or access sharables, invite users... And the user classes can be triggered by many parameters such as the [share score](#share-score), account age, number of validations the user gave...

### Share Score

The share score is the system that try to give an indication of how much an user has participate in the network. Unlike *private trackers*, that can precisely mesure torrents ratio, it's impossible and absurd to have a correct quantification of how much have been shared between users. But still, share score is there as the only indicator possible, and can be used for user class promotions, this is how it works :

Every time an user validate a Sharable, every user managing it win an amount of points, based on :

- validating user rank (higher user rank give more points)
- the amount of validation already given for this sharable (first validations give more points)
- divided by the number of managers

### Interface

![interface](https://246.eu/media/projets/antilope/antilope.gif)

Setup
-----

### Requirements

For now, the only way to install an Antilope app is via Symfony and `composer`.

- Apache
- PHP 7
- MySQL

### Install

Install via git clone.

```
git clone https://github.com/vincent-peugnet/antilope <APP_DIRECTORY>
```

Then run composer

```
composer install
```

### Global parameters

Default global parameters are generated interactively during `composer install` command.

- `app.openRegistration (bool)` Allow free sign up, disable the invite code system
- `app.userLimit (int)` Max user limit. If this number is reached, registrations are closed
- `app.invitationDuration (int)` Duration of invite code before it expired (in hours)
- `app.userClassRankSwap (bool)` Allow User Class to be increased after next one or decrease before previous one

Development
-----------

### Run checks

    composer check

### Fix basic check errors

    composer fix

### Run the dev server

    bin/console server:start

### Generate migrations for sqlite

    bin/console doctrine:migrations:diff --db-configuration sqlite

<!-- long url references -->
[build-badge]:https://img.shields.io/github/workflow/status/vincent-peugnet/antilope/build/main
[build-url]:https://github.com/vincent-peugnet/antilope/actions?query=branch%3Amain++workflow%3Abuild
[codestyle]: https://img.shields.io/badge/code%20style-PSR12-brightgreen
[phpstan]: https://img.shields.io/badge/phpstan-level%205-green
