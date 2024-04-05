
function UserBookingTable({name, bookingData, handleCourtChange}) {
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