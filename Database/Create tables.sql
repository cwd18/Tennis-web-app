-- Tennis web app schema
USE Tennis;
CREATE TABLE Users (
Userid int(8) not null auto_increment,
LastName varchar (50) not null,
FirstName varchar (50) not null,
EmailAddress varchar (50) not null unique,
PRIMARY KEY (Userid)
);
CREATE TABLE FixtureSeries (
Seriesid INT(8) not null auto_increment,
SeriesName varchar (50) not null,
SeriesWeekday INT(8) not null,
SeriesTime TIME not null,
SeriesDuration INT(3) not null DEFAULT 2,
PRIMARY KEY (Seriesid)
);
CREATE TABLE SeriesCandidates (
Seriesid INT(8) not null,
Userid INT (8) not null,
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE TABLE Fixtures (
Fixtureid INT(8) not null auto_increment,
Seriesid INT(8) not null,
FixtureDate DATE not null,
FixtureTime TIME not null,
FixtureDuration INT(3) not null DEFAULT 2,
PRIMARY KEY (Fixtureid),
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid)
);
CREATE TABLE CourtBookings (
Fixtureid INT(8) not null,
Userid INT(8) not null,
CourtNumber INT(3) not null,
BookingSlot INT(3) not null,
FOREIGN KEY (Fixtureid) REFERENCES Fixtures(Fixtureid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE TABLE FixtureParticipants (
Fixtureid INT(8),
Userid INT(8),
RequestTime TIMESTAMP,
FOREIGN KEY (Fixtureid) REFERENCES Fixtures(Fixtureid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
