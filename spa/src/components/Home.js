import React from 'react';
import Header from './Header';
import { useLoaderData } from 'react-router';
import { Link } from 'react-router-dom';

const Home = () => {
    const conferences = useLoaderData();
    return (
        <div>
            <Header />
            <div className="p-3">
                {conferences.map((conference) => (
                    <div
                        key={conference.id}
                        className="card border shadow-sm lift mb-3"
                    >
                        <div className="card-body">
                            <div className="card-title">
                                <h4 className="font-weight-light">
                                    {conference.city} {conference.year}
                                </h4>
                            </div>

                            <Link
                                className="btn btn-sm btn-primary stretched-link"
                                to={'/conference/' + conference.slug}
                            >
                                View
                            </Link>
                        </div>{' '}
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Home;
