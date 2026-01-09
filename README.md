# Tennis-web-app

© 2024-26 Charles Davies. All Rights Reserved.

## Motivation

This web app is a retirement project to meet a retirement goal to "do some coding".

## Key Concepts

- **User** - A person registered in the system with an email address and access token
- **Series** - A recurring pattern of tennis sessions (e.g., "Every Saturday at 08:30"). A series has a list of participants and serves as a template for creating fixtures
- **Fixture** - A specific tennis session on a particular date, created from a series template. Players indicate availability and court bookings for each fixture
- **Participant** - A user who is invited to a specific series or fixture

## Purpose of the app

This Tennis web app is designed to help groups of tennis players who meet regularly at a tennis club. The app enables players to indicate their availability, coordinate court bookings, and determine who can play based on the courts that were booked. It reduces the organizer's workload and provides a better experience with fewer errors.

The app supports:

**User Management:**

- Maintaining a list of users with email addresses
- Personal access links rather than passwords for authentication

**Series Management:**

- Creating multiple fixture series
- Each series defines recurring fixtures (day of week and time)
- Each series has its own list of participants

**Fixture Management:**

- Creating individual fixtures from a series template
- Each fixture inherits participants from its series
- Tracking who wants to play for each fixture
- Recording court bookings made by participants

**Communication:**

- Sending email invitations with personalized access links
- Enabling participants to self-report availability and court bookings

## Design

The design has evolved significantly. The backend now runs in Google App Engine (GAE) running PHP with a React front end single page application (SPA) with API calls to the backend.

The PHP application uses the [Free SQL Database](https://www.freesqldatabase.com/), which provides a basic MySQL service, which is sufficient for this app.

A [GAE cron job](https://docs.cloud.google.com/appengine/docs/flexible/scheduling-jobs-with-cron-yaml) runs at 7am each morning to e.g. send out automated emails.

The backend uses the [Slim framework](https://www.slimframework.com/), which uses a front controller design, which is required by the GAE PHP runtime.

Some legacy web pages and email creation uses the [Twig template engine for PHP](https://twig.symfony.com/), via the slim/twig-view component.

The frontend uses the [Pure CSS library](https://purecss.io/) and is designed to work well on both mobile and PC browsers.

The React SPA is a [separate but related project](https://github.com/cwd18/tennis-spa). The React build files are deployed as static files by the GAE backend.

## Authorization

The app uses personal tokens instead of passwords. These tokens are delivered as access links via email.

Each token is associated with one of three roles:

- **User** - can view fixtures and update their own availability
- **Owner** - can manage a specific fixture series
- **Admin** - has full system access

Users typically receive their tokens through automated emails (invitations and status updates).
