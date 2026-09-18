import React, {useEffect} from "react";
import {useNavigate} from "react-router";
import {useAuth} from "../../contexts/AuthContext.tsx";
import {isAuthenticated} from "../../models/interfaces/User.ts";
import api from "../../lib/axios.ts";
import {useLoading} from "../../contexts/LoadingContext.tsx";
import {Form} from "../../components/Form.tsx";
import {useSearchParams} from "react-router-dom";

export default function resetPassword() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const {setIsLoading} = useLoading();
    const {user} = useAuth();
    const [params] = useSearchParams();
    let email = params.get("email")
    let token = params.get("token")

    useEffect(() => {
        if (isAuthenticated(user)) {
            navigate("/");
        }
    }, [isAuthenticated(user), navigate]);

    //https://react.dev/reference/react-dom/components/form
    const handleSubmit = async (e: React.SubmitEvent<HTMLFormElement>): Promise<string | undefined> => {
        e.preventDefault();

        let formData = new FormData(e.currentTarget);
        if (!email) {
            return "Missing email?"
        }
        if(!token) {
            return "Missing token?"
        }

        setIsLoading(true)

        formData.set("email",email);
        formData.set("token",token);

        return api.post("/api/v1/reset-password", formData)
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
        <div>Hello {email}</div>,
        <div>Enter the new password you want to use</div>,
        "$error",
        <><div >Password:</div><input name="password" type="password" className="w-full"/></>,
        <><div className="text-nowrap">Password confirmation:</div><input name="password_confirmation" type="password" className="w-full"/></>,
    ]

    return <Form lines={lines} handleSubmit={handleSubmit} ></Form>
}

