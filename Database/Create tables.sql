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
SeriesCourts varchar (100) NOT NULL DEFAULT '1-17'
PRIMARY KEY (Seriesid),
FOREIGN KEY (SeriesOwner) REFERENCES Users(Userid)
);
CREATE TABLE SeriesCandidates (
Seriesid INT(8) NOT NULL,
Userid INT (8) NOT NULL,
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE TABLE Fixtures (
Fixtureid INT(8) NOT NULL auto_increment,
Seriesid INT(8) NOT NULL,
FixtureOwner INT(8) NOT NULL,
FixtureDate DATE NOT NULL,
FixtureTime TIME NOT NULL,
FixtureCourts varchar (100) NOT NULL DEFAULT '1-17'
FixtureDuration INT(3) NOT NULL DEFAULT 2,
PRIMARY KEY (Fixtureid),
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (FixtureOwner) REFERENCES Users(Userid)
);
CREATE TABLE CourtBookings (
Fixtureid INT(8) NOT NULL,
BookingTime TIME NOT NULL,
CourtNumber INT(3) NOT NULL,
Userid INT(8) NOT NULL,
PRIMARY KEY (Fixtureid, BookingTime, CourtNumber),
FOREIGN KEY (Fixtureid) REFERENCES Fixtures(Fixtureid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE TABLE FixtureParticipants (
Fixtureid INT(8),
Userid INT(8),
WantsToPlay BOOLEAN,
AcceptTime DATETIME DEFAULT NULL,
IsPlaying BOOLEAN NOT NULL DEFAULT FALSE,
FOREIGN KEY (Fixtureid) REFERENCES Fixtures(Fixtureid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
