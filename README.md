# Tennis-web-app
© 2024 Charles Davies. All Rights Reserved. 

## Motivation
This web app is an in-progress retirement project to meet a retirement goal to "do some coding".

## Purpose of the app
This Tennis web app is designed to help people who arrange tennis fixtures played by an email list of clubmates.

The app supports:
- Maintaining a list of users with emaill addresses
- Maintaining multiple fixture series, where each series consists of recurring fixtures (time and day of week)
- A fixture series acts as a template for creating the next fixture
- Maintaining a fixture, which initially inherits a list of invitees from the fixture series
- Sending email invitations with a personal link that enables each invitee to self-report their desire to play and any court bookings they have made for that fixture

## Design
The backend uses MySQL or MariaDB, Apache, and (modern) PHP.

The application uses the [Slim framework](https://www.slimframework.com/), which uses a front controller design and supports autoloading of classes.

The HTML in the HTTP response is created using the [Twig template engine for PHP](https://twig.symfony.com/), via the slim/twig-view component.

The frontend uses the [Pure CSS library](https://purecss.io/) and is designed to work well on a mobile browser but also on a PC browser.

To provide a better user eperience than would otherwise be possible, some of the pages use a React component within a Twig-defined page. The APIs to support the React components are provided by the same PHP backend.

Authorisation is based on personal tokens that are delivered as acces links by email. Each token is associated with a role (User, (series) Owner, Admin), which determines the level of functionality that can be accessed. 

