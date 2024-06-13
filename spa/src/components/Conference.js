import React, { useEffect, useState } from 'react';
import Header from './Header';
import { getComments } from '../api/comments';
import { useLoaderData, useParams } from 'react-router';
import Comment from './Comment';

const Conference = () => {
    const { slug } = useParams();
    const conferences = useLoaderData();
    const conference = conferences.find(
        (conference) => conference.slug === slug
    );
    const [comments, setComments] = useState(null);

    useEffect(() => {
        getComments(conference).then((comments) => setComments(comments));
    }, [slug]);

    return (
        <div>
            <Header />
            <div className="p-3">
                <h4>
                    {conference.city} {conference.year}
                </h4>
                <Comment comments={comments} />
            </div>
        </div>
    );
};

export default Conference;
