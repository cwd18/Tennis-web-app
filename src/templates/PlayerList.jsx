function PlayerList({ players, label }) {
    if (!players || players.length === 0) {
        return null;
    }   
    return (
        <div>
            <p className="no-space-after"><b>{label}:</b></p>
            <ol className="lno-space">
                {players.map((player) => (
                    <li key={player.Userid}>{player.ShortName}</li>
                ))}
            </ol>
        </div>
    );
}