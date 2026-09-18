import {Children, useEffect} from "react";

export function Lines({ visibleCount, children }: {
    visibleCount: number;
    children: React.ReactNode;
}) {
    return <>{Children.toArray(children).slice(0, visibleCount)}</>;
}

export function Line({ children }: { children: React.ReactNode }) {
    return <div className="flex flex-row w-full gap-1">{children}</div>;
}
