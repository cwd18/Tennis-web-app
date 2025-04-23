-- This SQL script retrieves the names of users and their acceptance times 
-- for fixtures on a specific date
SELECT u.ShortName, fp.AcceptTime 
FROM Users u, FixtureParticipants fp, Fixtures f 
WHERE f.FixtureDate = "2025-02-22"
AND u.Userid = fp.Userid AND fp.Fixtureid = f.Fixtureid AND fp.AcceptTime IS NOT null
ORDER BY fp.AcceptTime ASC ;