import {Link} from "react-router";

interface MeadowButtonProps {
    to?: string;
    onClick?: () => void;
    animated?: boolean;
    selected?: boolean;
    disabled?: boolean;
    className?: string;
    text: string;
    type?: "button" | "reset" | "submit" | undefined
}

export default function MeadowButton(props: MeadowButtonProps) {
    const classes = [
        'btn',
        props.selected && 'selected',
        props.animated && 'btn-press-animation',
        props.className

    ].filter(Boolean).join(' ');

    return props.to
        ? <Link onClick={props.onClick} aria-current={props.selected ? 'page' : undefined} to={props.to} className={classes}><span>{props.text}</span></Link>
        : <button disabled={props.disabled} aria-pressed={props.selected} type={props.type ?? "button"} onClick={props.onClick} className={classes}><span>{props.text}</span></button>;
}
