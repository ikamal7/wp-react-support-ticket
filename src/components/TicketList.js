import { useState, useEffect } from '@wordpress/element';
import { Table, Spinner, Button } from '@wordpress/components';
import { useNavigate } from 'react-router';
import apiFetch from '@wordpress/api-fetch';

function TicketList() {
    const navigate = useNavigate();
    const [tickets, setTickets] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        let isMounted = true;

        const loadTickets = async () => {
            try {
                const response = await apiFetch({
                    path: '/wp-support-ticket/v1/tickets',
                    method: 'GET'
                });
                if (isMounted) {
                    setTickets(response || []);
                    setIsLoading(false);
                }
            } catch (error) {
                console.error('Error loading tickets:', error);
                if (isMounted) {
                    setError(error);
                    setIsLoading(false);
                }
            }
        };

        loadTickets();

        return () => {
            isMounted = false;
        };
    }, []);

    if (isLoading) {
        return <Spinner />;
    }

    return (
        <div className="wpst-ticket-list">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {tickets.map(ticket => (
                        <tr key={ticket.id}>
                            <td>{ticket.id}</td>
                            <td>{ticket.subject}</td>
                            <td>{ticket.department}</td>
                            <td>{ticket.priority}</td>
                            <td>{ticket.status}</td>
                            <td>
                                <Button
                                    isSecondary
                                    onClick={() => navigate(`/ticket/${ticket.id}`)}
                                >
                                    View
                                </Button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export default TicketList;