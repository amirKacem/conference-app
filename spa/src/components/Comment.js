import React from 'react';

function Comment({ comments }) {
    if (comments !== null && comments.length === 0) {
        return <div className="text-center pt-4">No comments yet</div>;
    }

    if (!comments) {
        return <div className="text-center pt-4">Loading...</div>;
    }

    return (
        <div className="pt-4">
            {comments.map((comment) => (
                <div
                    key={comment.id}
                    className="shadow border rounded-3 p-3 mb-4"
                >
                    <div className="comment-img mr-3">
                        {!comment.photoFilename ? (
                            ''
                        ) : (
                            <a
                                href={
                                    ENV_API_ENDPOINT +
                                    'uploads/photos/' +
                                    comment.photoFilename
                                }
                                target="_blank"
                            >
                                <img
                                    src={
                                        ENV_API_ENDPOINT +
                                        'uploads/photos/' +
                                        comment.photoFilename
                                    }
                                />
                            </a>
                        )}
                    </div>

                    <h5 className="font-weight-light mt-3 mb-0">
                        {comment.author}
                    </h5>
                    <div className="comment-text">{comment.text}</div>
                </div>
            ))}
        </div>
    );
}

export default Comment;
