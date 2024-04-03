function WantsToPlay({name}) {
    const [wantsToPlay, setWantsToPlay] = React.useState('Unknown');
    React.useEffect(() => {
        fetch('/api/participantWantsToPlay/{{ fixtureid }}/{{ userid }}')
        .then(response => response.text())
        .then(response => setWantsToPlay(response));
    }, []);
    const handleWantsToPlayChange = (event) => {
        const { value } = event.target;
        setWantsToPlay(value);
        const url = '/api/participantWantsToPlay/{{ fixtureid }}/{{ userid }}/' + (value == 'No' ? '0' : '1');
        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
        });
    };
return (
    <div>
        <b>{name} wants to play: </b>
        <select
            id={"wantsToPlay"}
            value={wantsToPlay}
            onChange={(event) => handleWantsToPlayChange(event)}
        >
        <option key='0' value='No'>No</option>
        <option key='1' value='Yes'>Yes</option>
        {(wantsToPlay == 'Unknown') && <option key='2' value='Unknown'>Unknown</option>}
        </select>
    </div>
  );
}