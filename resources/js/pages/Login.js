import React, { useEffect } from "react";
import { Link, useNavigate } from "react-router";
import { useAuth } from "../contexts/AuthContext";
import api from "../lib/axios.ts";
import { isAuthenticated } from "../models/interfaces/User.ts";
import { useLoading } from "../contexts/LoadingContext.tsx";
import { useSearchParams } from "react-router-dom";
import { Form } from "../components/Form.tsx";
export default function Login() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const { user, refreshAuth } = useAuth();
    const { setIsLoading } = useLoading();
    const [params] = useSearchParams();
    useEffect(() => {
        if (isAuthenticated(user)) {
            navigate("/");
        }
    }, [isAuthenticated(user), navigate]);
    //https://react.dev/reference/react-dom/components/form
    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        let formData = new FormData(e.currentTarget);
        return api.post("/api/v1/login", formData)
            .then(async (response) => {
            if (response.status === 200) {
                refreshAuth().then(() => navigate('/'));
            }
            return undefined;
        })
            .catch((error) => {
            return error.response?.data?.message ?? "Login failed";
        })
            .finally(() => {
            setIsLoading(false);
        });
    };
    const lines = [
        <div className="dos">{params.get("registerSuccess") == "true" ? 'Account created successfully. Please authenticate.' : 'Welcome. Please authenticate.'}</div>,
        "$error",
        <><div className="dos">Username:</div><input name="username" type="text" className="dos w-full"/></>,
        <><div className="dos">Password:</div><input name="password" type="password" className="dos w-full"/></>,
        <><div className="dos">Remember me:</div><input name="remember" type="checkbox" className="dos"/></>,
        <Link className="dos url" to="/forgot-password">Forgot your password?</Link>,
        <Link className="dos url" to="/register">Need an account? Click here to register</Link>
    ];
    return <Form handleSubmit={handleSubmit} lines={lines}/>;
}
