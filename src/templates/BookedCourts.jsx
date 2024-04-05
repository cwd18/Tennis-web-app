function BookedCourts({ bookings }) {
    if (!bookings || bookings.length === 0) {
        return null;
    }
    const [header, ...rows] = bookings;   
    return (
    <div>
        <p className="no-space-after"><b>Booked courts...</b></p>
        <table className="pure-table">
            <thead><tr>
            {header.map((cell, i) => (
                <th key={i}>{cell}</th>
            ))}
            </tr></thead>
            <tbody>
            {rows.map((row, index) => (
                <tr key={index}>
                    {row.map((cell, index) => (
                        <td key={index}>{cell}</td>
                    ))}
                </tr>
            ))}
            </tbody>
        </table>
    </div>
    );
}