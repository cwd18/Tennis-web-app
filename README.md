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

## History

The web app was first deployed for Friday 12th April 2024, when participants got an email asking them if they wanted to play on Saturday the 20th. I had been testing the application by entering data from the emails in reply to all in response to manual emails sent out by Lawrence.

I sent an email to announce the system on the 11th.

In the event, the system failed spectacularly owing to a rare 6-hour outage in the unpkg.com service that provided the react libraries used by the front end, which was resolved by switching to cloudflare.com.

The code is still there for the legacy web version of the app (as opposed to the React-based SPA).

Here is the text of the email sent on the 11th:

All

TL;DR: you should get an email from me tomorrow at 7:30 asking you whether you want to play on Saturday 20th with a personal link you should follow to respond

For more info...

Starting with Saturday 20th, I will organise our Saturday morning tennis while Lawrence gets his feet fixed

As some of you know, I will be using a web application I have developed as a retirement project to "do some coding"
Lawrence and Roger have been helping me to test the system and giving me feedback to improve

The application is aimed at making life easier for you and for me as organiser. The benefits for you are:

- Easier for you to say whether you want to play or not (two clicks)
- Easier for you to say what courts you booked
- Easy access to up-to-date information on the start time, who wants to play, what courts have been booked, etc.
- Fewer errors as much is automated

At the usual time of 7am tomorrow, you will get a “wanna play” email. Rather than from Lawrence, it will come from my gmail account (FYI it’s generated automatically)
The email has a personal link which will take you to a personal web page that has two buttons to indicate whether you want to play or not.
That same page also provides information such as who else wants to play and who doesn’t.

Your reply (via selecting one of the two buttons) will be time-stamped, which impacts the position of your name in the list (as Lawrence has always done).
People who booked a court come before people who didn’t (as Lawrence has also always done).
The order becomes significant if we don’t have enough courts or if there is an odd number of people wanting to play.

At 7am on Saturday, people who have booked in the past will get an email asking them to book a court and with a list of who should try to book which court
This email has the same personal link, which you should use to record any courts you booked at 7:30

You shouldn't need to reply to these emails but you can, especially to report any problem or with a suggestion to improve

Anyhow, the basic change is that you select the link to respond rather than reply to the email

Fingers crossed that it all works!

Charles
