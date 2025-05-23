-- Tennis web app schema
USE Tennis;
CREATE TABLE Users (
Userid int(8) NOT NULL auto_increment,
LastName varchar (50) NOT NULL,
FirstName varchar (50) NOT NULL,
EmailAddress varchar (50) NOT NULL unique,
ShortName varchar (20),
Booker BOOLEAN DEFAULT FALSE,
PRIMARY KEY (Userid)
);
CREATE TABLE FixtureSeries (
Seriesid INT(8) NOT NULL auto_increment,
SeriesOwner INT(8) NOT NULL,
SeriesWeekday INT(8) NOT NULL,
SeriesTime TIME NOT NULL DEFAULT '07:30',
SeriesAltTimeIndex INT(8) NOT NULL DEFAULT 0, -- 0 for no alt time, -1 for an earlier alt time, 1 for a later alt time
SeriesDuration INT(3) NOT NULL DEFAULT 2,
SeriesCourts varchar (100) NOT NULL DEFAULT '1-26',
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
FixtureCourts varchar (100) NOT NULL DEFAULT '1-26',
TargetCourts varchar (100) NOT NULL DEFAULT '9-12',
FixtureAltTimeIndex INT(3) NOT NULL DEFAULT 0,
PRIMARY KEY (Fixtureid),
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (FixtureOwner) REFERENCES Users(Userid)
);
CREATE TABLE CourtBookings (
Fixtureid INT(8) NOT NULL,
BookingTime TIME NOT NULL,
CourtNumber INT(3) NOT NULL,
BookingType ENUM ('Booked', 'Request', 'Cancel') NOT NULL DEFAULT 'Booked',
Userid INT(8) NOT NULL,
PRIMARY KEY (Fixtureid, BookingTime, CourtNumber, BookingType),
FOREIGN KEY (Fixtureid, UserId) REFERENCES FixtureParticipants(Fixtureid, UserId)
);
CREATE TABLE FixtureParticipants (
Fixtureid INT(8),
Userid INT(8),
WantsToPlay BOOLEAN,
AcceptTime DATETIME DEFAULT NULL,
CourtsBooked  TINYINT DEFAULT 0,
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
Created DATE DEFAULT NULL,
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
CREATE TABLE SessionLog (
LogId INT(8) NOT NULL auto_increment,
Sessionid varchar(255) COLLATE utf8_unicode_ci NOT NULL,
LogTime DATETIME NOT NULL,
LogType ENUM ('Read', 'Write', 'Destroy'),
LogMessage varchar(1024),
PRIMARY KEY (LogId)
);
CREATE TABLE FeatureFlags (
FeatureName VARCHAR(100) NOT NULL,
FeatureEnabled BOOLEAN NOT NULL DEFAULT FALSE,
FeatureDescription TEXT,
PRIMARY KEY (FeatureName)
);