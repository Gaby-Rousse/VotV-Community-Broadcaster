import React, {useEffect, useState} from "react";
import {useNavigate} from "react-router";
import {useAuth} from "../../contexts/AuthContext.tsx";
import {isAuthenticated} from "../../models/interfaces/User.ts";
import api from "../../lib/axios.ts";
import {useLoading} from "../../contexts/LoadingContext.tsx";
import {Form} from "../../components/Form.tsx";
import {useSearchParams} from "react-router-dom";
export default function account() {
    //https://reactrouter.com/start/declarative/navigating#usenavigate
    const navigate = useNavigate();
    const {user, refreshAuth} = useAuth();
    const {setIsLoading} = useLoading();
    const [params] = useSearchParams();
    const [showConfirmDialog, setShowConfirmDialog] = useState(false)

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
        let max = user.email_verified_at ? 4 : 5
        if(isNaN(option) || option > max || option <= 0)
        {
            return `Invalid option. Must be between 1 and ${max}.`
        }



        switch(option) {
            case 1: {
                setIsLoading(true)
                return api.post("/api/v1/logout")
                    .then(async (response) => {
                        if (response.status === 204) {
                            refreshAuth();
                            navigate('/', {replace: true})
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
            case 2: {
                navigate('/update-profile')
                break;
            }
            case 3: {
                navigate('/update-password')
                break;
            }
            case 4: {
                setShowConfirmDialog(true);
                break;
            }
            case 5: {
                setIsLoading(true)
                return api.post("/api/v1/email/verification-notification")
                    .then(async (response) => {
                        if (response.status === 202) {
                            navigate('/account?redirectCode=5', {replace: true})
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
        }


    }

    const handleDeletion = async (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault()
        let formData = new FormData(e.currentTarget);
        let confirmation = formData.get("Confirm") as string;
        if(confirmation.toLowerCase() != 'y')
        {
            e.currentTarget.reset()
            setShowConfirmDialog(false)
            return;
        }

        setIsLoading(true)
        return api.delete(`/api/v1/user/${user.id}`)
            .then(async (response) => {
                if (response.status === 204) {
                    refreshAuth()
                    navigate('/login?redirectCode=4', {replace: true})
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

    let firstLine
    switch(params.get("redirectCode")) {
        case "2": {
            firstLine = 'Updated profile successfully.'
            break;
        }
        case "3": {
            firstLine = 'Updated password successfully.'
            break;
        }
        case "5": {
            firstLine = 'Re-sent email confirmation successfully.'
            break;
        }
        default: {
            firstLine = 'You can adjust your account settings here.'
            break;
        }
    }

    const lines = [
        <div>Hello {user.username}.</div>,
        <div>{firstLine}</div>,
        "$error",
        <div>1) Log off</div>,
        <div>2) Update profile information</div>,
        <div>3) Update password</div>,
        <div>4) Delete account</div>,
        !user.email_verified_at && <div>5) Re-send confirmation email</div>,
        <><div>Please select an option:</div><input name="Option"/></>,
        showConfirmDialog && <div className="pb-5"></div>
    ]

    return <><Form lines={lines} handleSubmit={handleSubmit}></Form>
        {showConfirmDialog && <form onSubmit={handleDeletion} className="ml-1 flex flex-row meadow gap-1 fixed bottom-0"><div>Are you sure you want to do this? (y/n)</div><input name="Confirm"/></form>}
    </>
}


