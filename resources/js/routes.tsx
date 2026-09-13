import { createBrowserRouter } from 'react-router-dom';
import Home from './pages/Home/Home';
import Login from './pages/Account/Login'
import Register from './pages/Account/Register'
import Account from './pages/Account/Account'
import ForgotPassword from './pages/Account/ForgotPassword'
import ResetPassword from './pages/Account/ResetPassword'
import UpdateProfile from "./pages/Account/UpdateProfile";
import UpdatePassword from "./pages/Account/UpdatePassword.tsx";
import Tui from './pages/Dev/Tui'
import Help from './pages/Home/Help'



export const router = createBrowserRouter([
    { path: '/', element: <Home /> },
    { path: '/login', element: <Login /> },
    { path: '/register', element: <Register /> },
    { path: '/account', element: <Account /> },
    { path: '/forgot-password', element: <ForgotPassword /> },
    { path: '/reset-password', element: <ResetPassword /> },
    { path: '/update-profile', element: <UpdateProfile /> },
    { path: '/update-password', element: <UpdatePassword /> },
    { path: '/tui', element: <Tui/>},
    { path: '/help', element: <Help/>},
]);
