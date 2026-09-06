import React, {useEffect} from "react";
import {useNavigate} from "react-router";
import {useAuth} from "../../contexts/AuthContext.tsx";
import {isAuthenticated} from "../../models/interfaces/User.ts";
import api from "../../lib/axios.ts";
import {useLoading} from "../../contexts/LoadingContext.tsx";
import {Form} from "../../components/Form.tsx";
export default function forgotPassword() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const {user, refreshAuth} = useAuth();
    const {setIsLoading} = useLoading();

    useEffect(() => {
        if (isAuthenticated(user)) {
            navigate("/account");
        }
    }, [isAuthenticated(user), navigate]);

    //https://react.dev/reference/react-dom/components/form
    const handleSubmit = async (e: React.SubmitEvent<HTMLFormElement>):Promise<string | undefined> => {
        e.preventDefault()

        let formData = new FormData(e.currentTarget);

        return api.post("/api/v1/forgot-password", formData)
                .then(async (response) => {
                    if (response.status === 200) {
                        navigate('/login?redirectCode=2')
                    }
                    return undefined;
                })
                .catch((error) => {
                    return error.response?.data?.message ?? "Something went wrong.";
                })
                .finally(() => {
                    setIsLoading(false)
                });
    }

    const lines = [
        <div>Hello.</div>,
        <div>Please input your email address and the server will send you a password reset link.</div>,
        "$error",
        <><div>Email:</div><input name="email" type="text" className="w-full"/></>,
    ]

    return <Form lines={lines} handleSubmit={handleSubmit}></Form>
}


