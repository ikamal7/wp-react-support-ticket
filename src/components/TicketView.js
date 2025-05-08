import { useState, useEffect } from '@wordpress/element';
import { useParams, useNavigate } from 'react-router';
import { Button, TextareaControl, Spinner, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

function TicketView() {
    const { ticketId } = useParams();
    const navigate = useNavigate();
    const [ticket, setTicket] = useState(null);
    const [replies, setReplies] = useState([]);
    const [newReply, setNewReply] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [notice, setNotice] = useState(null);

    useEffect(() => {
        loadTicketDetails();
    }, [ticketId]);

    const loadTicketDetails = async () => {
        try {
            const [ticketData, repliesData] = await Promise.all([
                apiFetch({ path: `/wp-support-ticket/v1/tickets/${ticketId}` }),
                apiFetch({ path: `/wp-support-ticket/v1/tickets/${ticketId}/replies` })
            ]);
            setTicket(ticketData);
            setReplies(repliesData);
            setIsLoading(false);
        } catch (error) {
            setError('Error loading ticket details');
            setIsLoading(false);
        }
    };

    const handleSubmitReply = async () => {
        if (!newReply.trim()) return;

        try {
            await apiFetch({
                path: `/wp-support-ticket/v1/tickets/${ticketId}/replies`,
                method: 'POST',
                data: { message: newReply }
            });
            setNewReply('');
            loadTicketDetails();
            setNotice({ status: 'success', message: 'Reply added successfully' });
        } catch (error) {
            setNotice({ status: 'error', message: 'Error adding reply' });
        }
    };

    const handleCloseTicket = async () => {
        try {
            await apiFetch({
                path: `/wp-support-ticket/v1/tickets/${ticketId}`,
                method: 'PATCH',
                data: { status: 'closed' }
            });
            loadTicketDetails();
            setNotice({ status: 'success', message: 'Ticket closed successfully' });
        } catch (error) {
            setNotice({ status: 'error', message: 'Error closing ticket' });
        }
    };

    if (isLoading) return <Spinner />;
    if (error) return <div className="wpst-error">{error}</div>;
    if (!ticket) return <div>Ticket not found</div>;

    return (
        <div className="wpst-ticket-view">
            <div className="wpst-ticket-header">
                <Button
                    isSecondary
                    onClick={() => navigate(-1)}
                    className="wpst-back-button"
                >
                    Back to List
                </Button>
                <h2>Ticket #{ticket.id}: {ticket.subject}</h2>
            </div>

            {notice && (
                <Notice
                    status={notice.status}
                    onRemove={() => setNotice(null)}
                    className="wpst-notice"
                >
                    {notice.message}
                </Notice>
            )}

            <div className="wpst-ticket-details">
                <p><strong>Status:</strong> {ticket.status}</p>
                <p><strong>Priority:</strong> {ticket.priority}</p>
                <p><strong>Department:</strong> {ticket.department}</p>
                <div className="wpst-ticket-message">
                    <strong>Message:</strong>
                    <p>{ticket.message}</p>
                </div>
            </div>

            <div className="wpst-ticket-actions">
                {ticket.status !== 'closed' && (
                    <Button
                        isPrimary
                        onClick={handleCloseTicket}
                        className="wpst-close-button"
                    >
                        Close Ticket
                    </Button>
                )}
            </div>

            <div className="wpst-replies">
                <h3>Replies</h3>
                {replies.map(reply => (
                    <div key={reply.id} className="wpst-reply">
                        <p>{reply.message}</p>
                        <small>Posted on {new Date(reply.created_at).toLocaleString()}</small>
                    </div>
                ))}
            </div>

            {ticket.status !== 'closed' && (
                <div className="wpst-reply-form">
                    <TextareaControl
                        label="Add Reply"
                        value={newReply}
                        onChange={setNewReply}
                        rows={4}
                    />
                    <Button
                        isPrimary
                        onClick={handleSubmitReply}
                        disabled={!newReply.trim()}
                    >
                        Submit Reply
                    </Button>
                </div>
            )}

            <style>{`
                .wpst-ticket-view {
                    padding: 20px;
                }
                .wpst-ticket-header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                    gap: 20px;
                }
                .wpst-ticket-details {
                    background: #fff;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin-bottom: 20px;
                }
                .wpst-ticket-message {
                    margin-top: 15px;
                }
                .wpst-replies {
                    margin-top: 30px;
                }
                .wpst-reply {
                    background: #f9f9f9;
                    padding: 15px;
                    border: 1px solid #eee;
                    border-radius: 4px;
                    margin-bottom: 10px;
                }
                .wpst-reply small {
                    color: #666;
                    display: block;
                    margin-top: 5px;
                }
                .wpst-reply-form {
                    margin-top: 30px;
                }
                .wpst-notice {
                    margin: 20px 0;
                }
                .wpst-ticket-actions {
                    margin: 20px 0;
                }
            `}</style>
        </div>
    );
}

export default TicketView;