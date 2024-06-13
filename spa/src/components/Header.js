import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getConferences } from '../api/conference';

function Header() {
    const [conferences, setConferences] = useState(null);
    useEffect(() => {
        getConferences().then((conferences) => {
            setConferences(conferences);
        });
    }, []);

    return conferences === null ? (
        <div className="text-center pt-5">Loading...</div>
    ) : (
        <>
            <header className="header">
                <nav className="navbar navbar-light bg-light">
                    <div className="container">
                        <Link className="navbar-brand mr-4 pr-2" to="/">
                            &#128217; Guestbook
                        </Link>
                    </div>
                </nav>

                <nav className="bg-light border-bottom text-center">
                    {conferences.map((conference) => (
                        <Link
                            className="nav-conference"
                            key={conference.id}
                            to={'/conference/' + conference.slug}
                        >
                            {conference.city} {conference.year}
                        </Link>
                    ))}
                </nav>
            </header>
        </>
    );
}

export default Header;
