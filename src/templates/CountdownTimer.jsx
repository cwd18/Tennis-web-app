function CountdownTimer({label, targetDateStr}) {
    const [remainingTime, setRemainingTime] = React.useState(0);
    const targetDateObj = new Date(targetDateStr);
    const targetDate = targetDateObj.getTime();
    React.useEffect(() => {
        const intervalId = setInterval(() => {
            const currentTime = new Date().getTime();
            setRemainingTime(targetDate - currentTime);
        }, 1000); 
      
        return () => clearInterval(intervalId); // Cleanup on unmount
        }, [targetDateStr]); 

    const days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
    const hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

    if (remainingTime <= 0) {
        return null;}
        
    return (
        <div>
        {label}{days}d {hours}h {minutes}m {seconds}s
        </div>
    );        
}