import React, {useEffect, useState} from "react";
import {useNavigate} from "react-router";
import {useAuth} from "../contexts/AuthContext";
import {isAuthenticated} from "../models/interfaces/User.ts";
import {Line} from "../components/Line.tsx";
import api from "../lib/axios.ts";
import {useLoading} from "../contexts/LoadingContext.tsx";
import {Form} from "../components/Form.tsx";
export default function account() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const {user, refreshAuth} = useAuth();
    const {setIsLoading} = useLoading();

    useEffect(() => {
        if (!isAuthenticated(user)) {
            navigate("/login");
        }
    }, [isAuthenticated(user), navigate]);

    //https://react.dev/reference/react-dom/components/form
    const handleSubmit = async (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault()

        let formData = new FormData(e.currentTarget);
        let option = parseInt(formData.get("Option")?.toString() ?? "")
        let max = 3
        if(isNaN(option) || option > max)
        {
            return `Invalid option. Must be between 1 and ${max}.`
        }

        if (option == 1) {
            api.post("/api/v1/logout")
                .then(async (response) => {
                    if (response.status === 200) {
                        refreshAuth().then(() =>
                            navigate('/')
                        )
                    }
                    return undefined;
                })
                .catch((error) => {
                    return error.response?.data?.message ?? "Couldn't logout.";
                })
                .finally(() => {
                    setIsLoading(false)
                });
        }
    }

    const lines = [
        <div>Hello {user.username}.</div>,
        <div>You can adjust your account settings here.</div>,
        "$error",
        <div>1) Log off</div>,
        <div>2) Update account information</div>,
        <div>3) Delete account</div>,
        <><div>Please select an option:</div><input name="Option"/></>
    ]

    return <Form lines={lines} handleSubmit={handleSubmit}></Form>
}


