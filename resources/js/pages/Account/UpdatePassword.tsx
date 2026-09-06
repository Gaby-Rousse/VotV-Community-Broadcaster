import React, {useEffect} from "react";
import {useNavigate} from "react-router";
import {useAuth} from "../../contexts/AuthContext.tsx";
import {isAuthenticated} from "../../models/interfaces/User.ts";
import api from "../../lib/axios.ts";
import {useLoading} from "../../contexts/LoadingContext.tsx";
import {Form} from "../../components/Form.tsx";

export default function UpdatePassword() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const {setIsLoading} = useLoading();
    const {user} = useAuth();

    useEffect(() => {
        if (!isAuthenticated(user)) {
            navigate("/");
        }
    }, [isAuthenticated(user), navigate]);

    //https://react.dev/reference/react-dom/components/form
    const handleSubmit = async (e: React.SubmitEvent<HTMLFormElement>): Promise<string | undefined> => {
        e.preventDefault();

        setIsLoading(true)

        let formData = new FormData(e.currentTarget);

        return api.put("/api/v1/user/password", formData)
            .then(async (response) => {
                if (response.status === 200) {
                    navigate('/login?redirectCode=3')
                }
                return undefined;
            })
            .catch((error) => {
                return error.response?.data?.message ?? "Something went wrong";
            })
            .finally(() => {
                setIsLoading(false)
            });
    }

    const lines = [
        <div>Update your password below</div>,
        "$error",
        <><div className="text-nowrap">Current password:</div><input name="current_password" type="password" className="w-full"/></>,
        <><div >Password:</div><input name="password" type="password" className="w-full"/></>,
        <><div className="text-nowrap">Password confirmation:</div><input name="password_confirmation" type="password" className="w-full"/></>,
    ]

    return <Form lines={lines} handleSubmit={handleSubmit} ></Form>
}

