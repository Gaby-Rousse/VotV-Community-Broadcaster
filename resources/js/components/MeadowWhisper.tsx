import { Tooltip, Whisper } from "rsuite";
import {deviceType} from "detect-it";
export function MeadowWhisper({ tooltip, children }: {
    tooltip: string;
    children: React.ReactNode;
}) {

    let canHover = deviceType != "touchOnly"

    return (
        <Whisper
            trigger={canHover ? "hover" : "click"}
            followCursor
            preventOverflow
            speaker={<Tooltip placement="top" className="meadow-tooltip" arrow={false}>
                {tooltip}
            </Tooltip>}
        >
            {children}
        </Whisper>
    )
}
