import { Children } from "react";
export function Lines({ visibleCount, children }) {
    return <>{Children.toArray(children).slice(0, visibleCount)}</>;
}
export function Line({ children }) {
    return <div className="flex flex-row gap-1">{children}</div>;
}
