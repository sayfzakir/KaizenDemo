import React from 'react'; // Assuming you're using React Router for navigation
import './Sidebar.css'; // Ensure you have some basic styles defined
import sidebarImage from './assets/kaizen patient.JPG';

function Sidebar() {
    return (
        <div className="sidebar">
            <img src={sidebarImage} alt="Sidebar Visual" className='sidebar-image'/>
            <div className="sidebar-link">My Dashboard</div>
            <div className="sidebar-link">My Profile</div>
            <div className="sidebar-link">My Vitals</div>
            <div className="sidebar-link">My Health Record</div>
        </div>
    );
}

export default Sidebar;

