import React, {useEffect, useState} from "react";
import {Link, useNavigate} from "react-router";
import {useAuth} from "../contexts/AuthContext";
import api from "../lib/axios.ts";
import {Line, Lines} from "./Line.tsx";
import {isAuthenticated} from "../models/interfaces/User.ts";
import {useLoading} from "../contexts/LoadingContext.tsx";
import {useSearchParams} from "react-router-dom";
export function Form({ handleSubmit, lines }: {
    handleSubmit:  (e: React.SubmitEvent<HTMLFormElement>) => Promise<string | undefined>;
    lines: React.ReactNode[];
}) {
    const [visibleCount, setVisibleCount] = useState(0)
    const [error, setError] = useState("");

    useEffect(() => {
        if (visibleCount < lines.length) {
            const timer = setTimeout(() => {
                setVisibleCount(prev => prev + 1);
            }, 500);
            return () => clearTimeout(timer);
        }
    }, [visibleCount]);

    const onSubmit = async (e: React.SubmitEvent<HTMLFormElement>) => {
        const message = await handleSubmit(e);
        if (message) setError(message);
    };


    return (
        <>
            <form onSubmit={onSubmit}
                  className="bg-black flex h-screen w-screen">
                <div className="ml-1 mt-auto flex flex-col">

                    <Lines visibleCount={visibleCount}>
                        {lines.map(value =>
                            value == "$error" ? <div className="dos red">ERR: {error}</div> : <Line>{value}</Line>
                        )}
                    </Lines>
                    <button type="submit" className="dos url w-4 sm:hidden">Submit!</button>
                </div>
                {/* For Safari that uses GO for some stupid reason */}
                <button type="submit" className="sr-only">Submit</button>
            </form>

        </>
    );
}
