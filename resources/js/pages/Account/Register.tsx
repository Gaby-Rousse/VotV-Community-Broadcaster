import React, {useEffect, useState} from "react";
import {Link, useNavigate} from "react-router";
import {useAuth} from "../../contexts/AuthContext.tsx";
import {isAuthenticated} from "../../models/interfaces/User.ts";
import api from "../../lib/axios.ts";
import {useLoading} from "../../contexts/LoadingContext.tsx";
import {Form} from "../../components/Form.tsx";

export default function register() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const {setIsLoading} = useLoading();
    const {user} = useAuth();

    useEffect(() => {
        if (isAuthenticated(user)) {
            navigate("/");
        }
    }, [isAuthenticated(user), navigate]);

    //https://react.dev/reference/react-dom/components/form
    const handleSubmit = async (e: React.SubmitEvent<HTMLFormElement>): Promise<string | undefined> => {
        e.preventDefault();

        setIsLoading(true)

        let formData = new FormData(e.currentTarget);

        return api.post("/api/v1/register", formData)
            .then(async (response) => {
                if (response.status === 201) {
                    navigate('/login?redirectCode=1')
                }
                return undefined;
            })
            .catch((error) => {
                return error.response?.data?.message ?? "Registration failed";
            })
            .finally(() => {
                setIsLoading(false)
            });
    }

    const lines = [
        <div>Welcome new user.</div>,
        <div>Enter the username and the password you wish to use.</div>,
        "$error",
        <><div >Username:</div><input name="username" type="text" className=" w-full"/></>,
        <><div className="text-nowrap">Email (Optional):</div><input name="email" type="text" className="w-full"/></>,
        <><div >Password:</div><input name="password" type="password" className="w-full"/></>,
        <><div className="text-nowrap">Password confirmation:</div><input name="password_confirmation" type="password" className="w-full"/></>,
        <Link to="/login">Already have an account? Click here to login</Link>
    ]

    return <Form lines={lines} handleSubmit={handleSubmit} ></Form>
}

