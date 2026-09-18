import React, {useEffect} from "react";
import {useNavigate} from "react-router";
import {useAuth} from "../../contexts/AuthContext.tsx";
import {isAuthenticated} from "../../models/interfaces/User.ts";
import api from "../../lib/axios.ts";
import {useLoading} from "../../contexts/LoadingContext.tsx";
import {Form} from "../../components/Form.tsx";

export default function UpdateProfile() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const {setIsLoading} = useLoading();
    const {user, refreshAuth} = useAuth();

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

        return api.put("/api/v1/user/profile-information", formData)
            .then(async (response) => {
                if (response.status === 200) {
                    await refreshAuth().then(() => navigate('/account?redirectCode=2'))

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
        <div>You can update the following fields:</div>,
        "$error",
        <><div>Username:</div><input defaultValue={user.username} name="username" type="text" className="w-full"/></>,
        <><div className="text-nowrap">Email (Optional):</div><input defaultValue={user.email} name="email" type="text" className="w-full"/></>,
    ]

    return <Form lines={lines} handleSubmit={handleSubmit} ></Form>
}

