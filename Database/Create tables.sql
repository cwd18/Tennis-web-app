-- Tennis web app schema
USE Tennis;
CREATE TABLE Users (
Userid int(8) NOT NULL auto_increment,
LastName varchar (50) NOT NULL,
FirstName varchar (50) NOT NULL,
EmailAddress varchar (50) NOT NULL unique,
PRIMARY KEY (Userid)
);
CREATE TABLE FixtureSeries (
Seriesid INT(8) NOT NULL auto_increment,
SeriesOwner INT(8) NOT NULL,
SeriesWeekday INT(8) NOT NULL,
SeriesTime TIME NOT NULL DEFAULT '07:30',
SeriesDuration INT(3) NOT NULL DEFAULT 2,
SeriesCourts varchar (100) NOT NULL DEFAULT '1-17',
TargetCourts varchar (100) NOT NULL DEFAULT '9-12',
AutoEmail BOOLEAN DEFAULT FALSE,
PRIMARY KEY (Seriesid),
FOREIGN KEY (SeriesOwner) REFERENCES Users(Userid)
);
CREATE TABLE SeriesCandidates (
Seriesid INT(8) NOT NULL,
Userid INT (8) NOT NULL,
PRIMARY KEY (Seriesid, Userid),
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE TABLE Fixtures (
Fixtureid INT(8) NOT NULL auto_increment,
Seriesid INT(8) NOT NULL,
FixtureOwner INT(8) NOT NULL,
FixtureDate DATE NOT NULL,
FixtureTime TIME NOT NULL,
FixtureCourts varchar (100) NOT NULL DEFAULT '1-12, 13-15',
TargetCourts varchar (100) NOT NULL DEFAULT '9-12',
FixtureDuration INT(3) NOT NULL DEFAULT 2,
InvitationsSent BOOLEAN NOT NULL DEFAULT FALSE,
PRIMARY KEY (Fixtureid),
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (FixtureOwner) REFERENCES Users(Userid)
);
CREATE TABLE CourtBookings (
Fixtureid INT(8) NOT NULL,
BookingTime TIME NOT NULL,
CourtNumber INT(3) NOT NULL,
BookingType ENUM ('Booked', 'Request') NOT NULL DEFAULT 'Booked',
Userid INT(8) NOT NULL,
PRIMARY KEY (Fixtureid, BookingTime, CourtNumber, BookingType),
FOREIGN KEY (Fixtureid, UserId) REFERENCES FixtureParticipants(Fixtureid, UserId)
);
CREATE TABLE FixtureParticipants (
Fixtureid INT(8),
Userid INT(8),
WantsToPlay BOOLEAN,
AcceptTime DATETIME DEFAULT NULL,
CourtsBooked  TINYINT DEFAULT NULL,
IsPlaying BOOLEAN NOT NULL DEFAULT FALSE,
PRIMARY KEY (Fixtureid, Userid),
FOREIGN KEY (Fixtureid) REFERENCES Fixtures(Fixtureid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE TABLE Tokens (
Token char (32),
Userid INT(8) NOT NULL,
TokenClass ENUM ('User', 'Owner', 'Admin', 'Auto'),
OtherId INT(8),
Expires DATETIME DEFAULT NULL,
PRIMARY KEY (Token),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE TABLE SessionData (
Sessionid varchar(255) COLLATE utf8_unicode_ci NOT NULL,
SessionExpires datetime NOT NULL,
SessionData varchar(1024) COLLATE utf8_unicode_ci,
PRIMARY KEY (Sessionid)
);
CREATE TABLE EventLog (
Seq INT(8) NOT NULL auto_increment,
EventTime DATETIME DEFAULT NULL,
EventMessage varchar(1024),
PRIMARY KEY (Seq)
);
