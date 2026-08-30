import React, {createContext, useContext, useEffect, useState} from 'react';

const LoadingContext = createContext({
    isLoading: false,
    setIsLoading: (value:boolean) => {},
});

export const LoadingProvider = ({children}: any) => {
    const [isLoading, setIsLoading] = useState(false);

    return (
        <LoadingContext.Provider
            value={{
                isLoading: isLoading,
                setIsLoading: setIsLoading,
            }}>
            {children}
            {isLoading ? (<LoadingOverlay/>) : ("")}
        </LoadingContext.Provider>
    );
};

export const useLoading = () => useContext(LoadingContext);

function LoadingOverlay()
{
    return (
        <>
            <div className="meadow fixed flex flex-col w-full h-full top-0 left-0 right-0 bottom-0 bg-black/50 z-10 ">
                <div className="flex gap-1 flex-row h-20 m-auto">
                    <img alt="" src="https://votvbroadcast.com/images/hourglass.gif"/>
                    <div className="text-xl my-auto">Loading...</div>
                </div>

            </div>
        </>
    )
}
