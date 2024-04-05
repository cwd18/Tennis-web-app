function UserInput({fixtureid, userid}) {
    const [wantsToPlay, setWantsToPlay] = React.useState();
    const [participantData, setParticipantData] = React.useState({});
    const [playerLists, setPlayerLists] = React.useState([]);
    const getPlayerLists = () => {
        fetch('/api/playerLists/' + fixtureid)
        .then(response => response.json())
        .then(response => {
            setPlayerLists(response);
        });
    }
    React.useEffect(() => {
        fetch('/api/participantData/' + fixtureid +'/' + userid)
        .then(response => response.json())
        .then(response => {
            setParticipantData(response);
            setWantsToPlay(response.wantsToPlay);
        });
        getPlayerLists();
    }, []);

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
            < WantsToPlay 
            name={FirstName} 
            wantsToPlay={wantsToPlay}
            handleWantsToPlayChange={handleWantsToPlayChange} />

            {(inBookingWindow == 0) && < BookingTable 
            name={FirstName} 
            fixtureid={fixtureid}
            userid={userid} />}

            <PlayerList players={playerLists.players} label="Playing" />
            <PlayerList players={playerLists.reserves} label="Wants to play" />
            <PlayerList players={playerLists.decliners} label="Can't play" />
        </div>
    );
}
