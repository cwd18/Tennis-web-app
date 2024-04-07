function UserInput({fixtureid, userid}) {
    const [wantsToPlay, setWantsToPlay] = React.useState();
    const [participantData, setParticipantData] = React.useState({});
    const [playerLists, setPlayerLists] = React.useState([]);
    const [bookings, setBookings] = React.useState([]);
    const [bookingData, setBookingData] = React.useState([]);
    const getUserBookingTable = () => {
        fetch('/api/participantBookings/' + fixtureid + '/' + userid)
        .then(response => response.json())
        .then(response => setBookingData(response));
    }
    const getPlayerLists = () => {
        fetch('/api/playerLists/' + fixtureid, {
            headers: {
              'Cache-Control': 'no-cache'
            }
          })
        .then(response => response.json())
        .then(response => setPlayerLists(response));
    }
    const getBookingViewGrid = () => {
        fetch('/api/bookingViewGrid/' + fixtureid, {
            headers: {
              'Cache-Control': 'no-cache'
            }
          })
        .then(response => response.json())
        .then(response => setBookings(response));
    }
    React.useEffect(() => {
        fetch('/api/participantData/' + fixtureid +'/' + userid, {
            headers: {
              'Cache-Control': 'no-cache'
            }
          })
        .then(response => response.json())
        .then(response => {
            setParticipantData(response);
            setWantsToPlay(response.wantsToPlay);
        });
        getUserBookingTable();
        getPlayerLists();
        getBookingViewGrid();
    }, []);

    const handleCourtChange = (event, index) => {
        const { value } = event.target;
        const resetIndex = index == 0 ? 1 : 0;
        const newBookingData = bookingData.map((item, i) => {
            if (i === index) {
                return { ...item, court: Number(value) };}
            return item;
        });
        const numCourtsSet = newBookingData.filter(item => item.court != 0).length;
        if (numCourtsSet > 2) {
            newBookingData[resetIndex].court = 0;
        }
        setBookingData(newBookingData);
        const bookings = newBookingData.map(item => ({ time: item.time, court: item.court }));
        fetch('/api/participantBookings/' + fixtureid + '/' + userid, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(bookings),
        })
        .then(() => getBookingViewGrid());
    };

    const handleWantsToPlayChange = (value) => {
        setWantsToPlay(value);
        fetch('/api/participantWantsToPlay/' + fixtureid +'/' + userid
             +'/' + (value == 'No' ? '0' : '1'), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
        })
        .then(getPlayerLists());
    };

    const {inBookingWindow, FirstName} = participantData;
    if (wantsToPlay === undefined) return null;
    return (
        <div>
            <WantsToPlay 
            name={FirstName} 
            wantsToPlay={wantsToPlay}
            handleWantsToPlayChange={handleWantsToPlayChange} />

            {(inBookingWindow == 0) && <UserBookingTable 
            name={FirstName} 
            bookingData={bookingData}
            handleCourtChange={handleCourtChange} />}

            <PlayerList players={playerLists.players} label="Playing" />
            <PlayerList players={playerLists.reserves} label="Wants to play" />
            <PlayerList players={playerLists.decliners} label="Can't play" />

            {(inBookingWindow == 0) && <BookedCourts bookings={bookings} />}
        </div>
    );
}
