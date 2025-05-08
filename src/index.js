import { render } from '@wordpress/element';
import { Panel, PanelBody, SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { BrowserRouter, Routes, Route } from 'react-router';
import TicketList from './components/TicketList';
import TicketView from './components/TicketView';
import './styles.css';

function App() {
    const [status, setStatus] = useState('');
    const [priority, setPriority] = useState('');
    const [department, setDepartment] = useState('');
    return (
        <BrowserRouter basename="/wp-admin/admin.php">
            <div className="wpst-admin-panel">
                <div className="wpst-admin-header">
                    <h1>WP Support Ticket System</h1>
                    <p className="wpst-header-description">Manage and respond to support tickets efficiently</p>
                </div>
                <div className="wpst-main-panel">
                    <div className="wpst-tickets-panel">
                        <div className="wpst-panel-content">
                            <Routes>
                                <Route
                                    path="/"
                                    element={
                                        <>
                                            <TicketList 
                                                filters={{ status, priority, department }}
                                            />
                                        </>
                                    }
                                />
                                <Route path="/ticket/:ticketId" element={<TicketView />} />
                            </Routes>
                        </div>
                    </div>
                </div>
            </div>
        </BrowserRouter>
    );
}

const domElement = document.getElementById('wpst-admin');
if (domElement) {
    render(<App />, domElement);
}