import React from 'react';
import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import { router } from './routes';
import {AuthProvider} from "./contexts/AuthContext.tsx";
import {LoadingProvider} from "./contexts/LoadingContext.tsx";

createRoot(document.getElementById('root')!).render(
    <LoadingProvider>
        <AuthProvider>
            <RouterProvider router={router} />
        </AuthProvider>
    </LoadingProvider>
);
