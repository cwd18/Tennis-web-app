function BookingRequests({ bookingRequests, bookingDate }) {
    if (typeof bookingRequests === 'undefined') {
        return null;
    }
    return (
        <div>
            <p className="no-space-after"><b>Court booking requests</b></p>
            <CountdownTimer label='Time to book: ' targetDateStr={bookingDate + ' 07:30'} />
            <table className="pure-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Court</th>
                        <th>Booker</th>
                    </tr>
                </thead>
                <tbody>
                    {bookingRequests.map((item, index) => (
                        <tr key={index}>
                            <td>{item.ShortName}</td>
                            <td>{item.CourtNumber}</td>
                            <td>{item.BookingTime}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

