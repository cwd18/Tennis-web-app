
function BookingTable({name, fixtureid, userid}) {
    const [bookingData, setBookingData] = React.useState([]);
    React.useEffect(() => {
        fetch('/api/participantBookings/' + fixtureid + '/' + userid)
        .then(response => response.json())
        .then(setBookingData);
    }, []);
    const handleCourtChange = (event, index) => {
        const { value } = event.target;
        const resetIndex = index == 0 ? 1 : 0;
        const newBookingData = bookingData.map((item, i) => {
            if (i === index) {
                return { ...item, court: Number(value) };
            }
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
        });
    };

    return (
        <div>
            <p className="no-space-after"><b>{name} booked these courts:</b></p>
            <table className="pure-table">
                <thead>
                    <tr>
                    {bookingData.map((item, index) => (
                        <th key={index}>{item.time}</th>
                    ))}
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        {bookingData.map((item, index) => (
                            <td key={index}>
                                <select
                                    id={item.time}
                                    value={item.court}
                                    onChange={(event) => handleCourtChange(event, index)}
                                >
                                    {item.availableCourts.map((court, index) => (
                                        <option key={index} value={court}>
                                            {court == 0 ? 'No' : court}
                                        </option>
                                    ))}
                                </select>
                            </td>
                        ))}
                    </tr>
                </tbody>
            </table>
        </div>
    );
}