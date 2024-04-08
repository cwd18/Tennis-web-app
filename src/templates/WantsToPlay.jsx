function WantsToPlay({name, wantsToPlay, handleWantsToPlayChange}) {
    if (wantsToPlay == 'Unknown') {
        return (
            <div>
                <p className="no-space-after"><b>{name} has not yet responded</b></p>
                <button className="pure-button button-margin-right" 
                onClick={() => handleWantsToPlayChange('Yes')}
                >Wants to play</button>
                <button className="pure-button"
                onClick={() => handleWantsToPlayChange('No')}
                >Cannot play</button>
            </div>
        );
        }
    return (
        <div>
            <p className="no-space-after"><b>{name} {wantsToPlay == 'Yes' ? 'wants to play' : "can't play"}</b></p>
            <button className="pure-button"
            onClick={() => handleWantsToPlayChange(wantsToPlay == 'Yes' ? 'No' : 'Yes')}
            >{wantsToPlay == 'Yes' ? 'Cannot now play' : 'Would now like to play'}</button>
        </div>
);
}
