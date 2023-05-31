-- Tennis web app schema
USE Tennis;
CREATE OR REPLACE TABLE Users (
Userid int(8) NOT NULL auto_increment,
LastName varchar (50) NOT NULL,
FirstName varchar (50) NOT NULL,
EmailAddress varchar (50) NOT NULL unique,
PRIMARY KEY (Userid)
);
CREATE OR REPLACE TABLE FixtureSeries (
Seriesid INT(8) NOT NULL auto_increment,
SeriesOwner INT(8) NOT NULL,
SeriesWeekday INT(8) NOT NULL,
SeriesTime TIME NOT NULL DEFAULT '07:30',
SeriesDuration INT(3) NOT NULL DEFAULT 2,
PRIMARY KEY (Seriesid),
FOREIGN KEY (SeriesOwner) REFERENCES Users(Userid)
);
CREATE OR REPLACE TABLE SeriesCandidates (
Seriesid INT(8) NOT NULL,
Userid INT (8) NOT NULL,
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE OR REPLACE TABLE Fixtures (
Fixtureid INT(8) NOT NULL auto_increment,
Seriesid INT(8) NOT NULL,
FixtureOwner INT(8) NOT NULL,
FixtureDate DATE NOT NULL,
FixtureTime TIME NOT NULL,
FixtureDuration INT(3) NOT NULL DEFAULT 2,
PRIMARY KEY (Fixtureid),
FOREIGN KEY (Seriesid) REFERENCES FixtureSeries(Seriesid),
FOREIGN KEY (FixtureOwner) REFERENCES Users(Userid)
);
CREATE OR REPLACE TABLE CourtBookings (
Fixtureid INT(8) NOT NULL,
Userid INT(8) NOT NULL,
BookingTime TIME NOT NULL,
CourtNumber INT(3) NOT NULL,
PRIMARY KEY (Fixtureid, Userid, BookingTime, CourtNumber),
FOREIGN KEY (Fixtureid) REFERENCES Fixtures(Fixtureid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
CREATE OR REPLACE TABLE FixtureParticipants (
Fixtureid INT(8),
Userid INT(8),
WantsToPlay BOOLEAN NOT NULL DEFAULT FALSE,
RequestTime TIMESTAMP,
IsPlaying BOOLEAN NOT NULL DEFAULT FALSE,
FOREIGN KEY (Fixtureid) REFERENCES Fixtures(Fixtureid),
FOREIGN KEY (Userid) REFERENCES Users(Userid)
);
