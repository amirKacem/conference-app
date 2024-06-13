import React from 'react';
import { createBrowserRouter } from 'react-router-dom';
import Home from './components/Home';
import Conference from './components/Conference';
import { getConferences } from './api/conference';

export const router = createBrowserRouter([
    {
        path: '/',
        element: <Home />,
        loader: getConferences,
    },
    {
        path: '/conference/:slug',
        element: <Conference />,
        loader: getConferences,
    },
]);
