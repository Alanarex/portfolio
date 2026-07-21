import React from 'react';
import { createRoot } from 'react-dom/client';
import { QueryClient } from '@tanstack/react-query';
import { createRouter, RouterProvider } from '@tanstack/react-router';
import { routeTree } from './portfolio/routeTree.gen';

const queryClient = new QueryClient();
const router = createRouter({ routeTree, context: { queryClient } });

declare module '@tanstack/react-router' {
  interface Register { router: typeof router }
}

const element = document.getElementById('portfolio-app');
if (!element) throw new Error('Missing #portfolio-app mount point');
createRoot(element).render(<React.StrictMode><RouterProvider router={router} /></React.StrictMode>);
